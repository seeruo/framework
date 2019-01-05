---
title: Shell学习-Mac上PHP版本管理
type: shell
date: 2018-12-27
tags: shell,php版本管理
---

一直使用手工操作的模式去切换我的Mac电脑上的php版本，有时候遇到一天来回切换几次的情况，就超级烦

还好有shell可以简化流程，我就把php的版本切换用shell来处理

直接上代码

```language-bash
#!/bin/bash
if [ ! -n "$1" ] ;then
    echo "Command List"
    echo "========================="
    echo "56     --php5.6版本"
    echo "72     --php7.2版本"
    echo ""
else
    if [ $1 == '56' ]
    then
        brew unlink php@7.2
        brew link --force php@5.6
    elif [ $1 == '72' ]
    then
        brew unlink php@5.6
        brew link --force php@7.2
    else
        echo "Command Error, Example:"
        echo "./xxx.sh [arg] "
        echo ""
        echo "Command List"
        echo "========================="
        echo "56     --php5.6版本"
        echo "72     --php7.2版本"
        echo ""
    fi
fi
```

以后妈妈再也不用担心我的php版本切换了😊
