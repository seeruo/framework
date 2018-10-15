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