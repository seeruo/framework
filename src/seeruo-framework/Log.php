<?php
namespace Seeruo;

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
        echo '[info]: ' . $desc . PHP_EOL;
        switch (strtoupper($type)) {
            case 'ERROR':
                exit();
                break;
            case 'THROW':
                throw new Exception($desc);
                break;
            default:
                break;
        }
    }

    static public function debug($data)
    {
        echo "<pre>";
        print_r($data);
        die();
    }
}