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

class Store{
    // 实例对象
    private static $_instance;

    // 数据存储器
    private $stroe = [];

    // 防止构造本身
    private function __construct(){}

    public static function getInstance(){
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // 存储数据
    public function set($key, $value){
        $this->stroe[$key] = $value;
    }

    // 获取数据
    public function get($key){
        return $this->has($key) ? $this->stroe[$key]: null;
    }

    // 检查是否有该数据
    public function has($key)
    {
        return isset($this->stroe[$key]) ? true: false;
    }
}