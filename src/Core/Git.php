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

/**
 * Git操作类
 */
class Git 
{
	private $_output; 			// 文件输出

    public function __construct($path, $log){
    	$this->_output = $log;
    	$this->_path   = $path;
    }
    // 初始仓库
    public function init($cmd='') {
        $cmd = 'git init';
        return $this->_cmd($cmd);
    }
    // 初始仓库
    public function Gclone($cmd='') {
        $cmd = "git clone https://github.com/cdking95/cdking95.github.io.git";
        return $this->_cmd( $cmd );
    }
    // 拉取代码
    public function pull($cmd='') {
        $cmd = "git pull https://github.com/cdking95/cdking95.github.io.git";
        return $this->_cmd( $cmd );
    }
    // 提交代码
    public function push($cmd='') {
        $cmd = "git push https://github.com/cdking95/cdking95.github.io.git";
        return $this->_cmd( $cmd );
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
        // die($cmd);
        $last_line = system($cmd, $retval);
        if ($last_line === false) {
            $this->_writeLog( $retval );
        }
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