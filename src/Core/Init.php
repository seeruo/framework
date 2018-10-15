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
use Seeruo\Core\Git;

/**
 * Git操作类
 */
class Init 
{
	private $config;

    public function __construct($config){
        $this->config = $config;
    }
    /**
     * 先初始文章
     * 1.清空仓库-初始仓库
     * @return [type] [description]
     */
    public function run()
    {
        $Git = new Git(RESULT, $this->config['logs']);
        $Git->init();
    }
}