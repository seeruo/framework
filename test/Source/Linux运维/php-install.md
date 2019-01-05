---
title: Linux下安装PHP
type: Linux,php
date: 2016-08-29 14:55:20
tags: PHP安装
---

#### 安装epel-release的rpm源
```language-bash
[root@Mei ~]# rpm -ivh http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-5.noarch.rpm
[root@Mei ~]# yum -y install epel-release 
```
#### 安装PHP7的rpm源
```language-bash
[root@Mei ~]# rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
[root@Mei ~]# yum install php70w
[root@Mei ~]# php -v
PHP 7.0.29 (cli) (built: Mar 30 2018 08:06:59) ( NTS )
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2017 Zend Technologies
```
#### 安装PHP组件

```language-bash
[root@Mei ~]# yum install php70w.x86_64 php70w-cli.x86_64 php70w-common.x86_64 php70w-gd.x86_64 php70w-ldap.x86_64 php70w-mbstring.x86_64 php70w-mcrypt.x86_64 php70w-mysql.x86_64 php70w-pdo.x86_64
[root@Mei ~]# yum install php70w-fpm
```
其他需要的组件在自行安装。

```language-bash
[root@Mei ~]# yum install php70w-devel
```