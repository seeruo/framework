<?php
namespace Seeruo;
use Exception;
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
        $Git = new \Seeruo\Git(RESULT, $this->config['logs']);
        $Git->init();
    }
}