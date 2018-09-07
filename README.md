# IdValidator.php

中国大陆个人身份证号码验证工具（PHP Composer 版）支持 15 位与 18 位身份证号。基于 [JavaScript 版本](https://github.com/mc-zone/IDValidator)。

Chinese Mainland Personal ID Card Validation.


[![Build Status](https://travis-ci.org/jxlwqq/id-validator.svg?branch=master)](https://travis-ci.org/jxlwqq/id-validator)
[![StyleCI](https://github.styleci.io/repos/147758862/shield?branch=master)](https://github.styleci.io/repos/147758862)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jxlwqq/id-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jxlwqq/id-validator/?branch=master)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fjxlwqq%2Fid-validator.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fjxlwqq%2Fid-validator?ref=badge_shield)

## 安装

```bash
composer require "jxlwqq/id-validator"
```

## 使用

> `440308199901101512` 和 `610104620927690` 示例身份证均为随机生成的假数据，如撞车，请联系删除。

### 验证身份证号合法性

验证身份证号是否合法，合法返回 true，不合法返回 false：

```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->isValid('440308199901101512'); // 18 位
$idValidator->isValid('610104620927690'); // 15 位
```

### 获取身份证号信息

当身份证号合法时，返回分析信息（地区、出生日期、性别、校验位），不合法返回 false：
```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->getInfo('440308199901101512'); // 18 位
$idValidator->getInfo('610104620927690'); // 15 位
```

### 生成可通过校验的假数据
伪造符合校验的身份证：

```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->fakeId(); // 18 位
$idValidator->fakeId(false); // 15 位
```

## 参考资料
GB 11643-1999 公民身份证号码

GB 2260-1995 中华人民共和国行政区划代码

## License
MIT


