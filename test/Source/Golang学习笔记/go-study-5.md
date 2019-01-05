---
title: Golang学习5（数据结构）
type: Golang
date: 2018-10-28 18:30:20
tags: 数据结构,数组
---
数据结构是程序开发中的基础，所以这点很重要
### 数组
Go 语言提供了数组类型的数据结构
数组是具有相同唯一类型的一组已编号且长度固定的数据项序列，这种类型可以是任意的原始类型例如整形、字符串或者自定义类型。

相对于去声明number0, number1, ..., and number99的变量，使用数组形式numbers[0], numbers[1] ..., numbers[99]更加方便且易于扩展。

数组元素可以通过索引（位置）来读取（或者修改），索引从0开始，第一个元素索引为 0，第二个索引为 1，以此类推。
![数组](http://www.runoob.com/wp-content/uploads/2015/06/arrays.jpg)
#### 声明数组
Go 语言数组声明需要指定元素类型及元素个数，语法格式如下：
```language-go
var variable_name [SIZE] variable_type
```
以上为一维数组的定义方式。数组长度必须是整数且大于 0。

例如以下定义了数组 balance 长度为 10 类型为 float32：
```language-go
var balance [10] float32
```

以下演示了数组初始化：
```language-go
var balance = [5]float32{1000.0, 2.0, 3.4, 7.0, 50.0}
```
初始化数组中 {} 中的元素个数不能大于 [] 中的数字。

如果忽略 [] 中的数字不设置数组大小，Go 语言会根据元素的个数来设置数组的大小：
```language-go
var balance = [...]float32{1000.0, 2.0, 3.4, 7.0, 50.0}
```
该实例与上面的实例是一样的，虽然没有设置数组的大小。

```language-go
 balance[4] = 50.0
以上实例读取了第五个元素。数组元素可以通过索引（位置）来读取（或者修改），索引从0开始，第一个元素索引为 0，第二个索引为 1，以此类推。
```
![数组2](http://www.runoob.com/wp-content/uploads/2015/06/array_presentation.jpg)


DEMO
```language-go
package main

import "fmt"

func main() {
   var n [10]int /* n 是一个长度为 10 的数组 */
   var i,j int

   /* 为数组 n 初始化元素 */         
   for i = 0; i < 10; i++ {
      n[i] = i + 100 /* 设置元素为 i + 100 */
   }

   /* 输出每个数组元素的值 */
   for j = 0; j < 10; j++ {
      fmt.Printf("Element[%d] = %d\n", j, n[j] )
   }
}
```
执行结果
```language-bash
Element[0] = 100
Element[1] = 101
Element[2] = 102
Element[3] = 103
Element[4] = 104
Element[5] = 105
Element[6] = 106
Element[7] = 107
Element[8] = 108
Element[9] = 109
```

#### 多维数组
Go 语言支持多维数组，以下为常用的多维数组声明方式：

```language-go
var variable_name [SIZE1][SIZE2]...[SIZEN] variable_type
```
以下实例声明了三维的整型数组：
```language-go
var threedim [5][10][4]int
```

**二维数组**
二维数组是最简单的多维数组，二维数组本质上是由一维数组组成的。二维数组定义方式如下：
```language-go
var arrayName [ x ][ y ] variable_type
```
variable_type 为 Go 语言的数据类型，arrayName 为数组名，二维数组可认为是一个表格，x 为行，y 为列，下图演示了一个二维数组 a 为三行四列：
![数组3](http://www.runoob.com/wp-content/uploads/2015/06/two_dimensional_arrays.jpg)


**初始化二维数组**
多维数组可通过大括号来初始值。以下实例为一个 3 行 4 列的二维数组
```language-go
a = [3][4]int{  
 {0, 1, 2, 3} ,   /*  第一行索引为 0 */
 {4, 5, 6, 7} ,   /*  第二行索引为 1 */
 {8, 9, 10, 11},   /* 第三行索引为 2 */
}
```
注意：以上代码中倒数第二行的 } 必须要有逗号，因为最后一行的 } 不能单独一行，也可以写成这样：
```language-go
a = [3][4]int{  
 {0, 1, 2, 3} ,   /*  第一行索引为 0 */
 {4, 5, 6, 7} ,   /*  第二行索引为 1 */
 {8, 9, 10, 11}}   /* 第三行索引为 2 */
 ```

DEMO
```language-go
package main

import "fmt"

func main() {
   /* 数组 - 5 行 2 列*/
   var a = [5][2]int{ {0,0}, {1,2}, {2,4}, {3,6},{4,8}}
   var i, j int

   /* 输出数组元素 */
   for  i = 0; i < 5; i++ {
      for j = 0; j < 2; j++ {
         fmt.Printf("a[%d][%d] = %d\n", i,j, a[i][j] )
      }
   }
}
```
执行结果
```language-bash
a[0][0] = 0
a[0][1] = 0
a[1][0] = 1
a[1][1] = 2
a[2][0] = 2
a[2][1] = 4
a[3][0] = 3
a[3][1] = 6
a[4][0] = 4
a[4][1] = 8
```


#### 向函数传递数组
如果你想向函数传递数组参数，你需要在函数定义时，声明形参为数组，我们可以通过以下两种方式来声明：

> 方式一
形参设定数组大小：
```language-go
func myFunction(param [10]int)
{
    // 函数体
}
```

> 方式二
形参未设定数组大小：
```language-go
func myFunction(param []int)
{
    // 函数体
}
```

DEMO
```language-go
func main() {
    var array = []int{1, 2, 3, 4, 5}
    /* 未定义长度的数组只能传给不限制数组长度的函数 */
    setArray(array)
    /* 定义了长度的数组只能传给限制了相同数组长度的函数 */
    var array2 = [5]int{1, 2, 3, 4, 5}
    setArray2(array2)
}

func setArray(params []int) {
    fmt.Println("params array length of setArray is : ", len(params))
}

func setArray2(params [5]int) {
    fmt.Println("params array length of setArray2 is : ", len(params))
}
```


