<?php
namespace Seeruo;

use Exception;
use \Seeruo\Core\Cmd;
use \Seeruo\Core\Log;
use \Seeruo\Core\File;
use \Seeruo\Core\Init;
use \Seeruo\Core\Git;
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
        // 基础设置 :: 必填
        'title'             => 'SeeRuo',                    // 网站名称
        'author'            => 'Danier(左浪)',               // 您的名字
        'url'               => 'http://www.example.com',    // 网站URL
        'license'           => '本博客所有文章除特别声明外，转载请注明出处！',
        'themes'            => 'default',                   // 网站主题
        'page_limit'        => 10,                          // 分页条数

        // 本地调试设置::必填
        'server_address'    => '127.0.0.1',                 // 本地服务器调试地址
        'server_port'       => '9001',                      // 本地服务器调试地址
        'auto_open'         => false,                       // 自动打开浏览器

        // 发布到服务器空间 :: [云服务器] 上传必填
        // 需要服务器开启的SSH支持
        // 'ssh_type'          => 'ssh',                       // 
        // 'ssh_user'          => 'root',                      // SSH账号
        // 'ssh_address'       => '127.0.0.1',                 // SSH推送地址
        // 'ssh_path'          => '/var/www/html/blog',        // SSH服务器网站根路径,该路径需开启写权限
        
        // 发布到github page :: [git] 上传必填
        // 需要github上添加"SSH keys",授权电脑免密操作
        // 'git_address'       => 'git@github.com:seeruo/seeruo.github.io.git',  // 仓库地址
        // 'git_log_file'      => 'git_log.log',    // git日志记录

        // 单页配置 :: 选填
        // 'home_page'         => '',                          // 是否需要使用md文件作为主页 '单页/home.md'
        // 单独解析的md文件，解析路径为 url/'你设置的链接'
        // 'single_pages'      => [   
        //     'about'             => '单页/about.md',          // 路径为 http://www.example.com/about
        //     'linker'            => '单页/linker.md'          // 路径为 http://www.example.com/linker
        // ],    
    ];

    /**
     * 构造：接受配置，设置各项初始
     */
    public function __construct($root)
    {
        //设置中国时区 
        date_default_timezone_set('PRC');

        // 设置项目根路径
        $this->config['root'] = $root;

        // 配置相关的资源全部放在Config里面
        $this->config['config_root'] = $root.'/Config';

        // 合并配置
        $config = @include_once $this->config['config_root'].'/config.php';
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }
        
        // 目录配置
        $this->config['public_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Public';
        $this->config['themes_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Themes'.DIRECTORY_SEPARATOR.$this->config['themes'];
        $this->config['source_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Source';
        $this->config['plugin_dir'] = $this->config['root'].DIRECTORY_SEPARATOR.'Plugins';
        $this->config['logs_dir']   = $this->config['root'].DIRECTORY_SEPARATOR.'Logs';


        // 检查配置
        if (empty($this->config['root'])) {
            die('root:缺少值'.PHP_EOL);
        }
        // 检查git日志文件释放配置
        $this->config['git_log_file'] = $this->config['git_log_file'] ?? 'seeruo_git.log';
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
            // 创建一个markdown文件
            case 'create':
                $fileName = @$argv[2] ?: '';
                if (empty($fileName)) {
                    $fileName = 'file_'.date('Ymdhis');
                }
                $Create = new Create($this->config);
                $Create->run($fileName);
                break;
            // 构建用于生产环境的静态网站文件
            case 'build':
                $Build = new Build($this->config);
                $model = @$argv[2] ?: '';
                if (trim($model) === '-s') {
                    $Build->listen();
                }else{
                    $Build->run();
                }
                break;
            // 开启本地调试服务器
            case 'server':
                $Server = new Server($this->config);
                $Server->listen();
                break;
            // 上传至服务器
            case 'ssh':
                $models = ['init','push'];
                $model = @$argv[2] ?: '';
                if (empty($model) || !in_array($model, $models)) {
                    Log::info( 'Command error.', 'error');
                }
                $Push = new Push($this->config);
                switch ($model) {
                    case 'push':
                        $Push->pushSsh();
                        break;
                    default:
                        break;
                }
                break;
            // 上传至服务器
            case 'git':
                $models = ['init','push'];
                $model = @$argv[2] ?: '';
                if (empty($model) || !in_array($model, $models)) {
                    Log::info( 'Command error.', 'error');
                }
                $Push = new Push($this->config);
                switch ($model) {
                    case 'init':
                        // 删除文件目录
                        File::deleteDir($this->config['public_dir']);
                        // 创建空目录
                        File::addDir($this->config['public_dir']);
                        // 创建日志文件
                        $log_file = $this->config['logs_dir']. '/' . $this->config['git_log_file'];
                        if (!file_exists($log_file)) {
                            File::createFile($log_file, 'Git日志文件');
                        }
                        // 拉取远程仓库
                        $Git = new Git($this->config);
                        $Git->init();
                        $Git->remote();
                        $Git->pull();
                        // 重新构建一次
                        $Build = new Build($this->config);
                        $Build->run();
                        break;
                    case 'push':
                        $Push->pushGit();
                        break;
                    default:
                        break;
                }
                break;
            // 命令帮助
            default:
                $usage = "===========================================================\n";
                $usage.= "Usage: Commands [mode] \n\n";
                $usage.= "Commands:\n";
                $usage.= "create\t\t\t 创建一个markdown文件（默认时间格式创建）.\n";
                $usage.= "\t'file_name'\t 文件名称(不需要文件后缀).\n";
                $usage.= "build \t\t\t 构建用于生产环境的静态网站文件(默认构建一次).\n";
                $usage.= "\t-s  \t\t 实时构建.\n";
                $usage.= "server\t\t\t 开启一个本地调试服务器.\n";
                $usage.= "git  \t\t\t 以git的方式上传文件.\n";
                $usage.= "\tinit \t\t 初始化仓库.\n";
                $usage.= "\tpush \t\t 发布网站.\n\n";
                $usage.= "ssh  \t\t\t 以ssh的方式上传文件.\n";
                $usage.= "\tpush \t\t 发布网站.\n\n";
                $usage.= "Use \"--help\" for more information about a command.\n";
                $usage.= "===========================================================\n";
                exit($usage);
        }
    }
}