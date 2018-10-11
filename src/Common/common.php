<?php
/**
 * [dd 调试方法]
 * @DateTime 2018-10-08
 * @param    [type]     $data [数据]
 * @return   [type]           [description]
 */
function dd($data)
{
    echo "<pre>";
    print_r($data);
    die();
}
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
    $pinyin = new \Seeruo\Lib\PinYin();
    return $pinyin->getpy(trim($char), $quanpin, $daxie);
}