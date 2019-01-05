---
title: SrWords社会化评论插件开发笔记
type: SrWords
date: 2018-09-23 14:55:20
tags: SrWords,社会化评论插件
---

之前有几个社会化评论插件，都已经关闭了，剩下一个‘畅言’（也不能畅言），但是收费吓死人，普通的博客主我想应该不会去花999/年去升级一个免广告畅言。阿里云一个云主机一年的费用也就300左右，遇上活动，298元客户买3年；想想这之前的成本，估计多半都会选择忍受广告，选择免费使用。

18年7月份，我把之前的博客改版了，参考了hexo用php重新写了一个静态博客构建工具(Seeruo)，用来构建我的Blog；之间断断续续开发，实现了大部分的功能，后续还需要继续开发；

9月份的时候，觉得我的Blog应该需要一个wordpress类似的一个评论插件，所以想到了之前用过的‘多说’，于是就去找官网，发现已经停运了；畅言这种恶心的操作，让我决定自己开发一个评论插件；


#### 1.0版本规划：
1. 先实现不盖楼的客户端业务代码逻辑
2. 实现用户注册登陆
3. 实现用户jwt权限认证
4. 国庆完成1.0版本的开发

#### 主要的开发逻辑
```
1.客户端渲染评论页面，生成评论框，和评论历史；
2.获取评论历史：从服务端拉取数据列表，在客户端异步渲染；
3.发送评论：根据用户存储的token，发送数据至服务端，验证token有效性，保存数据
4.登陆：输入用户名/密码，请求token,保存至客户端
5.服务端处理跨域问题；
```

#### 前端核心代码，后端代码该怎样就怎样
```language-javascript
;(function (window, document) {
    var SrWords = function (target, options) {
        // 检查是否是本对象
        if(!(this instanceof SrWords)){
            return new SrWords(target, options);
        }

        // 参数合并
        this.options = this.extend({
            // 这个参数以后可能会更改所以暴露出去
            tokenKey: 'SrWords_cookie_key_20180926',
            imgSrc:"../static/img/coupon-mask_1.png"
        },options);

        // 判断传进来的是DOM还是字符串
        if((typeof target)==="string"){
            this.target = document.querySelector(target);
        }else{
            this.target = target;
        }

        // 初始化插件
        this.init();
    }
    // 构建原型链方法
    SrWords.prototype = {
        /**
         * [init 初始化插件]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        init: function () {
            this.renderSpeakArea();
            this.renderHistoryMsg();
            this.renderDialog();
        },
        /**
         * [renderSpeakArea 渲染发送内容的输入框]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        renderSpeakArea: function () {
            var ss = this.target;
            var html = '<div class="srwords-speak">';
            html += '<textarea class="srwords-speak-textarea"></textarea>';
            html += '<div class="srwords-speak-options">';
            html += '<span class="">欢迎使用SeeruoWords</span>';
            html += '<span class="submit-button srwords-speak-speaker-btn" class="submit-text">发布</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="srwords-msgs-list-area"></div>';
            ss.innerHTML = html;
            this.bindEvent();
        },
        /**
         * [renderOneMessage 渲染页面内容]
         * @DateTime 2018-09-26
         * @param    {[type]}   obj [一条消息对象]
         * @return   {[type]}       [description]
         */
        renderOneMessage: function (obj) {
            var list_dom = this.target.querySelector('.srwords-msgs-list-area');
            var old_html = list_dom.innerHTML;
            var html = '<div class="srwords-msg">';
            html += '<div class="srwords-msg-options">';
            html += '<span class="srwords-msg-author">'+ obj.author +'</span>';
            html += '<span class="srwords-msg-time">'+ obj.time +'</span>';
            html += '</div>';
            html += '<span class="srwords-msg-text">'+ obj.value +'</span>';
            html += '</div>';
            html = html + old_html;
            list_dom.innerHTML = html;
        },
        /**
         * [renderHistoryMsg 获取历史消息，并渲染]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        renderHistoryMsg: function(){
            var _this = this;
            var param = encodeURI( '?key=' + window.location.href );
            _this.ajax('get', 'http://localhost:8000/api' + param, {}, function (res) {
                // 如果后端保存数据成功，开始渲染页面留言内容，在后面加一个留言dom
                if (res.status == 200) {
                    res.list.forEach(function(item, index) {
                        _this.renderOneMessage(item);
                    })
                }else{
                    alert(res.msg);
                }
            })
        },
        /**
         * [renderDialog 渲染对话框]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        renderDialog() {
            var ss = this.target;
            var dialog = document.createElement("div");
            // dialog.class = 'srwords-dialog';
            dialog.classList.add("srwords-dialog");

            var dlg_html = '<div class="srwords-dialog-mask"></div>';
            dlg_html += '<div class="srwords-dialog-body">';
            dlg_html += '<div class="srwords-dialog-close">X</div>';
            dlg_html += '<div class="srwords-dialog-input"><span>用户登陆</span></div>';
            dlg_html += '<div class="srwords-dialog-input"><label>用户</label><input type="text" class="srwords-dialog-input-user"></div>';
            dlg_html += '<div class="srwords-dialog-input"><label>密码</label><input type="password" class="srwords-dialog-input-pass"></div>';
            dlg_html += '<div class="srwords-dialog-input"><button class="srwords-dialog-login-btn">登陆</button></div>';
            dlg_html += '<div class="srwords-dialog-linker"><a class="srwords-dialog-regist">账号注册</a><a class="srwords-dialog-forget">忘记密码?</a></div>';
            dlg_html += '</div>';
            dialog.innerHTML = dlg_html;
            ss.appendChild(dialog);
            this.bindDialogEvent();
        },
        /**
         * [bindEvent 绑定发送留言事件]
         * @DateTime 2018-09-26
         * @param    {[type]}   e [给元素绑定属性]
         * @return   {[type]}     [description]
         */
        bindEvent: function () {
            var _this = this;
            // 提交按钮点击事件
            var submitBtnDom = this.target.querySelector('.srwords-speak-speaker-btn');
            submitBtnDom.addEventListener("click",function(){
                _this.handleSubmit();
            },false);
            // 内容输入事件
            var text_area = this.target.querySelector('.srwords-speak-textarea');
            text_area.addEventListener("focus",function(){
                _this.handleFocus();
            },false);
        },
        /**
         * [bindDialogEvent 绑定对话框事件]
         * @DateTime 2018-09-26
         * @param    {[type]}   e [给元素绑定属性]
         * @return   {[type]}     [description]
         */
        bindDialogEvent: function () {
            var _this = this;
            // 关闭对话框
            var closeBtnDom = this.target.querySelector('.srwords-dialog-close');
            closeBtnDom.addEventListener("click",function(){
                _this.closeDialog()
            },false);
            // 关闭对话框
            var loginBtnDom = this.target.querySelector('.srwords-dialog-login-btn');
            loginBtnDom.addEventListener("click",function(){
                _this.handleSubLogin();
            },false);
        },
        /**
         * [handleSubmit 提交事件]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        handleSubmit: function() {
            var _this = this;
            var sayWsDom = document.querySelector('.srwords-speak .srwords-speak-textarea'),
                api_key = window.location.href,
                api_value = sayWsDom.value,
                api_token = this.getSrCookie(this.options.tokenKey);
            var obj = {
                key: api_key,
                value: api_value,
                token: api_token
            }
            this.ajax('post', 'http://localhost:8000/api', obj, function (res) {
                // 如果后端保存数据成功，开始渲染页面留言内容，在后面加一个留言dom
                if (res.status == 200) {
                    _this.renderOneMessage(res.data);
                }else{
                    alert(res.msg);
                }
            })
        },
        /**
         * [handleFocus 输入框选中事件]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        handleFocus: function () {
            var token = this.getSrCookie(this.options.tokenKey);
            // 如果不存在key
            if ( typeof token !== 'string' || token === null) {
                this.showDialog();
                return false;
            }
        },
        /**
         * [handleSubLogin 登陆提交]
         * @DateTime 2018-09-26
         * @return   {[type]}   [description]
         */
        handleSubLogin(){
            var _this = this;
            var user_dom = this.target.querySelector('.srwords-dialog-input-user');
            var pass_dom = this.target.querySelector('.srwords-dialog-input-pass');
            var user_value = user_dom.value;
            var pass_value = pass_dom.value;

            var param = {user: user_value, pass: pass_value};
            this.ajax('post', 'http://localhost:8000/api/login', param, function (res) {
                // 如果后端保存数据成功，开始渲染页面留言内容，在后面加一个留言dom
                if (res.status == 200) {
                    _this.setSrCookie(_this.options.tokenKey, res.token, 'd10');
                    _this.closeDialog()
                }else{
                    alert(res.msg);
                }
            })
        },
        closeDialog(){
            var dialog = this.target.querySelector('.srwords-dialog');
            dialog.classList.remove("show-dialog");
        },
        showDialog(){
            var dialog = this.target.querySelector('.srwords-dialog');
            dialog.classList.add("show-dialog");
        },
        /**
         * [ajax js异步发送请求的方法]
         * @DateTime 2018-09-26
         * @param    {str}      method   [请求方式]
         * @param    {str}      url      [请求地址]
         * @param    {object}   data     [参数对象]
         * @param    {Function} callback [回调函数]
         * @return   {[type]}            [description]
         */
        ajax: function (method, url, data, callback) {
            var data = data ? data : {};
            var data_str = this.makeObj2Str(data);
            var token = this.getSrCookie(this.options.tokenKey);

            var xhr = new XMLHttpRequest() // 创建异步请求
            xhr.open(method, url, true)    // 获取方式，异步
            //发送合适的请求头信息
            xhr.setRequestHeader("Authorization", "Basic bGV5aWZyZWU6MTY4OTE4dHdpdHRlcg=="); 
            xhr.setRequestHeader("Accept","application/json"); 
            xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8"); 
            // 异步请求状态发生改变时会执行这个函数
            xhr.onreadystatechange = function () {
                // status == 200 用来判断当前HTTP请求完成
                if ( xhr.readyState == 4 && xhr.status == 200 ) {
                    callback(JSON.parse(xhr.responseText))  // 执行回调
                }
            }
            xhr.send( data_str )  // 发送异步请求
        },
        /**
         * [makeObj2Str 把对象转为适合xhr.send的字符串]
         * @DateTime 2018-09-26
         * @param    {[type]}   data [description]
         * @return   {[type]}        [description]
         */
        makeObj2Str: function (data) {
            var string = null;
            for (var key in data) {
                string += key + '=' + data[key] + '&';
            }
            if (string){
                string = string.substring(0, string.length-1);
            }
            return string;
        },
        /**
         * [extend 对象合并]
         * @DateTime 2018-09-26
         * @param    {[type]}   obj  [description]
         * @param    {[type]}   obj2 [description]
         * @return   {[type]}        [description]
         */
        extend: function(obj,obj2){
            for(var k in obj2){
                obj[k] = obj2[k];
            }
            return obj;
        },
        /**
         * [setSrCookie 设置cookie]
         * @DateTime 2018-09-26
         * @param    {[type]}   name  [description]
         * @param    {[type]}   value [description]
         * @param    {[type]}   time  [description]
         */
        setSrCookie: function(name, value, time) {//设置cookie方法
            var strsec = this.getsec(time); 
            var exp = new Date(); 
            exp.setTime(exp.getTime() + strsec*1); 
            document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString(); 
        },
        /**
         * [getSrCookie 获取cookie]
         * @DateTime 2018-09-26
         * @param    {[type]}   name [description]
         * @return   {[type]}        [description]
         */
        getSrCookie: function(name){//获取cookie方法
            var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
            if(arr=document.cookie.match(reg)){
                return unescape(arr[2]); 
            } else {
                return null;
            }
        },
        /**
         * [delSrCookie 删除cookie]
         * @DateTime 2018-09-26
         * @param    {[type]}   name [description]
         * @return   {[type]}       [description]
         */
        delSrCookie: function(name){
            var exp = new Date();
            exp.setTime(exp.getTime() - 1); 
            var cval=getCookie(name); 
            if(cval!=null) 
                document.cookie= name + "="+cval+";expires="+exp.toGMTString();
        },
        /**
         * [getsec 获取秒级时间]
         * @DateTime 2018-09-26
         * @param    {[type]}   str [description]
         * @return   {[type]}       [description]
         */
        getsec: function(str){ 
            var str1=str.substring(1,str.length)*1; 
            var str2=str.substring(0,1); 
            if (str2=="s"){ 
                return str1*1000;
            }else if (str2=="h"){ 
               return str1*60*60*1000;
            }else if (str2=="d"){ 
               return str1*24*60*60*1000;
            } 
        } 
    }
    window.SrWords = SrWords;
}(window, document))



// 插件的使用
/**
 * [页面加载完成之后，执行插件初始化并渲染]
 * @DateTime 2018-09-23
 * @return   {[type]}   [description]
 */
document.querySelector('body').onload= function () {
    SrWords('#SeeruoWords')
}
```