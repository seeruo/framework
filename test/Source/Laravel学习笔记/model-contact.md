---
title: 模型关联
type: Model,Eloqunt
date: 2016-08-29 14:55:20
tags: iptable
---

```language-php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 获取与用户关联的电话号码记录。
     */
    public function phone()
    {
        return $this->hasOne('App\Phone', 'id', 'u_id');
    }
}
```
使用:
```language-php
$phone = User::find(1)->phone;
```