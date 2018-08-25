<?php
namespace Seeruo\Core;

use Exception;
use \Seeruo\Core\File;
use \Seeruo\Core\Cmd;
use \Seeruo\Core\Log;

/**
 * 开启本地服务器,用于预览博客，不能用于生产环境
 */
class Server 
{
    private $config;
    private $web_root;

    public function __construct($config){
        $this->config = $config;
        $this->app_root = $config['root'];
        $this->web_root = $config['public_dir'];
    }

    /**
     * [预览]
     * @return [type] [description]
     */
    public function preview()
    {
        try {
            if ( $this->config['auto_open'] ) {
                if (strstr(PHP_OS, 'WIN')) {
                    $win_cmd = 'explorer http://'. $this->config['server_address'];
                    Cmd::system($win_cmd, $this->config['root'], 'Open Explorer');
                }else{
                    $mac_cmd = 'open http://'. $this->config['server_address'];
                    Cmd::system($mac_cmd, $this->config['root'], 'Open Explorer');
                }
            }
            // 开启服务器
            Log::info('请浏览器里预览生成的网站效果，地址：http://'.$this->config['server_address']);
            $cmd = 'php -S ' . $this->config['server_address'] . ' -t ' . $this->web_root;
            Cmd::system($cmd, $this->config['root'], 'Create One WebServer');
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
    /**
     * [开发]
     * @return [type] [description]
     */
    public function develop()
    {
        try {
            if ( $this->config['auto_open'] ) {
                if (strstr(PHP_OS, 'WIN')) {
                    $win_cmd = 'explorer http://'. $this->config['server_address'];
                    Cmd::system($win_cmd, $this->config['root'], 'Open Explorer');
                }else{
                    $mac_cmd = 'open http://'. $this->config['server_address'];
                    Cmd::system($mac_cmd, $this->config['root'], 'Open Explorer');
                }
            }
            // 开启服务器
            Log::info('请浏览器里预览生成的网站效果，地址：http://'.$this->config['server_address']);
            $cmd = 'php -S ' . $this->config['server_address'] . ' -t ' . $this->app_root;
            Cmd::system($cmd, $this->config['root'], 'Create One WebServer');
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}