---
title: PHP基础知识梳理
type: PHP
date: 2018-10-24 14:55:20
tags: PHP基础,知识梳理
---

```language-php
namespace App;

use App\Base;
use Closure;

class People extends Base {
    public $args;
    public function __construct(Int $args){
        echo $args . "\n";
        $this->args = $args;
        $this->init();
    }
    public function init(){
        echo 'init' . "\n";
    }
    public function say(String $arg){
        echo $arg . "\n";
    }
    public function speak(Closure $dd)
    {
        $dd($this);
        echo "this is callback";
    }
}
$obj = new People(1024);
$obj->say('hello world');
$ss = 'test';
$obj->speak(function($obj) use ($ss){
    echo $obj->say('2');
    echo "speak". "\n";
});
```

### PHP声明函数类型的三种方式：
- public 表示全局，类内部外部子类都可以访问；
- private表示私有的，只有本类内部可以使用；
- protected表示受保护的，只有本类或子类或父类中可以访问；

### 构造函数__constoruct
每个类中可以定义一个构造函数，具有构造函数的类会在每次创建新对象时先调用此方法，所以非常适合在使用对象之前做一些初始化工作。

构造函数可以接受参数，参数在每次实例化对象的时候传入

### PHP方法参数类型声明
PHP的每个方式的参数可以事先声明参数类型，这样在接受到参数的时候，程序会自动检测参数的类型，如果不匹配会抛出错误
参数的类型声明可以是Int,String等数据类型，也可以是对象类型或接口类型
参数类型的声明方式和golang等其他强类型语言类似

### Closure 类 (用于代表 匿名函数 的类.)
匿名函数没有
匿名函数（Anonymous functions），也叫闭包函数（closures），允许临时创建一个没有指定名称的函数。最经常用作回调函数（callback）参数的值。

函数里面传入的参数如果是一个没有命名的闭包函数，PHP 会自动把此种表达式转换成内置类 Closure 的对象实例

### ReflectionClass 反射
PHP具有完整的反射 API，添加了对类、接口、函数、方法和扩展进行反向工程的能力
此外，反射 API 提供了方法来取出函数、类和方法中的文档注释。
这些功能api在我们使用中，会帮我们减少很多工作量
使用参考：[PHP依赖注入及使用反射类创建容器](/articles/2018/10/25/php-ylzr)


### 依赖注入
依赖注入的介绍比较复杂，详情看具体的依赖注入博文
[PHP依赖注入及使用反射类创建容器](/articles/2018/10/25/php-ylzr)








