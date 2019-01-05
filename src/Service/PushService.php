<?php
/**
 * This file is part of sxblog.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Danier<cdking95@gmail.com>
 */

namespace App\Service;

use App\Library\Log;
use App\Service\GitService;
use App\Service\BuildService;
use Exception;

/**
 * 推送镜头文件到服务器
 */
class PushService
{
    private $config;

    public function __construct($config){
        $this->config = $config;
    }

    public function run($type='ssh')
    {
        switch (strtolower($type)) {
            case 'ssh':
                $this->pushServer();
                break;
            case 'git':
                $this->pushGit();
                break;
            case 'init':
                $this->pushGitInit();
                break;
            default:
                Log::info( '推送方式错误，只能使用ssh | git' );
                Log::info( '帮助: php seeruo push -h' );
                die();
                break;
        }
    }
    /**
     * [Git推送方式]
     * @return [type] [description]
     */
    public function pushServer()
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

        // 网站根目录
        $web_root = ROOT.DIRECTORY_SEPARATOR.$this->config['public'].DIRECTORY_SEPARATOR;
        if (strstr(PHP_OS, 'WIN')) {
            $web_root = str_replace("\\","/", $web_root);
            $web_root = iconv('UTF-8', 'gbk', $web_root);
        }
        // 上传指令
        $cmd = 'scp -r '. $web_root . '* ';
        $cmd .= $this->config['ssh_user'] . '@'.$this->config['ssh_address'] . ':';
        $cmd .= $this->config['ssh_path'];
        // 最终需要执行的命令
        $cmd = "cd ".$web_root." && ".$cmd;
        system($cmd, $out);
        if ($out === false) {
            throw new Exception('指令执行失败，请坚持是否配置错误');
        }
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
        $Git = new GitService($this->config);

                        
        $Git->add();
        $Git->commit();
        $Git->push();
    }

    /**
     * [Git仓库初始化]
     * @return [type] [description]
     */
    public function pushGitInit()
    {
        if (!isset($this->config['git_address'])) {
            Log::info('Git地址没有配置，请在Config.php文件参照如下方式配置：' );
            Log::info('$config[\'git_address\']=\'git@github.com:seeruo/seeruo.github.io.git\';','error');
        }
        $Git = new GitService($this->config);

        $Git->init();
        $Git->remote();
        $Git->pull();
        
        // 重新构建一次
        $build = new BuildService();
        $build->run();
    }
}
