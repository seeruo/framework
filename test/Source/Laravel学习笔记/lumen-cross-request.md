---
title: Lumen跨域问题解决方法
type: PHP,Laravel
date: 2018-09-27 14:55:20
tags: 跨域,Lumen跨域
---

一般我们在设置跨域的时候，只需要在入口文件头部加上允许跨域的header头就可以了
客户端在发送复杂请求的时候会发送一个options请求；
> 注意：该请求方法的响应不能缓存。
> OPTIONS请求方法的主要用途有两个：
> 1、获取服务器支持的HTTP请求方法；也是黑客经常使用的方法。
> 2、用来检查服务器的性能。例如：AJAX进行跨域请求时的预检，需要向另外一个域名的资源发送一个HTTP OPTIONS请求头，用以判断实际发送的请求是否安全。

这就需要我们在每一个同样的请求路径的情况下，多设置一条options路由，
```language-php
// 这是实际的业务资源请求
$router->get('/', 'uses'=>'IndexController@index']);
// 这是应付复杂请求的情况下，设置的options请求
$router->options('/', 'uses'=>'IndexController@index']);
...
// 有多少个业务资源请求，我们就需要设置多少个options请求
```
这种设计明显不现实，而且我们在实际使用中很少使用option来做资源路由；
这种问题是lumen本身没有处理关于options请求的关系造成，
Slim框架处理办法：
```language-php
public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods)
{
    if ($request->getMethod() === 'OPTIONS') {
        $status = 200;
        $contentType = 'text/plain';
        $output = $this->renderPlainOptionsMessage($methods);
    } else {
        //...
    }
    //...
}

```
所以基于上面的需求，在lumen中，我们可以添加一个全局中间件去处理options的请求；

#### 第一步：创建一个中间件('CrossRequestMiddleware.php')
```language-php
<?php
namespace App\Http\Middleware;

use Closure;

/**
 * 跨域中间件，主要处理 options 请求问题
 */
class CrossRequestMiddleware
{
    public function handle($request, Closure $next)
    {
        // 获取跨域配置
        $config = config('crossRequest');
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
        // 检查是否允许跨域
        if (in_array($origin, $config['Origin'])) {
            header('Content-Type: text/html; charset= UTF-8');
            // 只允许来自http://localhost:9001的跨域请求
            header("Access-Control-Allow-Origin: {$origin}"); // 允许跨域
            // 允许的请求方法
            header("Access-Control-Allow-Methods: {$config['Methods']}");
            // 允许的请求头(包括自定义Authorization)，
            header("Access-Control-Allow-Headers: {$config['Headers']}");
        }
    	// 检查请求的方法是否是options，如果是直接返回200
        if ($request->isMethod('options')) {
            return response('This Is Options Request!', 200);
        }
        return $next($request);
    }
}
```
#### 第二步：然后在内核文件注册该全局中间件('bootstrap/app.php')
```language-php
// 注册全局中间件
$app->middleware([
   App\Http\Middleware\CrossRequestMiddleware::class
]);
```
