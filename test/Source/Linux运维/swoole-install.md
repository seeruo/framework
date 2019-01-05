---
title: Linux下安装Swoole
type: Linux,php
date: 2018-06-29 14:55:20
tags: Swoole
---

#### 1.PHP安装请见PHP安装
#### 2.C语言环境(4.4以上)
```language-bash
[root@Mei ~]# yum install gcc
```
#### 3.安装make

```language-bash
[root@Mei ~]# yum install make
```
#### 4.安装autoconf

```language-bash
[root@Mei ~]# yum install autoconf
```
#### 5.安装pcre

```language-bash
[root@Mei ~]# yum install pcre-devel
```

下载Swoole:
https://github.com/swoole/swoole-src/releases
http://pecl.php.net/package/swoole
http://git.oschina.net/swoole/swoole
这三个地址都有swoole的版本
#### 1.准备下载工具
```language-bash
[root@Mei ~]# yum install wget
[root@Mei ~]# wget -c http://pecl.php.net/get/swoole-2.1.3.tgz
[root@Mei ~]# ls
swoole-2.1.3.tgz
```
#### 2.安装解压工具并解压包文件

```language-bash
[root@Mei ~]# yum install tar -y
[root@Mei ~]# tar -zxvf swoole-2.1.3.tgz
[root@Mei ~]# ls
package.xml  swoole-2.1.3  swoole-2.1.3.tgz
```

#### 1.开始编译安装
```language-bash
[root@Mei ~]# cd swoole-2.1.3
[root@Mei swoole-2.1.3]# phpize
Can't find PHP headers in /usr/include/php
The php-devel package is required for use of this command.
```
这里如果提示需要php-devel组件，请先先安装PHP的组件(参考PHP安装),再执行

```language-bash
[root@Mei swoole-2.1.3]# ./configure
[root@Mei swoole-2.1.3]# make
[root@Mei swoole-2.1.3]# make install
```

#### 2.配置php.ini

```language-bash
[root@Mei swoole-2.1.3]# vim /etc/php.ini
```
在php.ini文件里面添加

```language-bash
extension=swoole.so
; If you wish to have an extension loaded automatically, use the following
```

#### 3.检查swoole安装是否成功

```language-bash
[root@Mei swoole-2.1.3]# php -m
-------------------------
standard
swoole
tokenizer
[Zend Modules]
-------------------------
```
如果存在 swoole, 则表示安装成功

THE END!!!



