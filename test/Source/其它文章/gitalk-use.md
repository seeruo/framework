---
title: 博客接入Gitalk插件的过程笔记
type: Gitalk
date: 2018-12-26
tags: 未找到相关的 Issues 进行评论 请联系******* 初始化创建
---

最开始想自己写一个评论插件，后面放弃了😳，后面申请了个"畅言"，但还是觉得并不完美；
后面发现了Gitalk插件，然后把使用过程记录一下

### 引入插件

```language-html
<link rel="stylesheet" href="https://unpkg.com/gitalk/dist/gitalk.css">
<script src="https://unpkg.com/gitalk/dist/gitalk.min.js"></script>
```

### 插件配置

```language-html
    <script type="text/javascript">
    var uuid = document.querySelector('#SeeruoWords').getAttribute('data-uuid');
    var gitalk = new Gitalk({
        clientID: '234567899876545678876',
        clientSecret: '2345678998765456788762345678998765456788',
        repo: 'seeruo.github.io',   // 这里是仓库地址
        owner: 'seeruo',    // 这里是仓库用户，不是登陆账号
        admin: ['seeruo'],
        id: uuid, // 这是文章唯一标识，字符串，不能长于50字符
        distractionFreeMode: false
    })

    gitalk.render('SeeruoWords')
    </script>
```
还有其他配置，可以参考官方网站进行配置： [Gitalk官网](https://github.com/gitalk/gitalk/blob/master/readme-cn.md)

### 遇到的问题
### Q: 未找到相关的 Issues 进行评论 请联系XXXXX初始化创建
我遇到的原因是因为，owner写错了，
```language-html
{
    owner: 'seeruo' // 这里是仓库用户，不是登陆账号
},    
 ```
 网上还有一些错误原因
```language-html
{
    id: uuid  // 这是文章唯一标识，字符串，不能长于50字符
}
 ```

如果配置项都正确，你登陆的账号与amdin里面的一致，就可以在访问页面的时候，看见下图，直接点击初始化
![issues](/static/images/issues.png)

暂时就到这里，至于这里需要手工去点击初始化的问题，以后再研究吧
