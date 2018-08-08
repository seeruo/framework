<?php
namespace Seeruo;

use Exception;
use \Seeruo\log;
/**
 * 命令行类库
 */
class Cmd
{
    /**
     * 接收的指令
     * @var array
     */
    private static $options = [
        ['com' => 'h',      'args' => false  ,'desc' => '帮助'],
        ['com' => 'help',   'args' => false  ,'desc' => '-h'],
        ['com' => 'init',   'args' => false  ,'desc' => '初始化仓库及博客结构'],
        ['com' => 'b',      'args' => false  ,'desc' => '构建所有资源为静态html文件'],
        ['com' => 'build',  'args' => false  ,'desc' => '-b'],
        ['com' => 's',      'args' => false  ,'desc' => '创建本地服务器监听'],
        ['com' => 'server', 'args' => false  ,'desc' => '-s'],
        ['com' => 'c',      'args' => true   ,'desc' => '新建一片文章模板'],
        ['com' => 'create', 'args' => true   ,'desc' => '-c'],
        ['com' => 'p',      'args' => false  ,'desc' => '发布Blog,推送静态文件到服务器'],
        ['com' => 'push',   'args' => false  ,'desc' => '-p'],
    ];

    /**
     * 指令集
     */
    private static $command;

    /**
     * 解析参数
     */
    public static function parse()
    {
        $opt_str = '';
        $opt_arr = [];
        foreach (self::$options as $o) {
            $command = $o['com'];
            if (isset($o['args']) && $o['args'] === true) {
                $command .= ':';
            }
            if (strlen($o['com']) == 1) {
                $opt_str .= $command;
            } else {
                $opt_arr[] = $command;
            }
        }
        self::$command = getopt($opt_str, $opt_arr);
        // print_r(self::$command);die();
    }
    /**
     * 检测指令是否正确
     * @param  string  $com [指令]
     * @return boolean      [是否有值]
     */
    public static function has($com='')
    {
        return isset(self::$command[$com]);
    }
    /**
     * 获取指令的参数
     * @param  [type]   $com [指令]
     * @return [boolean]      [参数值]
     */
    public static function get($com)
    {
        if (isset(self::$command[$com]) && self::$command[$com]) {
            return self::$command[$com];
        }
    }
    /**
     * 获取帮助
     * @return [type] [description]
     */
    public static function help()
    {
        foreach (self::$options as $o) {
            if (!isset($o['desc'])) {
                continue;
            }
            $pre = '-';
            $len = strlen($o['com']);
            if (strlen($o['com']) > 1) {
                $len++;
                $pre .= '-';
            }
            $desc = $pre.$o['com'];

            $desc .= str_repeat(' ', 15 - $len);
            $desc .= $o['desc'];
            
            Log::info( $desc );
        }
    }

    /**
     * [以system运行命令]
     * @param  [type] $cmd  [指令]
     * @param  string $desc [指令描述]
     * @param  [type] $path [执行路径]
     * @return [type]       [description]
     */
    public static function system($cmd, $path, $desc='Create Process')
    {
        Log::info($desc);
        Log::info("Process runing...");
        $path = str_replace("\\","/\\", $path);
        $cmd = "cd ".$path." && ".$cmd;
        $last_line = system($cmd, $output);
        if ($last_line === false) {
            // Log::info( $output, 'error');
        }
        Log::info("Process done!");
    }
    /**
     * [以proc_open运行命令]
     * @param  [type] $cmd  [指令]
     * @param  string $desc [指令描述]
     * @param  [type] $path [执行路径]
     * @return [type]       [description]
     */
    public static function proc($cmd, $path, $desc='Create Process') {
        // 初始化命令执行地址
        $descriptorspec = array(
           0 => array("pipe", "r"), 
           1 => array("pipe", "w"), 
           2 => array("pipe", "r")
        );
        $process = proc_open(escapeshellarg($cmd), $descriptorspec, $pipes, $path, null); //run test_gen.php
        Log::info($desc);
        if (is_resource($process))
        {
            Log::info("Process runing...");
            fclose( $pipes[0] );
            stream_set_blocking($pipes[2], 0);
            $stream = $pipes[1];
            print_r(fgets($stream,4096));
            while ( ($buf = fgets($stream,4096)) ) {
                Log::info($buf);
                $stderr = fread($pipes[2], 4096);
                if( !empty( $stderr ) ) {
                    Log::info( $stderr, 'error');
                }
            }
            fclose( $pipes[1] );
            fclose( $pipes[2] );
            $return_value = proc_close($process);  //stop test_gen.php
            if ($return_value) {
                Log::info("Process done!");
            }else{
                Log::info('error');
            }
        } else {
            Log::info("Command empty...:");
        }
    }
}
