## 介绍

Croon是一个PHP版本的CronTab实现

## 功能：

- 兼容CronTab语法
- 精确到秒级控制
- PSR标准
- 支持事件绑定
- 支持日志

## 依赖

- PHP 5.3.9+
- ext-pcntl
- ext-posix
- [pagon/childprocess](https://github.com/hfcorriez/php-childprocess)
- [pagon/eventemitter](https://github.com/hfcorriez/php-eventemitter)
- [pagon/logger](https://github.com/pagon/logger)
- [pagon/argparser](https://github.com/hfcorriez/php-argparser)

## 安装

Git:

```
git clone git://github.com/hfcorriez/php-crontab.git
```

Composer

```
composer install croon/croon
```

## 使用

```
./bin/croon cron.list -l ~/log/croon.log
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
