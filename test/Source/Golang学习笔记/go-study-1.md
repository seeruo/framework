---
title: Golang学习1（开发环境配置）
type: Golang
date: 2018-10-28 14:55:20
tags: go开发环境搭建
---
Mac下安装golang

```language-bash
[~] brew install go@1.9
==> Downloading https://mirrors.tuna.tsinghua.edu.cn/homebrew-bottles/bottles/go@1.9-1.9.7
######################################################################## 100.0%
==> Pouring go@1.9-1.9.7.mojave.bottle.tar.gz
==> Caveats
A valid GOPATH is required to use the `go get` command.
If $GOPATH is not specified, $HOME/go will be used by default:
  https://golang.org/doc/code.html#GOPATH

You may wish to add the GOROOT-based install location to your PATH:
  export PATH=$PATH:/usr/local/opt/go@1.9/libexec/bin

go@1.9 is keg-only, which means it was not symlinked into /usr/local,
because this is an alternate version of another formula.

If you need to have go@1.9 first in your PATH run:
  echo 'export PATH="/usr/local/opt/go@1.9/bin:$PATH"' >> ~/.bash_profile

==> Summary
🍺  /usr/local/Cellar/go@1.9/1.9.7: 7,668 files, 294.2MB
```
配置环境变量,查看版本
```language-bash
[~] export PATH=$PATH:/usr/local/opt/go@1.9/libexec/bin
[~] go version
go version go1.9.7 darwin/amd64
```
