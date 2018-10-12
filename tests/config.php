<?php
return [
    'title'             => "SeeRuo",             // 网站名称
    'root'              => __DIR__,             // 网站根目录
    'url'               => '',                  // 网站URL
    'push_type'         => 'ssh',               // 暂时只支持ssh方式，需要服务器开启的SSH支持
    'push_user'         => 'root',              // SSH账号
    'push_address'      => '127.0.0.1',         // SSH推送地址
    'push_path'         => '/var/www/html',     // SSH服务器网站根路径,该路径需开启写权限
    'themes'            => 'document',              // 网站主题
    'auto_open'         => true,                // 自动打开浏览器
    // 扩展页码
    'home_page'         => '单页/home.md',       // 主页
    'single_pages'      => [
        'about'             => '单页/about.md',
        'linker'            => '单页/linker.md'
    ]
];