# IdValidator.php

中国大陆个人身份证号码验证工具（PHP Composer 版）支持 15 位与 18 位身份证号。基于 [JavaScript 版本](https://github.com/mc-zone/IDValidator)。

Chinese Mainland Personal ID Card Validation.

## 安装

```bash
composer require "jxlwqq/id-validator"
```

## 使用

### 验证身份证号合法性

验证身份证号是否合法，合法返回 true，不合法返回 false：

```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->isValid('440308199901101512');
```

### 获取身份证号信息

当身份证号合法时，返回分析信息（地区、出生日期、性别、校验位），不合法返回 false：
```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->getInfo('440308199901101512');
```

### 生成可通过校验的假数据
伪造符合校验的身份证：

```php
use Jxlwqq\IdValidator\IdValidator;

$idValidator = new IdValidator();
$idValidator->fakeId(); // 默认 生成 18 位
$idValidator->fakeId(true); // 生成 15 位
```

## 参考资料
GB 11643-1999 公民身份证号码

GB 2260-1995 中华人民共和国行政区划代码

## License
MIT


