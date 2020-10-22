# 插件开发


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