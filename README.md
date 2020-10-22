[TOC]

# 禾匠商城v4

##前置条件

###开发者需要掌握的

- Linux基本命令使用、文件、进程管理、Nginx+PHP+MySQL+Redis环境配置

- PHP开发

- MySQL数据库

- Redis数据库

- <a href="https://www.yiiframework.com/doc/guide/2.0/zh-cn" target="_blank">Yii框架</a>

- <a href="https://cn.vuejs.org/index.html" target="_blank">Vue</a>

- <a href="https://element.eleme.cn/#/zh-CN" target="_blank">Element-UI</a>

- <a href="https://getcomposer.org/doc/00-intro.md" target="_blank">Composer</a>

### 运行环境

Linux+Nginx+PHP7.2+MySQL(5.6|5.7)+Redis(4|5)

## 安装教程

### Git版本

1. `clone https://gitee.com/zjhj/zjhj_mall_v4.git`
2. `cd zjhj_mall_v4`
3. `cp config/db.example.php config db.php`，并配置相关数据库信息
4. `cd web`（可选）
5. `php -S localhost:8000`（可选）
6. 打开浏览器访问`http://localhost:8000`（可选）

### 源代码包（开源版）

1. 解压源代码到web目录，如`/www/wwwroot/zjhj_bd/`
2. 浏览器访问您的站点，自动进入安装界面，填写数据库配置信息完成安装

## 配置

### 数据库配置

复制`db.example.php`到`db.php`，按相关参数配置。

### 本地化配置

- 环境变量

复制`.env.example.php`到`.env`按需配置相关选项。

在`YII_DEBUG = true`的情况下，所有错误结果将由Yii框架处理，`YII_DEBUG = false`或未配置`YII_DEBUG`的情况下，所有错误结果将统一处理，HTTP不再直接返回相关错误码，错误码在ajax下返回在`code`字段中。

- 系统配置

复制`local.example.php`到`local.php`按需配置相关选项。

## 快速上手案例

通过案例了解框架处理流程

1. 控制器

创建文件`/controllers/mall/DemoController.php`

```php
<?php
namespace app\controllers\mall;

use app\core\response\ApiCode;

class DemoController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) { // ajax请求返回json数据，此处返回数组将自动转换成json
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'content' => 'hello!',
                ],
                'msg' => 'any msg'

            ];
        } else { // 其他请求返回界面视图
            return $this->render('index');
        }
    }
}

```

2. 视图文件（界面）

创建文件`/views/mall/demo/index.php`

```php
<div id="app" v-cloak>
    <div>{{content}}</div>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                content: ''
            };
        },
        created() {
            this.$request({
                params: {
                    r: 'mall/demo/index'
                }
            }).then(e => {
                this.content = e.data.data.content;
            }).catch(e => {
            });
        },
    });
</script>
```

3. 通过`http://网站目录/web/index.php?r=mall/demo/index`即可访问到。

##代码说明

###目录说明
```
/condif #配置文件
/controllers #控制器
/events #事件定义类
/forms #表单处理
/handlers #事件处理
/jobs #队列任务
/models #数据库表模型
/plugins #插件
/validators #自定义验证器
/views #视图文件
/web #入口文件、资源文件
```

###开发调试模式

要开启开发调试模式，可在项目根目录下创建`.env`配置文件，写入内容

```.env
YII_DEBUG=true
YII_ENV=dev
```

###插件开发

插件与系统交互部分:

- 后台 菜单、权限

- 小程序 导航菜单、底部导航、用户中心

每个插件代码放在/plugins目录下，插件格式可参照下面的demo插件，插件代码结构：

```
├── Plugin.php
├── assets
│   └── css
│       └── style.css
├── controllers
│   └── IndexController.php
├── models
│   └── DemoPost.php
├── tree.txt
└── views
    └── index
        └── index.php
```

Plugin.php: 插件配置文件，必须，需要继承\app\plugins\Plugin。

assets: 插件静态资源文件，如css、js、图片，插件安装时将自动复制到`/web/assets/plugins/插件名`目录下，可使用`\app\helpers\PluginHelper::getPluginBaseAssetsUrl()`和`\app\helpers\PluginHelper::getPluginAssetsPath()`获取。

controllers: 插件控制器目录。

- 后台控制器需要继承`\app\plugins\Controller`

- 小程序端控制器需要继承`\app\controllers\api\ApiController`

models: 插件model文件，注意插件数据表对应的model也放在此文件夹下。

views: 插件视图文件。

小程序端配置：详见\app\plugins\Plugin()->getAppConfig();

## 规范化

### 开发规范

**PSR12**

PHP规范要求符合PSR12的代码规范<https://laravel-china.org/docs/psr/>。

要求每个开发者给自己的代码编辑器安装规范检查工具: 

PHPStorm: <https://www.jianshu.com/p/b5697eb5f401>

Sublime: <https://blog.csdn.net/he426100/article/details/76573038>

**命名规范**

数据表、字段、常量、变量、类、方法函数命名应该明确，尽量能从命名能知道其作用。

驼峰or下划线: 数据表、接口传递字段使用下划线，其它位置尽量使用驼峰。

数据表前缀: 

核心数据表前缀使用zjhj_bd_core_，如日志、定时任务.

基础业务数据表使用zjhj_bd_xxx，如用户、商品、设置。

插件数据表使用zjhj_bd_插件_xxx。

**注释规范**

函数、方法应当编写对应的注释，要求写明函数说明、传入参数类型、返回参数类型。

可参考内容[https://www.cnblogs.com/hellohell/p/5733712.html]

**错误处理**

客户提交数据验证错误: 返回错误信息。

系统错误：抛出异常。

抛出异常将通过日志系统记录进日志。

**数据库规范**

- 明确字段是否是NOT NULL的，如果是NOT NULL，就不用设置默认值了，除非真的需要，如果是可以为NULL，请设置成NOT NULL并添加默认值。

- 表、字段 Charset 统一 `utf8mb4`，Collation 统一 `utf8mb4_general_ci`，存储引擎统一 `InnoDB`。

- 除非情况特殊，严禁使用 `TEXT` / `LONGTEXT` / `BLOB` / `LONGBLOB` 等类型。

- 每张表必须加入 `created_at(创建时间)` / `updated_at(更新时间)` 字段， `deleted_at(软删除时间)` 字段可按需添加。 字段类型统一为`TIMESTAMP`,
`is_delete(是否删除,TINYINT(1)类型)`, 

- 类似 `is_delete` `store_id` 等常用查询的字段，且必须建 Index 索引。

- 对于存储 URL 的字段，必须采用 `VARCHAR` 类型，建议长度：`2048` - `8192`，参见：<https://stackoverflow.com/questions/2659952/maximum-length-of-http-get-request>


**GIT规范**

commit备注禁止出现`1`、`提交`等不明其意的备注！

目前基础功能开发提交至dev分支，后期开发各个模块、插件的功能使用各自分支，要求分支命名能明其意，保持将dev分支合并到自己分支的习惯（每天至少一次）。

不提交的文件应该加入.gitignore，避免误提交。

------

### 安全

代码安全: SQL注入 | XSS | CSRF

逻辑安全: 支付

### 目录规范化
```
.
├── config                  // 配置文件
├── controllers             // 系统控制器
│   ├── admin               // 管理员（独立版管理员）
│   ├── mall                // 商城管理
│   └── api                 // 小程序接口
├── core                    // 系统文件
├── helpers                 // 公共函数、助手类
├── models                  // 系统模型
├── plugins                 // 插件
│   └── demo                // 示例插件
│       ├── assets          // 插件静态文件（css、js）
│       ├── controllers     // 插件控制器
│       ├── models          // 插件模型
│       └── views           // 插件视图
├── runtime                 // 运行临时目录
│   ├── HTML
│   ├── URI
│   ├── cache
│   ├── debug
│   ├── gii-2.0.15.1
│   └── logs
├── validators              // 公共验证器
├── vendor
├── views                   // 系统视图
│   ├── error
│   ├── layouts
│   └── site
└── web
    └── assets
        └── plugins
```

------

### 公共方法/助手类

公共函数放在/helpers/functions.php, 有公共方法可以补充到这里，或是在helpers下创建自己的助手类。

### 本地化配置

**/.env**

主要存储简单的环境配置，如调试模式开关，不应放敏感信息的配置。

**/config/local.php**

存储系统敏感配置信息，如缓存配置、session配置。

### 错误处理

统一由/core/ErrorHandler处理，要求区分json和html的返回结果，错误信息保存进日志。

### 日志系统

日志级别

Error: 系统出错无法继续运行，如抛出Exception。

Warning: 警告，系统继续运行，只是某些结果可能跟预期不符合；场景举例：订单支付模板消息通知，模板消息没法发送成功，可记录警告信息。

Info: 普通信息，不影响系统运行；场景举例：管理员操作日志，如商品删除、订单删除、订单价格修改等操作。

统一系统日志接口

api::info();
api::warning();
api::error();
api::add( ,level);

api::delete(id);
api::list(page, pageSize);
api::detail(id);

### 返回结果

JSON数据返回结果统一以下结构
```json
{
  "code": 0,
  "msg": "操作结果。",
  "data": 1,
  "error": null
}
```

参数|值|类型|说明
---|---|---|---
|code|-1|integer|用户未登录|
|code|0|integer|成功|
|code|1|integer|失败|
|code|500|integer|系统错误|
|msg|-|string|-|
|data|-|-|返回的数据|
|error|-|-|-|

### 前端开发

前端开发的成员请先熟悉ES6新特性<http://es6.ruanyifeng.com/>, 优先阅读: 

- 24.编程风格

- 2.let 和 const命令

- 7.函数的扩展-5.箭头函数

- 14.Promise 对象

前后端数据分离, 管理后台前端使用Vue | Element-ui | Axios, 使用示例见/controllers/DemoController。

## Yii扩展组件

### 队列服务

用于下单单线程处理、定时任务、异步任务。

详细用法见：<a href="https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/usage.md#usage" target="_blank">https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/usage.md#usage</a> 

**队列服务启动**

- 【方法一】一次运行，关闭控制台会自动结束。

    运行`代码目录/yii queue/listen 1` 即可。

- 【方法二】自动后台运行并创建检测服务。
  
    运行`chmod +x 代码目录/queue.sh && 代码目录/queue.sh` 即可。
    
    此脚本会在系统crontab创建任务每分钟检测服务是否运行，不运行会自动启动。

### 支付

支付组件封装了微信支付和余额支付的功能。

调用组件`\Yii::$app->payment`

组件接口

创建待支付订单
```php
$order = new \app\core\payment\PaymentOrder([
    'title' => '商品名称(128)',
    'amount' => 100.00, //订单金额(0.01~100000000.00)
    'orderNo' => '订单号(32)',
    'notifyClass' => MyNotifyClass::class, //支付结果通知类，需继承\app\core\payment\PaymentNotify并实现notify方法
    'supportPayTypes' => [ //选填，支持的支付方式，若不填将支持所有支付方式。
        \app\core\payment\Payment::PAY_TYPE_HUODAO, // 货到付款
        \app\core\payment\Payment::PAY_TYPE_BALANCE, // 余额
        \app\core\payment\Payment::PAY_TYPE_WECHAT, // 微信支付
        \app\core\payment\Payment::PAY_TYPE_ALIPAY, // 支付宝支付
        \app\core\payment\Payment::PAY_TYPE_BAIDU, // 百度支付
        \app\core\payment\Payment::PAY_TYPE_TOUTIAO, // 抖音头条支付
    ],
]);
$id = \Yii::$app->payment->createOrder($order);
```

获取待支付订单id后可将id返回给小程序端，由小程序端的支付组件完成后续付款操作。

### 货币
目前收录了 指定用户余额、积分（添加、减少、查询、退还）
指定用户添加余额
```php
\Yii::$app->currency->setUser(\app\models\User)->balance->add($price, $desc, $customDesc);
```
指定用户减少余额
```php
\Yii::$app->currency->setUser(\app\models\User)->balance->sub($price, $desc, $customDesc);
```
指定用户查询余额
```php
\Yii::$app->currency->setUser(\app\models\User)->balance->select();
```
指定用户退还余额
```php
\Yii::$app->currency->setUser(\app\models\User)->balance->refund($price, $desc, $customDesc);
```
指定用户添加积分
```php
\Yii::$app->currency->setUser(\app\models\User)->integral->add($integral, $desc, $customDesc);
```
指定用户减少积分
```php
\Yii::$app->currency->setUser(\app\models\User)->integral->sub($integral, $desc, $customDesc);
```
指定用户查询可用积分
```php
\Yii::$app->currency->setUser(\app\models\User)->integral->select();
```
指定用户查询总积分
```php
\Yii::$app->currency->setUser(\app\models\User)->integral->selectTotal();
```
指定用户退还积分
```php
\Yii::$app->currency->setUser(\app\models\User)->integral->refund($price, $desc, $customDesc);
```
指定用户添加佣金
```php
\Yii::$app->currency->setUser(\app\models\User)->brokerage->add($price, $desc, $customDesc);
```
指定用户减少佣金
```php
\Yii::$app->currency->setUser(\app\models\User)->brokerage->sub($price, $desc, $customDesc);
```
指定用户查询可用佣金
```php
\Yii::$app->currency->setUser(\app\models\User)->brokerage->select();
```
指定用户查询总佣金
```php
\Yii::$app->currency->setUser(\app\models\User)->brokerage->selectTotal();
```
指定用户退还佣金
```php
\Yii::$app->currency->setUser(\app\models\User)->brokerage->refund($price, $desc, $customDesc);
```

## 事件

系统中一些关键步骤会触发事件，可在应用启动时挂载一些事件处理器处理相关的事件。

### 挂载处理器

创建处理器`handlers\\MyHandler`:

```php
<?php
namespace app\handlers;


class MyHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(\app\models\User::EVENT_REGISTER, function ($event) {
            // todo 事件相应处理
        });
    }
}

```

挂载处理器，编辑文件`handlers/HandlerRegister.php`, 在`getHandlers`加入自己的处理器类即可: 
```php
public function getHandlers()
    {
        return [
            OrderCreatedHandler::class,
            MyHandler::class,
        ];
    }
```

### 商城事件列表

除了框架的事件外，商城运行过程也会触发事件。

#### 用户

| 事件 | 事件类 | 事件说明 |
| ------ | ------ | ------ |
| app\models\User::EVENT_REGISTER | app\events\UserEvent | 小程序新用户加入后触发 |
| app\models\User::EVENT_LOGIN | app\events\UserEvent | 小程序用户登录获取access_token时触发 |


#### 订单

| 事件 | 事件类 | 事件说明 |
| ------ | ------ | ------ |
| app\models\Order::EVENT_CREATED | app\events\OrderEvent | 创建新订单后触发 |
| app\models\Order::EVENT_PAYED | app\events\OrderEvent | 订单支付后触发 |
| app\models\Order::EVENT_SENT | app\events\OrderEvent | 订单发货后触发 |
| app\models\Order::EVENT_CONFIRMED | app\events\OrderEvent | 订单确认收货后触发 |
| app\models\Order::EVENT_SALES | app\events\OrderEvent | 订单过售后触发 |
| app\models\OrderRefund::EVENT_REFUND | app\events\OrderRefundEvent | 订单商品售后处理完成后触发 |

**注：售后订单处理完成后需要添加售后队列**

```php 
// 判断queue队列中的售后是否已经触发
    $queueId = app\models\CoreQueueData:select(app\models\Order $order->token);
    if (!\Yii::$app->queue->isDone($queueId)) {
    // 若未触发
        return ;
    } else {
    // 若已触发，则重新添加
        $id = \Yii::$app->queue->delay(0)->push(new OrderSalesJob([
            'orderId' => app\models\Order $order->id
        ]));
        CoreQueueData::add($id, $event->order->token);
    }
```
#### 订单支付完成事件对外开放
    商城订单支付完成事件需要执行的操作都收录在\app\handlers\orderHandler\OrderPayedHandlerClass中
    插件可以通过Plugin下面的getOrderHandler方法进行重载商城订单支付完成事件
    订单支付完成事件处理需要继承\app\handles\orderHandler\BaseOrderPayedHandler

## 前端（后台页面）

### 页面规范

#### 列表

详见示例r=mall/demo/index

0. 使用el-card组件，头部左侧标题，右侧添加按钮（或其他操作）。

0. 列表上边搜索表单（如果有），从左到右摆列。

0. 列表列数数据项不宜超过6列，数据项过多尽量放在同一个单元格内，用换行方式显示。

0. 对于可预知长度的列，尽量设定合适的宽度。

0. 列表数据加载过程请使用element-ui的loading动画。

0. 列表底部左侧放批量操作按钮（如果有）。

0. 列表底部右侧放分页组件。

0. el-card组件不要显示阴影。

0. 按钮的大小、颜色、样式请参照demo。

#### 表单

详见示例r=mall/demo/edit

0. 待定

####新增分页获取用法
示例： 
`\app\models\Goods::find()->page($pagination, $limit, $page)->all();`
使用page()方法可以直接获取分页列表；其中$pagination为null|\app\core\Pagination对象

### 网络

公共布局文件已经定义了全局的request实例（Axios），直接调用即可。

在vue下建议使用`this.$request`调用

- GET请求 

```javascript
this.$request({
    method: 'get', // 默认
    params: { // GET请求参数
        r: 'demo/index',
        id: 100,
    },
}).then(response => {
    // 请求成功
    // 返回的数据
    console.log(response.data);
}).catch(error => {
    // 请求出错
});
```

- POST请求

```javascript
this.$request({
    method: 'post', // 默认
    params: { // GET请求参数
        r: 'demo/index',
        id: 100,
    },
    data: { // POST提交内容，数据默认统一使用Qs组件转换成QueryString
        title: 'hello',
        content: 'longtext content',
    },
}).then(response => {
    // 请求成功
    // 返回的数据
    console.log(response.data);
}).catch(error => {
    // 请求出错
});
```

### 链接跳转

在Vue内可使用`this.$navigate(params, newWindow)`跳转网址: 

```html
<el-button @click="$navigate({r: 'demo/index'})">在当前页面打开</el-button>
<el-button @click="$navigate({r: 'demo/index'}, true)">在新页面打开</el-button>
```

```javascript
// 在当前页面打开
this.$navigate({
    r: 'demo/index',
    id: 1,
});

// 在新页面打开
this.$navigate({
    r: 'demo/index',
    id: 1,
}, true);
```

其它可使用navigateTo跳转链接

```javascript
navigateTo({
    r: 'demo/index',
    id: 123
});
```

### 获取浏览器地址栏参数

链接跳转js内可使用navigateTo跳转链接
```javascript
getQuery('name');
```

### 公共组件

#### 基础选择器

基础的选择器组件，用法：

```html
<app-picker :multiple="true" :max="2" :list="[{}, {}, {}]" @change="pickerChange"></app-picker>
```

参数：

- multiple: true|false, 是否可多选文件
- max: number, 多选文件上限

事件:

- change: 文件选择完成，点击确认，selected(list);

#### 附件选择器

可选择图片、视频等附件，或是上传新附件，用法：

```html
<app-attachment :multiple="true" :max="2" @selected="attachmentSelected">选择文件</app-attachment>
```

参数：

- multiple: true|false, 是否可多选文件
- max: number, 多选文件上限
- type: string, 文件类型，支持image|video|doc, 默认image
- simple： true|false, 简约模式，只上传图片不加载图片库，适用于独立版管理部分

事件:

- selected: 文件选择完成，点击确认，selected(list);

#### 图片列表

用于展示单张或多张图片，用法

```html
<app-gallery :show-delete="true" @deleted="picDeleted" :list="[]"></app-gallery>
```

参数：

- show-delete: true|false, 是否显示删除按钮
- list: Array, 图片列表，要求格式[ {url: 'http://xxx', ...}, {url: 'http://xxx', ...} ] 
- url: String 单图图片地址，（当传入url参数时，list参数失效）
事件:

- deleted: 点击删除操作，deleted(item, index); // item: 被删除元素, index: 被删元素位置

#### 导航链接列表

可选择跳转链接、底部导航链接，用法：

```html
<app-pick-link @selected="selectAdvertUrl"><el-button size="mini">选择链接</el-button></app-pick-link>
```

事件:

- selected: 链接选择完成，点击确认，selected(list);

- type single(默认)|单选, multiple|多选

#### 图片显示

```html
<app-image mode="" width="50px" height="50px" src="http://aaa.com/test.jpg"></app-image>
```

参数:

- mode: string, 图片显示方式，支持aspectFill（默认，图片自动裁剪），scaleToFill（不裁剪，自动拉伸）。

- width: string, 图片宽度，默认50px。

- radius: string, 图片圆角，默认0%

- height: string, 图片高度，默认50px。

- src: string, 图片地址。

#### 自动省略号文本

```html
<app-ellipsis :line="1">文本内容</app-ellipsis>
```

参数:

- line: number, 文本行数，当文本超过这一数值将自动显示为省略号。

#### 地图展示

可搜索位置、获取经纬度，用法：

```html
<app-map @map-submit="mapEvent"><el-button size="mini">展开地图</el-button></app-map>
```

事件:

- map-submit: 坐标选择完成，点击确认，mapEvent(e);

#### 省市区选择器

```html
<app-district :level="3" :detail="[]" @selected="selectDistrict" :edit="[]"></app-district>
```

参数：

- level: number 展示省市区的级数 3--展示省市区 2--展示省市 1--展示省
- detail: array 所有已选择的省市区
- edit: array 选中的省市区

事件：

- selected: 勾选时触发selected(list)

#### 商品编辑

```html
<app-goods></app-goods>
```

参数：
- is_info: 基本信息显示状态 0--不显示 1--显示可编辑 2--显示不可编辑
- in_attr：规格及库存状态 0--不显示 1--显示可编辑
- is_goods：商品设置状态： 0--不显示 1--显示可编辑
- is_marketing：营销设置状态： 0--不显示 1--显示可编辑
- is_detail：商品详情状态： 0--不显示 1--显示可编辑
- is_share：分销设置状态： 0--不显示 1--显示可编辑
- is_member：会员设置状态： 0--不显示 1--显示可编辑
- url：表单提交地址 默认：'mall/goods/edit'
- referrer：表单保存之后返回地址 默认：'mall/goods/index'
- form：动态表单数据，数据结构同form表单
（如需添加额外的规格属性（积分等））```form: {extra:{first:{name:'积分',value:''}}}```
- rule：动态表单验证规则，数据数据接口同form表单验证

内部方法：
- getDetail(id) 获取指定商品ID的商品信息
- getCats() 获取所有分类
- getServices() 获取商品服务
- getCards() 获取卡券列表
- getMembers() 获取会员列表
- getFreight() 获取会费规则
- getShareSetting() 获取分销设置

#### 文本编辑器

文本编辑器支持数据双向绑定（v-model）。

```php
// 加载组件
Yii::$app->loadViewComponent('app-rich-text')
```

```html
<app-rich-text v-model="content"></app-rich-text>
```

#### 热区选择

```php
// 加载组件
Yii::$app->loadViewComponent('app-hotspot')
```

```html
<app-hotspot @confirm></app-hotspot>
```
参数：
- multiple：Boolean 是否多选
- pic_url：图片的链接 String
- width：图片宽度 String
- height：图片高度 String
- hotspotArray：热区数组 Array
- is_link：是否选择链接 Boolean
事件：
- confirm 热区选择事件 参数：热区的集合

#### 弹窗选择组件

```php
// 加载组件
Yii::$app->loadViewComponent('app-dialog-select')
```

```html
<app-dialog-select @selected></app-dialog-select>
```
参数：
- url：请求链接 String
- multiple：是否多选 Boolean
- title: 弹框标题
事件：
- selected 选择的数据 当multiple为false时参数为对象，当multiple为true时参数为对象数组

## 模板消息
发送统一调用\app\forms\common\template\TemplateSend;
参数说明：
- $user: 模板消息接受者的\app\models\User对象||对象数组 单发模板消息时为对象，群发模板消息时为对象数组
- $templateTpl: 需要发送的模板消息的标示，例如订单支付标示--order_pay_tpl
- $templateId: 需要发送的模板消息ID 仅限群发这种直接获取template_id的，其他需要查询数据库的都不可传次参数
- $page: 小程序跳转页面
- $data: 模板消息数据

## 其它

### mall_setting字段说明

在后台管理代码 /models/Mall.php 有具体说明
