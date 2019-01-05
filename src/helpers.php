<?php
/**
 * 仓储工具
 */
if (! function_exists('store')) {
    /**
     * 获取应用对象
     * @DateTime 2018-11-21
     * @param    [type]     $value [description]
     * @return   [type]            [description]
     */
    function store($value = null){
        $store = \App\Library\Store::getInstance();
        if (empty($value)) {
            return $store;
        }
        return $store->get($value);
    }
}

/**
 * 调试工具
 */
if (! function_exists('dd')) {
    /**
     * 调试方法
     * @DateTime 2018-11-21
     * @param    [type]     $value [description]
     * @return   [type]            [description]
     */
    function dd($value = null)
    {
        print_r($value);
        echo "\r\n";
        die();
    }
}
/**
 * 获取拼音简码
 */
if (! function_exists('getPy')) {
    /**
     * [getPy 汉字转拼音]
     * @DateTime 2018-10-08
     * @param    [type]     $char    [汉字字符串]
     * @param    boolean    $quanpin [是否全拼]
     * @param    boolean    $daxie   [是否首字母大写]
     * @return   [type]              [description]
     */
    function getPy($char, $quanpin=true, $daxie=false)
    {
        $pinyin = new \App\Library\PinYin();
        return $pinyin->getpy(trim($char), $quanpin, $daxie);
    }
}




