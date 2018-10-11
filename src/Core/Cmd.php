<?php
namespace Seeruo\Core;

use Exception;
use \Seeruo\Core\log;

/**
 * 命令行类库
 */
class Cmd
{
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
