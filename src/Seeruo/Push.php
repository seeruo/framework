<?php
namespace Seeruo;

use Exception;
use \Seeruo\Cmd;
use \Seeruo\Log;

/**
 * 推送镜头文件到服务器
 */
class Push 
{
    private $config;
    private $web_root;

    public function __construct($config){
        $this->config = $config;
        $this->web_root = $config['public_dir'].DIRECTORY_SEPARATOR;
    }
    /**
     * [run description]
     * @return [type] [description]
     */
    public function run()
    {
    	if (!isset($this->config['push_type'])) {
            Log::info( '你没有配置推送方式，请在Config.php文件里面配置：' );
            Log::info( '$config[\'push_type\']=\'git/ssh\';' );
            Log::info( '$config[\'push_user\']=\'root\';' );
            Log::info( '$config[\'push_address\']=\'127.0.0.1\';' );
            Log::info( '$config[\'push_path\']=\'/usr/www/html\';' );
            die();
        }
        switch ($this->config['push_type']) {
            case 'git':
                $this->pushGit();
                break;
            case 'ssh':
                $this->pushSsh();
                break;
            default:
                Log::info( '推送方式配置错误，请参照如下方式配置：' );
                Log::info( '$config[\'push_type\']=\'git/ssh\'; //推送方式暂时只支持 git 和 ssh' );
    			break;
    	}
    }
    /**
     * [Git推送方式]
     * @return [type] [description]
     */
    private function pushSsh()
    {
    	if (!isset($this->config['push_user'])) {
            Log::info( 'SSH账户没有配置，请在Config.php文件参照如下方式配置：' );
            Log::info( '$config[\'push_user\']=\'root\';' );
    		die();
    	}
    	if (!isset($this->config['push_address'])) {
    		Log::info( 'SSH地址没有配置，请在Config.php文件参照如下方式配置：' );
    		Log::info( '$config[\'push_address\']=\'127.0.0.1\';' );
    		die();
    	}
    	if (!isset($this->config['push_path'])) {
    		Log::info( 'SSH路径没有配置，请在Config.php文件参照如下方式配置：' );
    		Log::info( '$config[\'push_path\']=\'/usr/www/html\';' );
    		die();
    	}

        if (strstr(PHP_OS, 'WIN')) {
            $this->web_root = str_replace("\\","/", $this->web_root);
            $this->web_root = iconv('UTF-8', 'gbk', $this->web_root);
        }
    	$cmd = 'scp -r '. $this->web_root . '* ';
    	$cmd .= $this->config['push_user'] . '@'.$this->config['push_address'] . ':';
    	$cmd .= $this->config['push_path'];
        // die($cmd);
        Cmd::system($cmd, $this->web_root, 'Publish Web');
    }
    /**
     * [Git推送方式]
     * @return [type] [description]
     */
    private function pushGit()
    {
        Log::info( "开发中...!" );
    }
}





