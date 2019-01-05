---
title: PHP使用gearman进行任务分发
type: PHP
date: 2018-10-09
tags: 任务分发,分布式
---

Gearman是一个分发任务的程序框架，可以用在各种场合，与Hadoop相比，Gearman更偏向于任务分发功能。
它的任务分布非常简单，简单得可以只需要用脚本即可完成。
Gearman最初用于LiveJournal的图片resize功能，由于图片resize需要消耗大量计算资源，因此需要调度到后端多台服务器执行，完成任务之后返回前端再呈现到界面。

### Gearman可以做什么
异步处理:图片处理,订单处理,批量邮件/通知之类的
要求高CPU或内存的处理:大容量的数据处理,MapReduce运算,日志聚集,视频编码
分布式和并行的处理
定时处理:增量更新,数据复制
限制速率的FIFO处理
分布式的系统监控任务

gearman中请求的处理过程一般涉及三种角色：client->job->worker
> client：是请求的发起者
> job：是请求的调度者，用于把客户的请求分发到不同的worker上进行工作
> worker：是请求的处理者

[Gearman官方手册](http://gearman.org/getting-started/)

### 安装
**首先安装gearman**
```language-shell
[Du]# brew install gearman
==> gearman
To have launchd start gearman now and restart at login:
  brew services start gearman
Or, if you don't want/need a background service you can just run:
  gearmand -d
```

**安装php扩展**
前置需求
This extension requires the **libgearman, libevent and uuid** libraries, and a running Gearman server.

```language-shell
[Du]# pecl install gearman
```

### Demo
**Client**
```php
<?php
//创建一个客户端
$client = new GearmanClient();
//添加一个job服务
$client->addServer('127.0.0.1', 4730);
//doBackground异步，返回提交任务的句柄
$ret = $client->doBackground('sendEmail', json_encode(array(
    'email' => 'test@qq.com',
    'title' => '测试异步',
    'body' => '异步执行好牛B的样子',
)));
 
//继续执行下面的代码
echo "我的内心毫无波动，甚至还想笑\n";
 
do {
    sleep(1);
 
    //获取任务句柄的状态
    //jobStatus返回的是一个数组
    //第一个，表示工作是否已经知道
    //第二个，工作是否在运行
    //第三和第四，分别对应完成百分比的分子与分母
    $status = $client->jobStatus($ret);
     
    echo "完成情况：{$status[2]}/{$status[3]}\n";
 
    if(!$status[1]) {
        break;
    }
} while(true);
```
**Worker**
```language-shell
<?php
//创建一个worker
$worker = new GearmanWorker();
//添加一个job服务
$worker->addServer('127.0.0.1', 4730);
//注册一个回调函数，用于业务处理
$worker->addFunction('sendEmail', function($job) {
    //workload()获取客户端发送来的序列化数据
    $data = json_decode($job->workload(), true);
    //模拟发送邮件所用时间
    sleep(6);
    echo "发送{$data['email']}邮件成功\n";
});
 
//死循环
//等待job提交的任务
while($worker->work());
```

### 执行
我们先启动gearmand服务
```language-shell
[Du]# gearmand
```
在运行 worker
```language-shell
[Du]# php worker.php
```
最终运行client
```language-shell
[Du]# php client.php
```

