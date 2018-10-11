<?php
namespace Seeruo\Core;

use Exception;
use \Seeruo\Core\File;
use \Seeruo\Core\Log;
use \Seeruo\Lib\Parser;

/**
 * Build
 */
class Build 
{
    /**
     * 配置文件
     */
    private $config = array(); 
    /**
     * 分页条数
     */
    private $page_limit = 10;
    /**
     * 模版文件路径
     */
    private $themes_dir; 
    /**
     * 渲染结果存放目录
     */
    private $public_dir;
    /**
     * 源文件路径
     */
    private $source_dir; 
    /**
     * 文件组
     */
    private $files; 
    /**
     * 单文件索引组
     */
    private $file_index;
    /**
     * 类型索引组
     */
    private $type_index;
    /**
     * 解析markdown的对象
     */
    private $markd2html;
    /**
     * 单页文件uuid
     */
    private $single_pages = array();

    /**
     * 构造函数
     * @DateTime 2018-10-09
     */
    public function __construct($config){
        $this->config = $config;
        $this->page_limit = 5;
        $this->themes_dir = $config['themes_dir'];
        $this->public_dir = $config['public_dir'];
        $this->source_dir = $config['source_dir'];
        $this->markd2html = new Parser;
    }
    /**
     * 构建静态网站所需文件
     */
    public function run()
    {
        try {
            Log::info('Start build...');
            // @检查单页
            $this->checkSinglePages();
            // @解析文件设置
            $this->resolveFiles();
            // @静态文件构建
            $this->createStatic();
            // @生成查询json
            $this->createSearchData();
            // @生成文章信息
            $this->createHome();
            // @生成文章页面
            $this->createArticles();
            // @生成主题页面
            $this->createSinglePage();
            // @生成归档页面
            $this->createTimer();
            // @生成分类页面
            $this->createCategory();
            Log::info('Complete build!');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::info('Build failed!');
        }
    }

    /**
     * [createStatic 静态资源文件转移]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-12
     * @return   [type]
     */
    private function createStatic()
    {
        // 主题里面的静态文件
        $files = File::getFiles($this->themes_dir . DIRECTORY_SEPARATOR . 'static');
        // 文件拷贝
        array_walk($files, function ($file)
        {
            $search_str = $this->themes_dir . DIRECTORY_SEPARATOR . 'static';
            $replace_str = $this->public_dir . DIRECTORY_SEPARATOR . 'static';
            $to_path = str_replace($search_str, $replace_str, $file['file_path']);
            File::copyFolder($file['file_path'], $to_path);
        });
    }

    /**
     * [createSinglePage 构建单页页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-19
     * @return   [type]     [description]
     */
    private function createSinglePage()
    {
        if (isset($this->config['single_pages'])) {
            $themes = $this->config['single_pages'];
            foreach ($themes as $key => $v) {
                $theme_page = $this->source_dir.DIRECTORY_SEPARATOR . $v;
                if ( file_exists($theme_page) ) {
                    $file_path = $this->public_dir . DIRECTORY_SEPARATOR . $key . '/index.html';
                    $html = $this->renderArticle(md5($theme_page), true);
                    File::createFile($file_path, $html);
                }
            }
        }
    }

    /**
     * [createSinglePage 构建查询json]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-19
     * @return   [type]     [description]
     */
    private function createSearchData()
    {
        $search_data = [];
        $files = $this->files;
        // 单页文件不解析
        foreach ($files as $key => $file) {
            if (in_array($file['page_uuid'], $this->single_pages)) {
                unset($files[$key]);
                continue;
            }
            $full_content = File::getContent($file['file_path'])['content'];
            $desc_content = mb_substr($full_content, 0, 1000, 'utf-8');
            $file['description'] = $this->markd2html->makeHtml( $desc_content );
            $search_data[] = [
                'title' => $file['title'],
                'href' => $file['href'],
                'date' => $file['date'],
                'tags' => implode(',', $file['tags']),
                'type' => implode(',', $file['type']),
                // 'desc' => $file['description'],
            ];
        }
        $file_path = $this->public_dir . DIRECTORY_SEPARATOR . 'articles/data.json';
        $html = json_encode($search_data, JSON_UNESCAPED_UNICODE);
        File::createFile($file_path, $html);
    }

    /**
     * [createHome 构建主页静态文件]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-12
     * @return   [type]
     */
    public function createHome()
    {
        // 检查是否设置了主页
        $home_page = @$this->config['home_page'] ?: '';
        if ( !empty($home_page) && file_exists($this->source_dir.DIRECTORY_SEPARATOR . $home_page) ) {
            // 渲染html
            $home_page = $this->source_dir.DIRECTORY_SEPARATOR . $home_page;
            $html = $this->renderArticle(md5($home_page), true);
            // 创建文件
            $file_path = $this->public_dir . '/index.html';
            File::createFile($file_path, $html);
        }else{
            $files = $this->files;
            // 单页文件不解析
            foreach ($files as $key => $file) {
                if (in_array($file['page_uuid'], $this->single_pages)) {
                    unset($files[$key]);
                    continue;
                }
            }
            // 根据分页渲染
            $limit = $this->page_limit;
            $pages = ceil(count($files) / $limit);
            for ($i=1; $i <= $pages; $i++) {
                $repo = [];
                $path_key = '';
                if ($i == 1) {
                    $repo = array_slice($files, 0, $limit);
                    $path_key = '';
                }else{
                    $start = $limit * ($i-1);
                    $repo = array_slice($files, $start, $limit);
                    $path_key = 'page'.'-'.$i;
                }
                $html = $this->renderHome($repo, $pages, $i);

                // 处理路由
                $path_key = array_filter(explode('-', $path_key));
                $path = empty($path_key)? '' : implode('/', $path_key) . '/';
                $file_path = $this->public_dir .DIRECTORY_SEPARATOR. $path .'index.html';
                File::createFile($file_path, $html);
            }
        }
    }

    /**
     * [renderHome 解析主页静态页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-19
     * @param    array      $rep               [文件数组]
     * @param    [type]     $pages             [总页码]
     * @param    [type]     $curr_page         [当前页码]
     * @param    [type]     $path_key          [路径关键字]
     * @return   [type]                        [description]
     */
    public function renderHome($rep=[], $pages, $curr_page)
    {
        // 如果分页太多，就创建一个分页导航
        $pages_html = '';
        if ($pages>1) {
            $pages_html .= '<div class="pages">';
            for ($i=1; $i <= $pages; $i++) {
                if ($i === $curr_page) {
                    $pages_html .= '<span class="current-page">'.$i.'</span>';
                }else{
                    if ($i === 1) {
                        $pages_html .= '<a href="/">'.$i.'</a>';
                    }else{
                        $pages_html .= '<a href="/page/'.$i.'">'.$i.'</a>';
                    }
                }
            }
            $pages_html .= '</div>';
        }

        // 获取部分文件内容,用作缩略展示
        foreach ($rep as &$file) {
            $full_content = File::getContent($file['file_path'])['content'];
            $desc_content = mb_substr($full_content, 0, 1000, 'utf-8');
            $file['description'] = $this->markd2html->makeHtml( $desc_content );
        }
        unset($file);
        
        $file = [];
        $file['articles_file_index'] = $this->file_index;
        $file['articles_type_index'] = $this->type_index;
        $file['articles_list'] = $rep;
        $file['articles_pages'] = $pages_html; // 分页导航
        $file['web_title'] = $this->config['title'];  // 网站标题
        $html = $this->render('index.html', $file);
        return $html;
    }
    
    /**
     * [createArticles 构建文章静态页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-12
     * @return   [type]
     */
    private function createArticles()
    {
        // 解析文件索引
        $site_title = $this->config['title'];
        foreach ($this->files as $key => $file) {
            // 单页文件不解析
            if (in_array($file['page_uuid'], $this->single_pages)) {
                continue;
            }
            $html = $this->renderArticle($file['page_uuid']);
            File::createFile($file['public_dir'], $html);
        }
    }

    /**
     * [renderArticle 解析文章静态页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-12
     * @param    [type]     $page_uuid         [文章uuid]
     * @return   [type]
     */
    public function renderArticle($page_uuid, $single_page=false)
    {
        $file = $this->files[$page_uuid];
        $file_data = File::getContent($file['file_path']);
        $file['content']             = $this->markd2html->makeHtml( $file_data['content'] );
        $file['articles_file_index'] = $this->file_index;
        $file['articles_type_index'] = $this->type_index;
        $file['author'] = $file['author'] ?: $this->config['author'];
        $file['href'] = $this->config['base_url'].$file['href'];
        $file['single_page'] = $single_page;

        // 类型分类需要字段
        $file['page_uuid_type_now'] = getpy($this->getLastItem($file['type']));
        $file['page_uuid_type'] = $file['type'];
        array_walk($file['page_uuid_type'], function(&$d){
            $d = getpy(trim($d));
        });

        $html = $this->render('article.html', $file);
        return $html;
    }

    /** 日期归档 blog *****************************************/
    /**
     * [createTimer 创建时间归档页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-18
     * @return   [type]     [description]
     */
    public function createTimer()
    {
        $archives = [];
        $files = $this->files;

        foreach ($files as $key => $file) {
            // 单页文件不解析
            if (in_array($file['page_uuid'], $this->single_pages)) {
                unset($files[$key]);
                continue;
            }
            // 解析其他文件
            $date = empty($file['date']) ? date('Y-m-d') : date('Y-m-d', strtotime($file['date']));
            $date_arr = explode('-', $date);
            $archives[$date_arr[0]][] = $file;
            $archives[$date_arr[0].'-'.$date_arr[1]][] = $file;
            $archives[$date_arr[0].'-'.$date_arr[1].'-'.$date_arr[2]][] = $file;
        }
        $archives['all'] = $files;
        // 构建分页
        $limit = $this->page_limit;
        foreach ($archives as $k => $v) {
            $pages = ceil(count($v) / $limit);
            for ($i=1; $i <= $pages; $i++) {
                $repo = [];
                $path_key = '';
                if ($i == 1) {
                    $repo = array_slice($v, 0, $limit);
                    $path_key = $k;
                }else{
                    $start = $limit * ($i-1);
                    $repo = array_slice($v, $start, $limit);
                    $path_key = $k.'-'.'page'.'-'.$i;
                }
                $html = $this->renderTimer($repo, $pages, $i, $k);

                // 处理路由
                $path_key = array_filter(explode('-', $path_key), function($s){
                    return $s !== 'all';
                });
                $path = empty($path_key)? '' : implode('/', $path_key) . '/';
                $file_path = $this->public_dir .'/archives/'. $path .'index.html';

                File::createFile($file_path, $html);
            }
        }
    }

    /**
     * [renderTimer 解析时间归档页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-19
     * @param    array      $rep               [文件数组]
     * @param    [type]     $pages             [总页码]
     * @param    [type]     $curr_page         [当前页码]
     * @param    [type]     $path_key          [路径关键字]
     * @return   [type]                        [description]
     */
    public function renderTimer($rep=[], $pages, $curr_page, $path_key)
    {
        // 基础路径解析
        $path_key_arr = array_filter(explode('-', $path_key), function($s){
            return $s !== 'all';
        });
        $path = empty($path_key_arr)? '' : implode('/', $path_key_arr) . '/';
        $path = '/archives/'. $path;

        // 如果分页太多，就创建一个分页导航
        $pages_html = '';
        if ($pages>1) {
            $pages_html .= '<div class="pages">';
            for ($i=1; $i <= $pages; $i++) {
                if ($i === $curr_page) {
                    $pages_html .= '<span class="current-page">'.$i.'</span>';
                }else{
                    if ($i === 1) {
                        $pages_html .= '<a href="'.$path.'">'.$i.'</a>';
                    }else{
                        $pages_html .= '<a href="'.$path.'page/'.$i.'">'.$i.'</a>';
                    }
                }
            }
            $pages_html .= '</div>';
        }
                
        $file = [];
        $file['articles_file_index'] = $this->file_index;
        $file['articles_list'] = $rep;
        $file['articles_pages'] = $pages_html;
        $file['web_title'] = $this->config['title'];  // 网站标题
        $file['title'] = '归档';  // 网站标题
        $html = $this->render('archives.html', $file);
        return $html;
    }


    /** 类型归档 note  *****************************************/
    /**
     * [createCategory 创建分类归档页面]
     * @DateTime 2018-10-08
     * @param    string     $value [description]
     * @return   [type]            [description]
     */
    private function createCategory($value='')
    {
        $files = $this->files;

        // 构建文件分类
        $archives = [];
        foreach ($files as $key => $file) {
            // 单页文件不解析
            if (in_array($file['page_uuid'], $this->single_pages)) {
                unset($files[$key]);
                continue;
            }
            // 文件类型处理
            $len = count($file['type']);
            $str = '';
            for ($i=0; $i < $len; $i++) {
                $str.= '__' . $file['type'][$i];
                $str = trim($str, '__');
                $str_py = getpy($str);
                $archives[ $str_py ][] = $file;
            }
        }
        $archives['all'] = $files;

        // 构建分页 分类导航索引
        $limit = $this->page_limit;
        foreach ($archives as $k => $v) {
            // $k = getpy($k); // 中文转拼音
            $pages = ceil(count($v) / $limit);
            for ($i=1; $i <= $pages; $i++) {
                $repo = [];
                $path_key = '';
                if ($i == 1) {
                    $repo = array_slice($v, 0, $limit);
                    $path_key = $k;
                }else{
                    $start = $limit * ($i-1);
                    $repo = array_slice($v, $start, $limit);
                    $path_key = $k.'__'.'page'.'__'.$i;
                }
                $html = $this->renderCategory($repo, $pages, $i, $k);

                // 处理路由
                $path_key = array_filter(explode('__', $path_key), function($s){
                    return $s !== 'all';
                });
                $path = empty($path_key)? '' : implode('/', $path_key) . '/';
                $file_path = $this->public_dir .'/category/'. $path .'index.html';

                File::createFile($file_path, $html);
            }
        }
    }

    /**
     * [renderCategory 解析分类归档页面]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-19
     * @param    array      $rep               [文件数组]
     * @param    [type]     $pages             [总页码]
     * @param    [type]     $curr_page         [当前页码]
     * @param    [type]     $path_key          [路径关键字]
     * @return   [type]                        [description]
     */
    public function renderCategory($rep=[], $pages, $curr_page, $path_key)
    {
        // 基础路径解析
        $path_key_arr = array_filter(explode('__', $path_key), function($s){
            return $s !== 'all';
        });
        $path_key_arr = array_values($path_key_arr);
        $path = empty($path_key_arr)? '' : implode('/', $path_key_arr) . '/';
        $path = '/category/'. $path;

        // 如果分页太多，就创建一个分页导航
        $pages_html = '';
        if ($pages>1) {
            $pages_html .= '<div class="pages">';
            for ($i=1; $i <= $pages; $i++) {
                if ($i === $curr_page) {
                    $pages_html .= '<span class="current-page">'.$i.'</span>';
                }else{
                    if ($i === 1) {
                        $pages_html .= '<a href="'.$path.'">'.$i.'</a>';
                    }else{
                        $pages_html .= '<a href="'.$path.'page/'.$i.'">'.$i.'</a>';
                    }
                }
            }
            $pages_html .= '</div>';
        }
                
        $file = [];
        // 文件索引
        $file['articles_file_index'] = $this->file_index;
        // 类型索引相关
        $file['articles_type_index'] = $this->type_index;
        $file['page_uuid'] = @$this->getLastItem($path_key_arr);
        $file['page_uuid_type'] = $path_key_arr;
        // 文章相关
        $file['articles_list'] = $rep;  // 归档列表
        $file['articles_pages'] = $pages_html;
        $file['web_title'] = $this->config['title'];  // 网站标题
        $file['title'] = '归档';  // 网站标题
        $html = $this->render('archives.html', $file);
        return $html;
    }

    /**
     * [resolveFiles 解析文件]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-09-18
     * @return   [type]     [description]
     */
    public function resolveFiles()
    {
        // 获取文件列表
        $files = File::getFiles($this->source_dir);
      
        // 对文件按时间重新排序
        $this->resortByTime($files);
        
        // 文章归集
        // $lvl_index = [];        // 解析文章索引
        $new_files = [];        // 新的文件数组
        foreach ($files as $key => $file) {
            // 解析文件名
            $file_name = $file['file_name'];
            $file_type = substr(strrchr($file_name, '.'), 1);
            $file_name = basename($file_name, '.'.$file_type);

            // 解析时间并设置归档路径和链接地址
            $date = empty($file['date']) ? date('Y-m-d') : date('Y-m-d', strtotime($file['date']));
            $date_arr = explode('-', $date);
            $file['date'] = $date;
            $file['href'] = '/articles/'.$date_arr[0].'/'.$date_arr[1].'/'.$date_arr[2].'/'.$file_name;
            $file['public_dir'] = $this->public_dir . $file['href'] . '/index.html';   // 文件生成路径
            $file['web_title'] = $this->config['title'];            // 网站标题
            $file['page_uuid'] = md5($file['file_path']);
            $file['page_uuid_type'] = getpy($file['type'][0]);
            $file['page_uuid_type_now'] = getpy($this->getLastItem($file['type']));

            // 处理文章分类
            if ( empty($file['type']) ) {
                $file_type = str_replace($this->source_dir, '', $file['file_dire']);
                $file_type = explode(DIRECTORY_SEPARATOR, trim($file_type));
                // foreach ($file_type as &$t) {
                //     $t = trim($t);
                // }
                // $file['type'] = array_values(array_filter($file_type));
            }else{
                $file_type = explode(',', trim($file['type']));
            }
            foreach ($file_type as &$t) {
                $t = trim($t);
            }
            $file['type'] = array_values(array_filter($file_type));

            $new_files[$file['page_uuid']] = $file;

            /** 文章索引 ***********************/

            // // 直接索引：一级索引
            // $all_index[$file['page_uuid']] = [
            //     'href'  => $file['href'],
            //     'title' => $file['title'],
            //     'uuid'  => $file['page_uuid'],
            // ];
            // 类型索引：后续处理多级索引
            // $lvl_index[] = $file;
        }
        unset($key, $file);
        $this->files = $new_files;

        // 单文件索引创建：包含标题索引
        $this->resolveFileMenu();

        // 类型归档索引创建
        $this->resolveTypeIndex();
    }

    /**
     * [FunctionName 单文件子菜单索引]
     * @DateTime 2018-10-10
     * @param    string     $value [description]
     */
    public function resolveFileMenu()
    {
        $file_index = [];
        $files = $this->files;
        foreach ($files as $key => $file) {
            // 单页文件不做索引
            if (in_array($file['page_uuid'], $this->single_pages)) {
                continue;
            }
            // 标题索引处理
            $content = File::getContent($file['file_path']);
            $token = array_filter(explode("\n", $content['content']), function ($d){
                if (empty($d)) {
                    return false;
                }
                if (strpos($d, "\n#") === false && strpos($d, '#') !== 0 ) {
                    return false;
                }
                return true;
            });
            if (count($token)>0) {
                foreach ($token as &$value) {
                    $value = preg_replace_callback("|(#{1,6} )|", function($m){
                                return '';
                            }, $value);
                    $value = [
                        'title' => $value,
                        'href'  => '#'.$value,
                    ];
                }
            }
            $index = [
                'title' => $file['title'],
                'href'  => $file['href'],
                'uuid'  => $file['page_uuid'],
                'child' => $token
            ];
            $file_index[$file['page_uuid']] = $index;
        }
        $this->file_index = $file_index;
        // dd($file_index);
    }

    /** note类型归档分类 start ***********************************************************/
    /**
     * [resolveTypeIndex 根据类型多级分类]
     * @DateTime 2018-10-09
     * @param    [type]     $arr [description]
     * @return   [type]          [description]
     */
    private function resolveTypeIndex()
    {
        // 构建文件分类
        $archives = [];
        $files = $this->files;
        foreach ($files as $key => $file) {
            // 单页文件不做索引
            if (in_array($file['page_uuid'], $this->single_pages)) {
                continue;
            }
            // 文件类型处理
            $len = count($file['type']);
            $str = '';
            for ($i=0; $i < $len; $i++) {
                $str.= '__' . $file['type'][$i];
                $str = trim($str, '__');
                $archives[] = $str;
            }
        }
        unset($file);

        $archives = array_unique($archives);
        $keys = $archives;
        foreach ($archives as $k1 => $v1) {
            foreach ($keys as $v2) {
                if (strpos($v2, $v1) !== false && ($v1 !== $v2)) {
                    unset($archives[$k1]);
                    break;
                }
            }
        }
        unset($v1,$k1,$v2);

        $type_index = [];
        foreach ($archives as $key => $t) {
            $type = explode('__', $t);
            $length = count($type);
            $url = '/category';
            $type_index = $this->my_merge($type_index, $this->getArray(0, $length, $type, $url));
        }
        // dd($type_index);
        $this->type_index = $type_index;
    }

    /**
     * [my_merge 数组深处合并]
     * @DateTime 2018-10-10
     * @param    [type]     $a [description]
     * @param    [type]     $b [description]
     * @return   [type]        [description]
     */
    private function my_merge($a, $b){
        if (is_array($b)) {
            foreach ($b as $k => $v) {
                if (isset($a[$k])) {
                    $a[$k] = $this->my_merge($a[$k], $v);
                }else{
                    $a[$k] = $v;
                }
            }
        }
        @asort($a);
        return $a;
    }

    /**
     * [getArray 字符串转数组]
     * @DateTime 2018-10-10
     * @param    [type]     $i      [description]
     * @param    [type]     $length [description]
     * @param    [type]     $arr    [description]
     * @param    [type]     $url    [description]
     * @return   [type]             [description]
     */
    
    private function getArray($i, $length, $arr, $url){
        $str=array();
        if($i==$length-1){
            $str[$arr[$i]] = [
                'title' => $arr[$i],
                'href'  => $url.'/'.getpy($arr[$i]),
                'uuid'  => getpy($arr[$i])
            ];
        }else{
            $str[$arr[$i]]['title'] = $arr[$i];
            $str[$arr[$i]]['href']  = $url.'/'.getpy($arr[$i]);
            $str[$arr[$i]]['uuid']  = getpy($arr[$i]);
            $str[$arr[$i]]['child'] = $this->getArray($i+1, $length, $arr, $str[$arr[$i]]['href']);
            
        }
        return $str;
    }
    /**
     * [getLastItem 获取最后一个元素]
     * @DateTime 2018-10-10
     * @param    [type]     $arr [元素对象]
     * @return   [type]          [description]
     */
    public function getLastItem($arr)
    {
        if (is_array($arr)) {
            $length = count($arr);
            return $arr[$length - 1];
        }else{
            return '';
        }
    }
    /** note类型归档分类 end ***********************************************************/


    /**
     * [resortByTime 对数组按照时间重新排序]
     * @DateTime 2018-10-09
     * @param    [type]     &$files [description]
     * @return   [type]             [description]
     */
    private function resortByTime(&$files)
    {
        $length = count($files); // 文件数量
        for($i=1; $i<$length; $i++) {
            for($j=0; $j<$length - $i; $j++){
                if ( (int)strtotime($files[$j]['date']) <  (int)strtotime($files[$j+1]['date'])) {
                    $temp = $files[$j+1];
                    $files[$j+1] = $files[$j];
                    $files[$j] = $temp;
                }
            }
        }
    }
    /**
     * [checkSinglePages 监测单页]
     * @DateTime 2018-10-09
     * @return   [type]     [description]
     */
    public function checkSinglePages()
    {
        $sg_pgs = @$this->config['single_pages'] ?: [];
        $sg_pgs = array_values($sg_pgs);
        // 检查需要剔除的页面
        $home_page = @$this->config['home_page'] ?: '';
        if (!empty($home_page)) {
            array_push($sg_pgs, $home_page);
        }
        if (!empty($sg_pgs)) {
            array_walk($sg_pgs, function(&$d){
                $d = md5($this->source_dir . DIRECTORY_SEPARATOR . $d);
            });
        }
        $this->single_pages = $sg_pgs;
    }

    /**
     * [render 模版解析]
     * @DateTime 2018-10-09
     * @param    [type]     $file [模版文件]
     * @param    array      $data [填充数据]
     * @return   [type]           [模版代码]
     */
    public function render($file, $data=[])
    {
        \Twig_Autoloader::register();
        $loader = new \Twig_loader_Filesystem($this->themes_dir);
        $twig = new \Twig_Environment(
                    $loader,
                    [
                        'cache' => false, //或者直接指定路径
                        'debug' => false
                    ]
                );
        $template = $twig->loadTemplate($file);
        $html = $template->render($data);
        return $html;
    }

    /**** 文件变化监测程序 ***************************************************************/
    /**
     * [getFilesHashKey 获取文件hash-key]
     * @DateTime 2018-09-23
     * @return   [type]     [description]
     */
    public function getFilesHashKey()
    {
        $file_locker = [];
        $files_source = File::getFiles($this->source_dir);
        $files_themes = File::getFiles($this->themes_dir);
        $files = array_merge($files_source, $files_themes);
        foreach ($files as $file) {
            $file_locker[] = md5_file($file['file_path']);
        }
        return $file_locker;
    }
    /**
     * [文件变动监听]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-25
     * @return   [type]
     */
    public function listen()
    {
        try {
            Log::info('['.date('H:i:s').']文件监测程序运行中...');
            while (true) {
                sleep(1);
                // 获取新key
                $now_keys = $this->getFilesHashKey();
                // 获取旧key
                $old_keys = file_get_contents($this->config['root'].'/lock.key');
                $old_keys = explode('-', $old_keys);
                // 监测key值变化
                $diff = array_diff($now_keys, $old_keys);
                // 如果有文件发生了修改删除，编辑
                if (!empty($diff)) {
                    Log::info('发现文件变动，开始执行渲染...');
                    $files_key_str = implode('-', $now_keys);
                    // 执行渲染操作
                    $this->run();
                    file_put_contents($this->config['root'].'/lock.key', $files_key_str);
                    Log::info('['.date('H:i:s').']文件渲染已经完成!');
                    Log::info('['.date('H:i:s').']文件监测程序运行中...');
                }
            }
        } catch (Exception $e) {
            Log::info('['.date('H:i:s').']文件渲染错误，程序退出!');
            exit();
        }
    }
    /**** 文件变化监测程序 ***************************************************************/
}