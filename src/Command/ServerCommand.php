<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Exception;


class ServerCommand extends Command
{
    /**
     * 指令名称
     */
    protected static $defaultName = 'server';
    /**
     * 文件系统
     */
    protected $fileSystem;
    /**
     * 配置
     */
    protected $config;

    public function __construct()
    {
        $this->config = store('config');
        $this->fileSystem = new Filesystem();
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('开启一个本地调试服务器')
            ;
    }
    /**
     * [执行命令]
     * @DateTime 2018-12-13
     * @param    InputInterface  $input  输入对象
     * @param    OutputInterface $output 输出对象
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $port   = $this->config['port'] ?: 9001;
            $public = $this->config['public'] ?: 'Public';

            $output->writeln([
                '<bg=red;>=================================</>',
                '请在浏览器里预览网站效果',
                '地址：http://localhost:'.$port,
                '<bg=red;>=================================</>'
            ]);
            // open explorer
            if ( $this->config['auto_open'] ) {
                if (strstr(PHP_OS, 'WIN')) {
                    $win_cmd = "cd ".ROOT." && ". 'explorer http://localhost:'.$port;
                    system($win_cmd);
                }else{
                    $mac_cmd = "cd ".ROOT." && ". 'open http://localhost:'.$port;
                    system($mac_cmd);
                }
            }
            // 为了减少复杂度，直接使用php自带的调试服务器
            $cmd = "cd ".ROOT." && ". 'php -S ' . 'localhost:'.$port . ' -t ' . $public;
            system($cmd);
        } catch (Exception $e) {
            $output->writeln('<bg=red;>Start Server Error</>');
        }
    }
}