---
title: Linux下安装Sambar
type: Linux,centos
date: 2016-08-29 14:55:20
tags: Sambar,Linux
---

注意：一定要关闭SELINUX
### 防火墙端口开放

```language-bash
[root@base ~]# vim /etc/sysconfig/iptables
-A INPUT -p udp -m state --state NEW -m udp --dport 137 -j ACCEPT
-A INPUT -p udp -m state --state NEW -m udp --dport 138 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 139 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 389 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 445 -j ACCEPT
-A INPUT -p tcp -m state --state NEW -m tcp --dport 901 -j ACCEPT

[root@base ~]# yum -y install samba samba-client
```
### 设置开机启动
```language-bash
[root@base ~]# systemctl enable smb
```
备份原始配置，并配置新配置文件

```language-bash
[root@base ~]# cd /etc/samba/ 
[root@base samba]# cp smb.conf smb.conf_bak
[root@base samba]# vim smb.conf
```
### 详细配置

```language-bash
[global]
    workgroup = WORKGROUP
    server string = Ted Samba Server %v
    netbios name = TedSamba
    passdb backend = tdbsam
    security = user
    map to guest = Bad User
[webservices]
    comment = share Directories
    path = /smb/webservices
    writable = yes
    public = yes
```
```language-bash
[root@localhost /]# groupadd samba
[root@localhost /]# useradd smbuser -g samba -s /sbin/nologin
[root@localhost /]# smbpasswd -a smbuser
[root@localhost /]# mkdir -m 0777 /devshare
[root@localhost /]# chown -R smbuser:samba /devshare
[root@localhost /]# vim /etc/samba/smb.conf
```

```language-bash
[devShare]
    comment = dev file
    path = /devshare
    valid = smbuser
    write list = smbuser
    create mask = 0777
    directory mask = 0777
```
启动smb

```language-bash
[root@base ~]# systemctl restart smb
```
本地测试
```language-bash
[root@base ~]# smbclient -L 127.0.0.1 -u smbuser
```

### 指定新建目录的属性
```language-bash
directory mask =0777
force directorymode = 0777
directorysecurity mask = 0777
force directorysecurity mode = 0777
```
### 指定新建文件的属性
```language-bash
create mask =0777　
force createmode = 0777
security mask =0777
force securitymode = 0777
```

