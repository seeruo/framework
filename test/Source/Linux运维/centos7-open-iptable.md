---
title: CentOS7下开启iptable
type: Linux,centos
date: 2016-08-29 14:55:20
tags: iptable
---

### 关闭firewall：
```language-bash
[root@localhost ~]# systemctl stop firewalld.service            #停止firewall
[root@localhost ~]# systemctl disable firewalld.service        #禁止firewall开机启动
```
### 安装安装iptables防火墙
```language-bash
[root@localhost ~]# yum install iptables-services            #安装
```
### 修改防火墙规则
```language-bash
[root@localhost ~]# vim /etc/sysconfig/iptables
```

### 重启防火墙
```language-bash
[root@localhost ~]# systemctl restart iptables
```
### 设置开机自启动

```language-bash
[root@localhost ~]# systemctl enable iptables
```

### 关闭SELINUX

```language-bash
[root@base ~]# setenforce 0
[root@base ~]# vim /etclinux/config 
```
将SELINUX=enforcing改为SELINUX=disabled，保存后退出

### 检查当前配置
```language-bash
[root@base ~]# getenforce
```