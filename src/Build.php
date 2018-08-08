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
    private $config;    // 配置文件
    private $themes_dir;      // 模版文件路径
    private $public_dir;      // 模版文件路径

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
            $this->buildStatic();
            # @生成文章信息
            $this->buildIndex();
            $this->buildArticles();
            Log::info('Complete build!');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::info('Build failed!');
        }
    }

    /**
     * 复制静态资源文件
     * @return [type] [description]
     */
    private function buildStatic()
    {
        $files = File::getFiles($this->themes_dir . 'static');
        array_walk($files, function ($file)
        {
            $to_path = str_replace($this->themes_dir.'static', $this->public_dir.'static', $file['file_path']);
            // Log::debug($to_path);
            File::copyFolder($file['file_path'], $to_path);
        });
    }

    /**
     * 构建主页静态文件
     */
    public function buildIndex()
    {
        $file['web_title'] = $this->config['title'];
        // 获取文件属性
        $files_list = $this->sortByTime();
        // 获取文件索引
        $index = $this->resolveIndex($files_list);
        // 获取文件内容
        $file['articles_list'] = $index;
        // Log::debug($file);
        $html = $this->render('index.tmp', $file);
        $file_path = $this->public_dir . 'index.html';
        File::createFile($file_path, $html);
    }

    /**
     * 构建文章静态文件
     */
    public function buildArticles()
    {
        // 获取文件属性
        $files_list = $this->sortByTime();
        // 获取文件索引
        $index = $this->resolveIndex($files_list);
        $site_title = $this->config['title'];
            // Log::debug($index);
        foreach ($files_list as $key => $file) {
            // 获取文件内容
            $file['web_title'] = $site_title;
            $file['content'] = File::getContent($file['file_path'])['content'];
            $file['articles_list'] = $index;
            $file['active'] = $key;
            // Log::debug($file);
            $html = $this->render('article.tmp', $file);
            File::createFile($file['public_dir'], $html);
        }
    }

    public function resolveIndex(&$files_list)
    {
        $index = [];
        foreach ($files_list as $key => &$file) {
            // 解析文件名
            $file_name = $file['file_name'];
            $file_type = substr(strrchr($file_name, '.'), 1);
            $file_name = basename($file_name, '.'.$file_type);
            // 时间解析
            $date = empty($file['date']) ? date('Y-m-d') : date('Y-m-d', strtotime($file['date']));
            $date_arr = explode('-', $date);
            $file_herf = 'articles' . DIRECTORY_SEPARATOR . $date_arr[0] . DIRECTORY_SEPARATOR . $date_arr[1] . DIRECTORY_SEPARATOR . $date_arr[2] . DIRECTORY_SEPARATOR . $file_name . DIRECTORY_SEPARATOR . 'index.html';
            $file['href'] = DIRECTORY_SEPARATOR . $file_herf;
            // Log::debug($file);
            $file['public_dir'] = $this->public_dir . $file_herf;
            $index[] = [
                'href'  => $file['href'],
                'title' => $file['title'],
                'alt'   => $file['title'],
                'active' => $key
            ];
        }
        return $index;
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
     * 对文件数组按时间先后顺序重新排序 冒泡排序
     * @param  [type] $file_list [description]
     * @return [type]            [description]
     */
    public function sortByTime()
    {
        $files = File::getFiles($this->source_dir);
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
        return $files;
    }

    /**
     * 模版解析
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
}