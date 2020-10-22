# PHP Wechat SDK

## Usage

### Install

```bash
composer require 'luweiss/wechat'
```

### Wechat

```php
require __DIR__ . '/vendor/autoload.php';

$wechat = new \luweiss\Wechat\Wechat();
$accessToken = $wechat->getAccessToken;
```

### WechatPay

```php
require __DIR__ . '/vendor/autoload.php';

$wechatPay = new \luweiss\Wechat\WechatPay();
$res = $wechatPay->unifiedOrder();
```
