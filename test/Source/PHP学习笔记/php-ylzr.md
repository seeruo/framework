---
title: PHP依赖注入及使用反射类创建容器
type: PHP
date: 2018-10-25
tags: 依赖注入,PHP
---

### 依赖注入
直接看代码，理解依赖注入的特性
```language-php
<?php
class C
{
    public function doSomething()
    {
        echo __METHOD__, '我是C类|';
    }
}

/**
 * B依赖C
 */
class B
{
    private $c;

    // 这里声明需要依赖C对象的实例
    public function __construct(C $c)
    {
        $this->c = $c;
    }

    public function doSomething()
    {
        $this->c->doSomething();
        echo __METHOD__, '我是B类|';
    }
}

/**
 * A依赖B
 */
class A
{
    private $b;

    // 这里声明需要依赖B对象的实例
    public function __construct(B $b)
    {
        $this->b = $b;
    }

    public function doSomething()
    {
        $this->b->doSomething();
        echo __METHOD__, '我是A类|';;
    }
}

// A对象依赖B对象，那么在实例化A对象的时候，传入一个B对象就对了，B类的依赖也同样操作即可
// 这样操作的好处，就是如果B类由修改，那么A类就不需要去做任何修改，从而达到了解藕的目的
$obj = new A(new B( new C()));
$obj->doSomething();
```

### 依赖注入容器
依赖注入容器的目的是自动绑定（Autowiring）或 自动解析（Automatic Resolution）
当然还有其他很多特性，这里只是实现基础原理
利用__set()和__get()魔术方法来进行
```language-php
class Container
{
    public $s = array();

    public function __set($k, $c)
    {
        $this->s[$k] = $c;
    }

    public function __get($k)
    {
        return $this->build($this->s[$k]);
    }

    /**
     * 自动绑定（Autowiring）
     * 自动解析（Automatic Resolution）
     *
     * @param string $className
     * @return object
     * @throws Exception
     */
    public function build($className)
    {
        // 如果是匿名函数（Anonymous functions），也叫闭包函数（closures）
        if ($className instanceof Closure) {
            // 执行闭包函数，并将结果
            return $className($this);
        }

        // 新建一个反射对象，参数书命名空间地址
        $reflector = new ReflectionClass($className);

        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            throw new Exception("Can't instantiate this.");
        }

        /** @var ReflectionMethod $constructor 获取类的构造函数 */
        $constructor = $reflector->getConstructor();

        // 若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            return new $className;
        }

        // 取构造函数参数,通过 ReflectionParameter 数组返回参数列表
        $parameters = $constructor->getParameters();

        // 递归解析构造函数的参数
        $dependencies = $this->getDependencies($parameters);

        // 创建一个类的新实例，给出的参数将传递到类的构造函数。
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function getDependencies($parameters)
    {
        $dependencies = [];

        /** @var ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            /** @var ReflectionClass $dependency */
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                // 是变量,有默认值则设置默认值
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                // 是一个类，递归解析
                $dependencies[] = $this->build($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws Exception
     */
    public function resolveNonClass($parameter)
    {
        // 有默认值则返回默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception('error');
    }
}
```

写一个简单的App来做测试
```language-php
class App
{
    public $class;
    // 需要放入容器的字典
    public $map = [
        'ac'=>'A',
        'bc'=>'B',
        'cc'=>'C'
    ];
    public function __construct()
    {
        $this->class = new Container();
        foreach($this->map as $k=>$v){
            // 注册
            $this->class->$k = $v;
        }
    }
    public function do($k, $method){
        // 获取
        $obj = $this->class->$k;
        // 执行
        return $obj->$method();
    }
}

//模拟访问'A'类的'doSomething'方法
$router = new App();
$data = $router->do('ac', 'doSomething');
```







