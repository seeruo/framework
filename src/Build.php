<?php
namespace Seeruo;

use Exception;
use \Seeruo\File;
use \Seeruo\Log;

/**
 * Build
 */
class Build 
{
    private $config;            // 配置文件
    private $themes_dir;        // 模版文件路径
    private $public_dir;        // 模版文件路径
    private $source_dir;        // 源文件路径
    private $files;             // 文件组
    private $index;             // 索引组

    public function __construct($config){
        $this->config = $config;
        $this->themes_dir = $config['themes_dir'].DIRECTORY_SEPARATOR;
        $this->public_dir = $config['public_dir'].DIRECTORY_SEPARATOR;
        $this->source_dir = $config['source_dir'].DIRECTORY_SEPARATOR;
    }
    /**
     * 生成HTML文件,放到Public
     */
    public function run()
    {
        try {
            Log::info('Start build...');
            // @静态文件构建
            $this->buildStatic();
            // @解析文件设置
            $this->sortByTime();
            // @生成文章信息
            $this->buildIndex();
            $this->buildArticles();
            Log::info('Complete build!');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::info('Build failed!');
        }
    }

    /**
     * 静态资源文件转移
     * @return [type] [description]
     */
    private function buildStatic()
    {
        $files = File::getFiles($this->themes_dir . 'static');
        array_walk($files, function ($file)
        {
            $to_path = str_replace($this->themes_dir.'static', $this->public_dir.'static', $file['file_path']);
            File::copyFolder($file['file_path'], $to_path);
        });
    }
    /**
     * 构建主页静态文件
     */
    public function buildIndex()
    {
        // 设置页面变量
        $file = [];
        // 检查是否设置了主页
        $file_path = $this->source_dir . $this->config['home_page'];
        if ( !empty($this->config['home_page']) && file_exists($file_path) ) {
            // markdown 2 html
            $file = $this->files[md5($file_path)];
            $file_data = File::getContent($file['file_path']);
            $file = $file_data['setting'];
            $Parsedown = new \Parsedown();
            $file['content'] = $Parsedown->text( $file_data['content'] );
            $file['articles_list'] = $this->index;
        }else{
            $file['articles_list'] = $this->index;
        }
        $file['web_title'] = $this->config['title'];  // 网站标题
        $html = $this->render('index.tmp', $file);
        $file_path = $this->public_dir . 'index.html';
        File::createFile($file_path, $html);
    }

    /**
     * 构建文章静态文件
     */
    public function buildArticles()
    {
        // 解析文件索引
        $site_title = $this->config['title'];
        $Parsedown = new \Parsedown();
        foreach ($this->files as $key => $file) {
            $file_data = File::getContent($file['file_path']);
            $Parsedown = new \Parsedown();
            $file['content'] = $Parsedown->text( $file_data['content'] );
            $file['articles_list'] = $this->index;
            $file['active_key'] = $key;
            $html = $this->render('article.tmp', $file);
            // Log::debug($html);
            File::createFile($file['public_dir'], $html);
        }
    }

    /**
     * 生成文件路径名:已时间归档的方式构建
     * @param  [type] $file_conf [文件配置]
     * @return [type]            [description]
     */
    private function makeViewFileName($file_conf)
    {
        // 静态文件存放基目录
        $base_path = $this->public . DIRECTORY_SEPARATOR; 
        // 解析文件名
        $file_name = $file_conf['file_name'];
        $file_type = substr(strrchr($file_name, '.'), 1);
        $file_name = basename($file_name, '.'.$file_type);
        // 时间解析
        $date = empty($file_conf['date']) ? date('Y-m-d') : date('Y-m-d', strtotime($file_conf['date']));
        $date_array = explode('-', $date);
        $file_path = $base_path . $date_array[0] . DIRECTORY_SEPARATOR . $date_array[1] . DIRECTORY_SEPARATOR . $date_array[2] . DIRECTORY_SEPARATOR . $file_name . DIRECTORY_SEPARATOR . 'index.html';
        return $file_path;
    }

    /**
     * 对文件数组按时间先后顺序重新排序，并去除是主页的文件, 设置索引
     * @param  [type] $file_list [description]
     * @return [type]            [description]
     */
    public function sortByTime()
    {
        // 获取文件列表
        $files = File::getFiles($this->source_dir);
        // 文件按时间排序
        $length = count($files); // 文件数量
        for($i=1; $i<$length; $i++) {
            for($j=0; $j<$length - $i; $j++){
                if ( (int)strtotime($files[$j]['date']) >  (int)strtotime($files[$j+1]['date'])) {
                    $temp = $files[$j+1];
                    $files[$j+1] = $files[$j];
                    $files[$j] = $temp;
                }
            }
        }
        // 解析文章索引
        $index = [];
        $new_files = []; // 新的文件数组
        foreach ($files as $key => $file) {
            // 解析文件名
            $file_name = $file['file_name'];
            $file_type = substr(strrchr($file_name, '.'), 1);
            $file_name = basename($file_name, '.'.$file_type);
            // 时间解析
            $date = empty($file['date']) ? date('Y-m-d') : date('Y-m-d', strtotime($file['date']));
            $date_arr = explode('-', $date);
            if (isset($this->config['web_develop'])) {
                $file_herf = 'index.php/'.str_replace($this->source_dir, '', $file['file_path']);
            }else{
                $file_herf = 'articles/' . $date_arr[0] . '/' . $date_arr[1] . '.' . $date_arr[2] . '/' . $file_name . '/' . 'index.html';
            }
            $file['href'] = '/' . $file_herf;                       // 文章URL
            $file['public_dir'] = $this->public_dir . $file_herf;   // 文件生成路径
            $file['web_title'] = $this->config['title'];            // 网站标题
            $file['page_uuid'] = md5($file['file_path']);
            $new_files[$file['page_uuid']] = $file;

            // 检查是否为首页
            $home_page = $this->source_dir . $this->config['home_page'];
            if ( !empty($this->config['home_page']) && file_exists($home_page) ) {
                if ($home_page == $file['file_path']) {
                    continue;
                }
            }

            // 解析文件路径
            $index[$file['page_uuid']] = [
                'href'  => $file['href'],
                'title' => $file['title'],
                'uuid'  => $file['page_uuid']
            ];
        }
        $this->index = $index;
        $this->files = $new_files;
    }

    /**
     * 模版解析
     */
    public function render($file, $data=[])
    {
        \Twig_Autoloader::register();
        // Log::debug($file);
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

    /**********************************************************************************************************************
     * 网页模式
     **********************************************************************************************************************/
    /**
     * 网页模式入口
     */
    public function web()
    {
        // @解析文件设置
        $this->sortByTime();
        $static_dir = str_replace($this->config['root'], '', $this->config['themes_dir']);
        $static_dir = str_replace('\\', '/', $static_dir);

        $html = $this->buildPage();
        $html = str_replace('/static/', $static_dir.'/static/', $html);
        echo $html;
    }

    /**
     * 构建主页静态文件
     */
    public function buildPage()
    {
        // 路由
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        // 设置页面变量
        if ($path_info === '/') {
            // 检查是否设置了主页
            $home_page = $this->source_dir . $this->config['home_page'];
            if ( !empty($this->config['home_page']) && file_exists($home_page) ) {
                $file = $this->files[md5($home_page)];
                $file_data = File::getContent($file['file_path']);
                $Parsedown = new \Parsedown();
                $file['content'] = $Parsedown->text( $file_data['content'] );
                $file['articles_list'] = $this->index;
            }else{
                $file = [];
                $file['articles_list'] = $this->index;
                $file['web_title'] = $this->config['title'];  // 网站标题
            }
            $html = $this->render('index.tmp', $file);
        }else{
            $path_info = substr($path_info, 1);
            $file_path = $this->source_dir . $path_info;
            $file = $this->files[md5($file_path)];
            $file_data = File::getContent($file['file_path']);
            $Parsedown = new \Parsedown();
            $file['content'] = $Parsedown->text($file_data['content']);
            $file['articles_list'] = $this->index;
            $html = $this->render('article.tmp', $file);
        }
        return $html;
    }
}