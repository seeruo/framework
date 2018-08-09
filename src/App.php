<?php
namespace Seeruo;

use Exception;
use \Seeruo\Cmd;
use \Seeruo\Log;

/**
* 框架主类
*/
class App
{
    /**
     * 初始配置
     */
	private $config = [
        // 基础设置::必填
        'root'              => '',                  // 应用根路径
        // 以下配置根据情况选填
        'title'             => '我的博客',          // 网站名称
        'url'               => '',                  // 网站URL
        'themes'            => 'default',           // 网站主题
        'author'            => 'danier',            // 您的名字
        'home_page'         => '',                  // 主页文件
        // 本地调试设置
        'server_address'    => 'localhost:9001',    // 本地服务器调试地址
        'auto_open'         => false,               // 自动打开浏览器
        // 发布到服务器空间
        'push_type'         => 'ssh',               // 推送到服务器的方式 ssh/git
        'push_user'         => 'root',              // SSH账号
        'push_address'      => '127.0.0.1',         // SSH推送地址
        'push_path'         => '/var/www/html/blog',// SSH推送路径,该服务器的路径必须要开启写权限
    ];

    /**
     * 构造：接受配置，设置各项初始
     */
    public function __construct($config = [])
    {
        date_default_timezone_set('PRC'); //设置中国时区 
        // 合并配置
    	if ($config) {
    		$this->config = array_merge($this->config, $config);
    	}
        
        // 目录设置
        $this->config['public_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'_Public';
        $this->config['themes_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Themes'.DIRECTORY_SEPARATOR.$this->config['themes'];
        $this->config['source_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Source';

        // 检查配置
        if (empty($this->config['root'])) {
            die('root:缺少值'.PHP_EOL);
        }
    }

    /**
     * 执行入口
     */
    public function run()
    {
        // 解析命令
        Cmd::parse();
        // 检测需要执行的指令
        // 帮助
        if (Cmd::has('h') || Cmd::has('help')) {
            Cmd::help();
            exit;
        }
        // 初始化
        if (Cmd::has('init')) {
            $model = new \Seeruo\Init($this->config);
            $model->run();
            exit;
        }
        // 创建模板文件
        if (Cmd::has('c') || Cmd::has('create')) {
            $fileName = Cmd::has('c') ? Cmd::get('c') : Cmd::get('create');
            if (empty($fileName)) {
                Log::info( 'Miss FileName!', 'error');
            }
            $model = new \Seeruo\Create($this->config);
            $model->run($fileName);
            exit;
        }
        // 构建静态文件
        if (Cmd::has('b') || Cmd::has('build')) {
            $model = new \Seeruo\Build($this->config);
            $model->run();
            exit;
        }
        // 本地调试服务器
        if (Cmd::has('s') || Cmd::has('server')) {
            $model = new \Seeruo\Server($this->config);
            $model->run();
            exit;
        }
        // 发布网站到服务器
        if (Cmd::has('p') || Cmd::has('push')) {
            $model = new \Seeruo\Push($this->config);
            $model->run();
            exit;
        }
    }
}