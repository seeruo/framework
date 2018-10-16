<?php
/**
 * This file is part of seeruo.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Danier<cdking95@gmail.com>
 */
namespace Seeruo\Core;

use Exception;
use \Seeruo\Core\Cmd;
use \Seeruo\Core\Git;
use \Seeruo\Core\Log;

/**
 * 推送镜头文件到服务器
 */
class Push 
{
    private $config;
    private $web_root;

    public function __construct($config){
        $this->config = $config;
    }
    /**
     * [Git推送方式]
     * @return [type] [description]
     */
    private function pushSsh()
    {
    	if (!isset($this->config['ssh_user'])) {
            Log::info( 'SSH账户没有配置，请在Config.php文件参照如下方式配置：' );
            Log::info( '$config[\'ssh_user\']=\'root\';' );
    		die();
    	}
    	if (!isset($this->config['ssh_address'])) {
    		Log::info( 'SSH地址没有配置，请在Config.php文件参照如下方式配置：' );
    		Log::info( '$config[\'ssh_address\']=\'127.0.0.1\';' );
    		die();
    	}
    	if (!isset($this->config['ssh_path'])) {
    		Log::info( 'SSH路径没有配置，请在Config.php文件参照如下方式配置：' );
    		Log::info( '$config[\'ssh_path\']=\'/usr/www/html\';' );
    		die();
    	}

        $web_root = $this->config['public_dir'].DIRECTORY_SEPARATOR;
        if (strstr(PHP_OS, 'WIN')) {
            $web_root = str_replace("\\","/", $web_root);
            $web_root = iconv('UTF-8', 'gbk', $web_root);
        }
    	$cmd = 'scp -r '. $web_root . '* ';
    	$cmd .= $this->config['ssh_user'] . '@'.$this->config['ssh_address'] . ':';
    	$cmd .= $this->config['ssh_path'];
        Cmd::system($cmd, $web_root, 'Publish Web');
    }
    /**
     * [Git推送方式]
     * @return [type] [description]
     */
    public function pushGit()
    {
        if (!isset($this->config['git_address'])) {
            Log::info('Git地址没有配置，请在Config.php文件参照如下方式配置：' );
            Log::info('$config[\'git_address\']=\'git@github.com:seeruo/seeruo.github.io.git\';','error');
        }
        $Git = new Git($this->config);
        $Git->add();
        $Git->commit();
        $Git->push();
    }
}





