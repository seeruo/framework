---
title: Golang学习2（hello world）
type: Golang
date: 2018-10-28 15:55:20
tags: 入门示例
---
继上一篇  Golang学习1（开发环境配置） 之后，
本章是just do it(从入门到放弃)

### hello wold

创建一个项目目录，并新建一个go文件
```language-bash
[~]# mkdir lesson2
[~]# chmod -R 0777 lesson2
[~]# cd lesson2
[lesson2]# vim hello.go
```

代码内容：
```language-go
package main
  
import "fmt"

func main (){
    fmt.Println("hello world!")
}
```

保存并执行
```language-bash
[lesson2]# go run hello.go
hello world!
```
能够打印出"hello world"，说明你已经入门，下一步就是“放弃”；


### PHP与Golang
PHP（外文名:PHP: Hypertext Preprocessor，中文名：“超文本预处理器”）是一种通用开源脚本语言。脚本语言不需要编译，可以直接用，由解释器来负责解释；一般都是以文本形式存在,类似于一种命令。由于代码是一句一句解释执行的，这中间有很多等待的过程，而且还取决于解释器的执行速度，所以解释型编程语言的特点就是运行速度慢。


Golang是一门编译型语言，它还需要经过一个编译的步骤，把写好的代码通过语言提供的编译器，编译成操作系统方便阅读而人类几乎无法阅读的代码。这种语言的好处就是，运行速度快。如：Go, Java, C/C++，都是编译型编程语言。

了解两者之间的区别之后，现在我们对go文件进行编译，然后再执行编译之后的文件试试；

```language-bash
[lesson2]# go build hello.go
[lesson2]# ls
hello       hello.go
[lesson2]# ./hello
hello world!
```
可以看见，我们得到了与run相同的结果

windows上编译之后的结果应该是hello.exe, 双击执行应该获取的效果也是一样的

### 关于main

> package main

上面的demo中，我们使用了一行代码
```language-go
package main
```
如果我们把main改为hello，会造成什么后果呢?

```language-go
package hello
  
import "fmt"

func main (){
    fmt.Println("hello world!")
}
```
执行go文件，看看有什么结果
```language-bash
[lesson2]# go run hello.go
go run: cannot run non-main package
```
这里就会提示没有找到main包, Why？
这是Go语言规定，程序的入口文件，必须输入main包，

> func main

再有我们在go文件中定义了一个main方法，那可以改为其他的方法吗？
```language-go
package main
  
import "fmt"

func hello (){
    fmt.Println("hello world!")
}
```
执行go文件，看看有什么结果
```language-bash
[lesson2]# go run hello.go
# command-line-arguments
runtime.main_main·f: relocation target main.main not defined
runtime.main_main·f: undefined: "main.main"
```
这里就会提示main没有定义, Why？
这是Go语言另一个语法规定，程序的入口文件，必须输入main包外，而且必须包含一个main方法
这些约定跟C语言的类似

> func 关键字 相当于php里面的function , 语法块与使用方法基本一致
例如我们在demo中再加入一个方法 speak

```language-go
package main
  
import "fmt"

func hello (){
    fmt.Println("hello world!")
    speak()
}
func speak(){
    fmt.Println("你好，我是Seeruo")
}
```
执行一下我们会得到如下返回

```language-bash
[lesson2]# go run hello.go
hello world!
你好，我是Seeruo
```

### { 是不能单独占一行的
如果有这种情况发生，就会报如下错误,这里是与php有区别的
```language-bash
[lesson2]# go run hello.go
# command-line-arguments
./hello.go:5:6: missing function body
./hello.go:6:1: syntax error: unexpected semicolon or newline before {
```

到这里，我还没有放弃，继续下一章节








