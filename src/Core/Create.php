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
use \Seeruo\Core\File;

/**
 * 创建模板文件
 */
class Create
{
    private $config;
    private $source_dir;

    public function __construct($config){
        $this->config = $config;
        $this->source_dir = $this->config['source_dir'] . DIRECTORY_SEPARATOR;
    }

    /**
     * [run description]
     * @param  [type] $fileName [文件名]
     * @return [type] [none]
     */
    public function run($fileName)
    {
        try {
            $fileName = getPy($fileName);
            $file = $this->source_dir . $fileName . ".md";
            if (file_exists($file)) {
                throw new Exception("This file exists");
            }
            $html = '';
            $html .= "---" . PHP_EOL;
            $html .= "type: " . 'type class' . PHP_EOL;
            $html .= "title: " . $fileName . PHP_EOL;
            $html .= "date: " . date('Y-m-d H:i:s') . PHP_EOL;
            $html .= "tags: " . $fileName . PHP_EOL;
            $html .= "---" . PHP_EOL . PHP_EOL;
            $html .= "Create at ".date('Y-m-d H:i:s').PHP_EOL;
            $html .= 'You should edit this page as markdown'.PHP_EOL;

            Log::info( 'Creating ' . $file );
            File::createFile($file, $html);
        } catch (Exception $e) {
            Log::info( 'Created ' . $file . ' failed!' );
            Log::info( $e->getMessage() );
        }
    }
}