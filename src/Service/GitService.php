<?php
/**
 * This file is part of sxblog.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Danier<cdking95@gmail.com>
 */

namespace App\Service;

use Exception;

/**
 * Git操作类
 */
class Git 
{
    private $_output;           // 文件输出
    private $_address;          // 仓库地址

    public function __construct($config){
        $this->_output = $config['logs_dir']. '/' . $config['git_log_file'];
        $this->_path   = $config['public_dir'];
        $this->_address = $config['git_address'];
    }
    // 初始仓库
    public function init() {
        $this->_cmd( 'git init' );
    }
    // 初始仓库
    public function clone() {
        $cmd = 'git clone ' . $this->_address;
        $this->_cmd( $cmd );
    }
    public function remote()
    {
        $cmd = 'git remote add origin ' . $this->_address;
        $this->_cmd( $cmd );
    }
    // 拉取代码
    public function pull() {
        $cmd = 'git pull ' . $this->_address;
        $this->_cmd( $cmd );
    }
    public function add()
    {
        $this->_cmd( 'git add .' );
    }
    public function commit($msg = 'seeruo文章提交')
    {
        $msg = 'git commit -m "'.$msg.'"';
        $this->_cmd( $msg );
    }
    // 提交代码
    public function push() {
        $this->_cmd( 'git push -u origin master' );
    }
    /*
     * 运行命令行
     * 该方法可以运行命令行, 并会自动记录命令行日志
     * @param string $cmd 要运行的命令行
     * @return boolen
     */
    private function _cmd($cmd) {
        $path = str_replace("\\","/\\", $this->_path);
        $cmd = "cd ".$path." && ".$cmd;
        $last_line = system($cmd, $retval);
        $this->_writeLog( $retval );
        return $last_line;
    }
    /**
     * 记录日志
     * @param  string $string [日志内容]
     * @return [type]         [description]
     */
    private function _writeLog($msg) {
        $log = "-------------------------------------\n";
        $log .= date('Y-m-d H:i:s') . ":\n";
        $log .= $msg."\n";
        $log .= "-------------------------------------\n";
        file_put_contents($this->_output, $log, FILE_APPEND | LOCK_EX);
    }
}