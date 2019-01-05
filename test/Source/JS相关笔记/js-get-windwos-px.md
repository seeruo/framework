---
title: JS中获取窗口高度的方法
type: JavaScript
date: 2016-09-01 14:55:20
tags: JS高度获取
---

在IE中：
```language-JavaScript
document.body.clientWidth               // BODY对象宽度
document.body.clientHeight              // BODY对象高度
document.documentElement.clientWidth    // 可见区域宽度
document.documentElement.clientHeight   // 可见区域高度
document.documentElement.scrollTop      // 窗口滚动条滚动高度
```

在FireFox中：
```language-JavaScript
document.body.clientWidth               // BODY对象宽度
document.body.clientHeight              // BODY对象高度
document.documentElement.clientWidth    // 可见区域宽度
document.documentElement.clientHeight   // 可见区域高度
document.documentElement.scrollTop      // 窗口滚动条滚动高度
```

在chrome中：
```language-JavaScript
document.body.clientWidth               // BODY对象宽度
document.body.clientHeight              // BODY对象高度
document.documentElement.clientWidth    // 可见区域宽度
document.documentElement.clientHeight   // 可见区域高度
document.body.scrollTop                 // 窗口滚动条滚动高度
```

在Opera中：
```language-JavaScript
document.body.clientWidth               // 可见区域宽度
document.body.clientHeight              // 可见区域高度
document.documentElement.clientWidth    // 页面对象宽度（即BODY对象宽度加上Margin宽）
document.documentElement.clientHeight   // 页面对象高度（即BODY对象高度加上Margin高
```

滚动条：
```language-JavaScript
// 滚动到顶部 
window.scrollTo(0,0)
// 滚动到顶部 
document.getElementById('contentDiv').scrollTop = 0;
// 滚动到尾部 
window.scrollTo(0,document.body.clientHeight)
```