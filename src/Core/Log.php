<?php
namespace Seeruo\Core;

use Exception;

/**
 * 日志
 */
class Log 
{
    static public function info($desc='', $type='INFO')
    {
        if (strstr(PHP_OS, 'WIN')) {
            $desc = iconv('UTF-8', 'gb2312', $desc);
        }
        
        switch (strtoupper($type)) {
            case 'ERROR':
                echo '[error]: ' . $desc . PHP_EOL;
                exit();
                break;
            case 'THROW':
                throw new Exception($desc);
                break;
            default:
                echo '[info]: ' . $desc . PHP_EOL;
                break;
        }
    }
}