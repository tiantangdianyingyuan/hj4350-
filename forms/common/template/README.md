[TOC]

# 模板消息

###前置说明
- 模板消息仅在发送时对接各个平台

###配置说明（仅限插件中配置模板消息）
- 消息配置类：`\app\form\common\template\TemplateForm.php`

###消息发送参数(处理模板消息发送需要的参数)
- 消息参数类：`\app\form\common\template\tplmsg\BaseTemplate.php`
```php
    $user: 必填 接收消息的用户 `\app\models\User`
    $page: 选填 模板消息跳转路径 默认：'\pages\index\index'
    $templateTpl 发送的模板消息标记
    $templateForm 发送的模板消息的配置类
    ... 其他模板消息上自带的参数
```

###发送说明
- 消息发送平台类：`\app\form\common\template\templateSender.php`（各个平台需要继承该类，并添加发送方法）
- 消息发送接口：`\app\form\common\template\TemplateSend.php`

###模板消息列表
- `\app\form\common\template\TemplateList.php`