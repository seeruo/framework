---
title: PHP中pear和pecl的区别和联系
type: PHP
date: 2018-10-2
tags: pear,pecl,扩展安装
---
### Pear
是PHP的扩展代码包，所有的扩展均以PHP代码的形式出现，功能强大，安装简单，甚至可以改改就用。使用的时候，要在代码中进行Include才能够使用。

**它里面的扩展是完全用PHP代码写的函数和类**


### Pecl
是PHP的标准扩展，可以补充实际开发中所需的功能，所有的扩展都需要安装，在Windows下面以Dll的形式出现，在linux下面，需要单独进行编译，它的表现形式为根据PHP官方的标准用C语言写成，尽管源码开放但是一般人无法随意更改源码。

**它里面的扩展是用c或者c++编写外部模块加载至php中**

### Pear是PHP的上层扩展，Pecl是PHP的底层扩展。**
 
从使用角度来说，两者都是为特定的应用提供一套函数或类的工具，区别的只是实现的方式方法不同，但是最终目的一样。

### 举例说明
gearman扩展有两种扩展方式（见官网）

官网下载里面有这样一段描述，
[Gearman扩展下载专区](http://gearman.org/download/)
![gearman](/static/images/gearman.png)

然后我们同时使用两种方式来安装一下gearman，看看组件有什么不同

```language-bash
[Du]# pecl search gearman
Retrieving data...0%
Matched packages, channel pecl.php.net:
=======================================
Package Stable/(Latest) Local
gearman 1.1.2 (stable)        PHP wrapper to libgearman
[Du]#
[Du]# pear search gearman
Retrieving data...0%
Matched packages, channel pear.php.net:
=======================================
Package     Stable/(Latest) Local
Net_Gearman 0.2.3 (alpha)         A PHP interface to Gearman
```

我们可以发现两种方式查找的扩展，结果并不相同

好了，到这里应该弄明白两种的区别了

