<?php
namespace Seeruo;

use Exception;
use \Seeruo\Core\Cmd;
use \Seeruo\Core\Log;
use \Seeruo\Core\Init;
use \Seeruo\Core\Create;
use \Seeruo\Core\Server;
use \Seeruo\Core\Push;
use \Seeruo\Core\Build;
use \Seeruo\Core\HooksMan;

// 公共函数
include 'Common/common.php';

/**
* 框架主类
*/
class Worker
{
    /**
     * 初始配置
     */
    private $config = [
        // 基础设置::必填
        'root'              => '',                  // 应用根路径
        // 以下配置根据情况选填
        'title'             => '我的博客',           // 网站名称
        'base_url'          => 'http://www.codegrids.com', // 网站URL
        'themes'            => 'default',           // 网站主题
        'author'            => 'your name',         // 您的名字
        'home_page'         => '',                  // 主页
        'single_pages'      => [],                  // 自定义单页
        // 本地调试设置
        // 'server_address'    => 'localhost:9001',    // 本地服务器调试地址
        'server_address'    => '127.0.0.1',         // 本地服务器调试地址
        'server_port'       => '9001',              // 本地服务器调试地址
        'auto_open'         => false,               // 自动打开浏览器
        // 发布到服务器空间
        'push_type'         => 'ssh',               // 暂时只支持ssh方式，需要服务器开启的SSH支持
        'push_user'         => 'root',              // SSH账号
        'push_address'      => '127.0.0.1',         // SSH推送地址
        'push_path'         => '/var/www/html/blog',// SSH服务器网站根路径,该路径需开启写权限
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
        $this->config['plugins_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Plugins';

        // 检查配置
        if (empty($this->config['root'])) {
            die('root:缺少值'.PHP_EOL);
        }

        // 初始钩子
        HooksMan::getinstance($this->config);
    }

    /**
     * [parseCmd 解析命令参数]
     * @DateTime 2018-09-20
     * @return   [type]     [description]
     */
    public function run()
    {
        global $argv;
        $command = isset($argv[1]) ? $argv[1] : '';
        // $command2 = isset($argv[2]) ? $argv[2] : '';
        // 解析指令
        switch ($command) {
            // 初始化本地环境
            case 'init':
                $Init = new Init($this->config);
                $Init->run();
                break;
            // 开启调试模式
            case 'start':
                $Build = new Build($this->config);
                $Build->listen();
                break;
            // 创建一个markdown文件
            case 'create':
                $fileName = @$argv[2] ?: '';
                if (empty($fileName)) {
                    Log::info( 'Command error.', 'error');
                }
                $Create = new Create($this->config);
                $Create->run($fileName);
                break;
            // 开启本地调试服务器
            case 'server':
                $Server = new Server($this->config);
                $Server->listen();
                break;
            // 构建用于生产环境的静态网站文件
            case 'build':
                $Build = new Build($this->config);
                $Build->run();
                break;
            // 上传至服务器
            case 'push':
                // $model = @$argv[2] ?: '';
                // if (empty($model)) {
                //     Log::info( 'Command error.', 'error');
                // }
                $Push = new Push($this->config);
                $Push->run();
                break;
            // 命令帮助
            default:
                $usage = "===========================================================\n";
                $usage.= "Usage: Commands [mode] \n\n";
                $usage.= "Commands:\n";
                $usage.= "init  \t\t\t 初始化本地环境.\n";
                $usage.= "start \t\t\t 开启调试模式.\n";
                $usage.= "create\t\t\t 创建一个markdown文件.\n";
                $usage.= "\t'file_name'\t 文件名称(不需要文件后缀).\n";
                $usage.= "server\t\t\t 开启一个本地调试服务器.\n";
                $usage.= "build \t\t\t 构建用于生产环境的静态网站文件.\n";
                $usage.= "push  \t\t\t 上传文件到生产环境.\n";
                $usage.= "\t-ftp \t\t 以sftp的方式上传文件.\n";
                $usage.= "\t-git \t\t 以git的方式上传文件.\n\n";
                $usage.= "Use \"--help\" for more information about a command.\n";
                $usage.= "===========================================================\n";
                exit($usage);
        }
    }
}