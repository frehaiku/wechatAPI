# wechatAPI

> A common wechat package,including basic The Individual Subscription Account implements

## Install

```bash
git clone https://github.com/frehaiku/wechatAPI.git

cd wechatAPI

# copy WechatSubscribedAccountSDK.class.php to your project
```

## Usage

```php
include_once "WechatSubscribedAccountSDK.class.php"
$wechat = new WechatSubscribedAccountSDK();
$wechat->checkSignature();

$type = $wechat->getMsg()->getRecType();
switch($type) {
    case WechatSubscribedAccountSDK::MSGTEXT:
        $wechat->text('reply to you')->reply();
        break;
    case WechatSubscribedAccountSDK::MSGEVENT:
        // ...
        break;
 }
```

## Lisence

MIT
