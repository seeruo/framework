---
title: Seeruo1.0开发基本完成
type: Seeruo
date: 2018-09-20
tags: Seeruo,blog,静态博客构建工具
---

截止10.13日，Seeruo1.0版本基本功能开发完毕，可以暂时投入使用，下面是快速使用手册，如需详情，请到Seeruo文档查看：
[Seeruo文档](http://seeruo.codegrids.com)

#### 准备开始
> 开始安装，使用之前有些条件需要满足，当然phper应该都已经有这些了：
- 安装php，并设置环境变量: 下载地址
- 安装Composer工具: 下载地址

#### Seeruo介绍
Seeruo是一个用php开发的用于快速构建静态博客(文档、笔记)的极简框架工具。使用markdown语法书写文章，用命令行模式构建静态文件，并上传至GitHub Pages或云服务器。

Seeruo具有如下特性：
- 简单易用
- 快速构建
- 本地预览
- 一键发布

Seeruo的开发初衷是为了用php开发一个博客系统，接触了hexo之后，吸收了hexo的一些特性，并用php开发了这套框架。
Seeruo陪备了基础框架，可以直接使用；也可以通过直接require框架的方式使用。

#### 版本说明
截止10.13日，Seeruo1.0版本基本功能开发完毕，可以暂时投入使用。

#### 版本规划
需要开发的功能
1、排序规则，时间先后顺序的配置项
2、是否忽略解析，在每个文章里面配置 或者 在配置文件里面配置
3、上传原始文件到仓库的配置、拉取原始文件的配置

#### 快速开始
```language-shell
[~]# composer create-project seeruo/seeruo myblog
[~]# cd myblog
[App]# cd myblog/App
[App]# php seeruo create test
[App]# php seeruo build
[App]# php seeruo server
```

更多使用细则，请参考详细使用手册。

