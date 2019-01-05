---
title: Cordova学习(基础使用流程)
type: Cordova
date: 2018-10-03 19:20:20
tags: Cordova笔记,使用js开发原生app
---
Apache Cordova是一个开源移动开发框架。它允许您使用标准Web技术 - HTML5，CSS3和JavaScript进行跨平台开发。
应用程序在针对每个平台的包装器中执行，并依赖符合标准的API来访问每个设备的功能，如传感器，数据，网络状态等。

### 安装
```language-bash
[~]# sudo npm install -g cordova
```
创建一个项目
```language-bash
[~]# cordova create hello com.example.hello HelloWorld
[hello]# cd hello
```

### 添加平台
```language-bash
[hello]# cordova platform add ios
[hello]# cordova platform add android
// 检查当前的平台集：
[hello]# cordova platform ls
```

### 构建应用

> 安装构建的先决条件
```language-bash
[hello]# cordova requirements
Requirements check results for ios:
Apple macOS: installed darwin
Xcode: installed 10.0
ios-deploy: installed 1.9.4
CocoaPods: not installed 
The CocoaPods repo has not been synced yet, this will take a long time (approximately 500MB as of Sept 2016). Please run `pod setup` first to sync the repo.
Some of requirements check failed
```
这里会检查构建应用需要的依赖
如果缺少，就根据需要的依赖进行安装即可；
这里有一个CocoaPods缺少仓库，但是直接走ruby的原始源安装太慢
修改ruby源,并安装
```language-bash
[hello]# gem sources --remove https://rubygems.org/
[hello]# gem sources -a http://rubygems-china.oss.aliyuncs.com
[hello]# pod setup
```

开始构建应用
```language-bash
[hello]# cordova build
[hello]# cordova build ios
```







