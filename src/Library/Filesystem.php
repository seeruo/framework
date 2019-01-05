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

use Symfony\Component\Filesystem\Filesystem as SymFilesystem;
use App\Library\Log;
use Exception;

/**
 * 文件操作
 */
class Filesystem extends SymFilesystem
{
    /**
     * 创建文件
     * @param  [type] $file_path[文件路径]
     * @param  string $content  [文件内容]
     */
    public function create($file_path, $content='')
    {
        $file_dir = dirname($file_path);
        $new_path = $file_path;
        if (strstr(PHP_OS, 'WIN')) {
            $file_dir = mb_convert_encoding($file_dir, 'gbk', 'UTF-8');
            $new_path = mb_convert_encoding($file_path, 'gbk', 'UTF-8');
        }
        $this->exists($file_dir) || $this->mkdir($file_dir, 0777);
        // 创建文件
        $status = file_put_contents($new_path, $content, LOCK_EX);
        if ($status === false) {
            throw new Exception('Create '.$file_path.' failed!');
        }
        Log::info('Create '.$file_path.' done!');
    }
    /**
     * 获取文件内容
     * @param  [type] $fileName [文件名]
     */
    public function getContent($file_path)
    {
        $file = []; // 文件解析完之后的数据
        $file_path = trim($file_path);
        // 初始配置
        $file_set = [
            'file_name' => basename($file_path),
            'file_dire' => dirname($file_path),
            'file_path' => dirname($file_path). DIRECTORY_SEPARATOR . basename($file_path),
            'title'     => '',
            'date'      => date('Y-m-d'),
            'tags'      => '',
            'type'      => '',
            'author'    => '',
            'linker'    => '',
            'license'   => '',
        ];
        // 检查是否为md文件，不是的话不做后续解析
        $string = file_get_contents($file_path);
        $string = explode("---", trim($string));
        $string = array_filter($string);
        $setting = isset($string[1]) ? $string[1] : ''; // 文章配置
        unset($string[0], $string[1]);
        $content = implode($string, "---");

        // 文章配置
        $setting = explode("\n", trim($setting));
        $setting = array_filter($setting);
        foreach ($setting as $key => $v) {
            $s = explode(":", trim($v));
            $key = trim($s[0]);
            unset($s[0]);
            $value = trim( implode($s, ':') );
            if (!empty($value)) {
                $file_set[$key] = $value;
            }
        }

        // 处理文章标签
        if (isset($file_set['tags'])) {
            $file_set['tags'] = array_filter(explode(',', $file_set['tags']));
        }

        $file['setting'] = $file_set;
        $file['content'] = $content;
        return $file;
    }

    /**
     * 获取文件列表
     * @param  [type] $directory [文件仓库路径]
     * @return [type]            [所有文件列表]
     */
    public function getFiles($directory='') {
        if (empty($directory)) {
            throw new Exception("without $directory param");
        }
        if($dir = opendir($directory)) {
            $tmp = Array();
            while($file = readdir($dir)) {
                if($file != "." && $file != ".." && $file[0] != '.') {
                    if(is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                        $tmp2 = $this->getFiles($directory . DIRECTORY_SEPARATOR . $file);
                        if(is_array($tmp2)) {
                            $tmp = array_merge($tmp, $tmp2);
                        }
                    } else {
                        $file_path = $directory .DIRECTORY_SEPARATOR. $file;
                        // 解析文件内容及配置
                        $content = $this->getContent($file_path);
                        $config = $content['setting']; // 文章配置
                        $tmp[] = $content['setting'];
                    }
                }
            }
            closedir($dir);
            return $tmp;
        }
    }
}