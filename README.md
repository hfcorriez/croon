## Croon [![Build Status](https://travis-ci.org/hfcorriez/croon.png?branch=master)](https://travis-ci.org/hfcorriez/croon)

Croon是一个PHP版本的CronTab实现

之前也做过一个类似的实现[php-crontab](https://github.com/hfcorriez/php-crontab)，Croon与之不同的是：使用进程管理方式Fork工作进程；有友好的日志模块；加入事件驱动；更佳规范和稳定

## 功能：

- 兼容CronTab语法
- 精确到秒级控制
- [PSR标准](https://github.com/hfcorriez/fig-standards)
- 支持事件绑定
- 支持日志

## 依赖

- PHP 5.3.9+
- ext-pcntl
- ext-posix
- [Composer](http://getcomposer.org)

库依赖（使用`composer install`自动安装）

- [pagon/childprocess](https://github.com/hfcorriez/php-childprocess)
- [pagon/eventemitter](https://github.com/hfcorriez/php-eventemitter)
- [pagon/logger](https://github.com/pagon/logger)
- [pagon/argparser](https://github.com/hfcorriez/php-argparser)

## 安装

下载使用：

[Latest Release](https://github.com/hfcorriez/croon/releases/)

Git:

```
git clone git://github.com/hfcorriez/croon.git
cd croon
composer install
```

Composer

```
composer require croon/croon
```

## 使用

### 基本用法

`croon.list`

```
* * * * * * ls -l >> /tmp/ls.log

# 兼容系统crontab
* * * * * pwd >> /tmp/pwd.log
```

执行

```
./bin/croon croon.list -l croon.log
```

`croon.log`

```
[2013-04-20 14:07:01] 27a6c9 -  debug   - Croon...!!!
[2013-04-20 14:07:01] 27a6c9 -   info   - Execute (ls >> /tmp/ls.log)
[2013-04-20 14:07:01] 27a6c9 -   info   - Finish (ls >> /tmp/ls.log)[0]
```

### 以mysql数据库为计划任务源
* 修改数据库连接信息 bin/croon_with_mysql
* 表结构为
```

+---------------------+--------------------------------------------------------+
| time                | command                                                |
+---------------------+--------------------------------------------------------+
| [秒] 分 时 日 月 周   | command                                                |
+---------------------+--------------------------------------------------------+

```
执行

```
./bin/croon_with_mysql -l croon.log
```


### 高级用法

`bootstrap.php`

```php
<?php

// 绑定启动事件
$croon->on('run', function() use($croon) {
    // 注入db
    $croon->db = new \PDO('mysql://localhost:3306;dbname=reports');
});

// 绑定执行事件
$croon->on('executed', function ($command, $output) use ($croon) {
    // 记录执行结果
    $croon->db->exec(sprintf(
        'INSERT INTO cron(command, status, stdout, stderr, create_time) VALUES ("%s", "%s", "%s", "%s", "%s")',
        $command, $output[0], $output[1], $output[2], date('Y-m-d H:i:s'))
    );
});
```

执行

```
./bin/croon croon.list -l croon.log -b bootstrap.php
```

## License

(The MIT License)

Copyright (c) 2012 hfcorriez &lt;hfcorriez@gmail.com&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
