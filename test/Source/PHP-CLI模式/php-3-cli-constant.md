---
title: PHP的三种CLI常量
type: PHP
date: 2016-04-07 14:55:20
tags: Cli常量,PHP
---

#### PHP CLI常量 STDIN、STDOUT、STDERR
> PHP CLI(command line interface)中有三个系统常量,分别是 **STDIN、STDOUT、STDERR**，代表文件句柄.

文件句柄的操作
简单的例子：
echo "请输入一个数字：";
$num = trim(fgets(STDIN));

echo "请再输入一个数字：";
$num1 = trim(fgets(STDIN));
echo "两个数字的和为：",$num + $num1;