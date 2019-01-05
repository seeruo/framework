<?php
/**
 * This file is part of SxBlog.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Danier<cdking95@gmail.com>
 */

namespace App\Library;

use Exception;

/**
 * 日志
 */
class Log 
{
    static public function info($desc='')
    {
        if (strstr(PHP_OS, 'WIN')) {
            $desc = iconv('UTF-8', 'gb2312', $desc);
        }
        echo '[info]: ' . $desc . PHP_EOL;
    }
}