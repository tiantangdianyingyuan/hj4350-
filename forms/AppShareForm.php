<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\forms;

use app\core\response\ApiCode;
use app\plugins\Plugin;


class AppShareForm
{
    /**
     * 自定义分享页面
     * @return mixed|string
     */
    public function getList()
    {
        $links = $this->links();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $links
            ]
        ];
    }

    /**
     * 导航链接
     * @return array
     */
    public function links()
    {
        $list = [
            [
                'name' => '首页',
                'page_url' => 'pages/index/index'
            ],
        ];

        $plugins = \Yii::$app->plugin->list;
        foreach ($plugins as $plugin) {
            $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getShareContentSetting')) {
                $list = array_merge($list, $object->getShareContentSetting());
            }
        }

        return $list;
    }
}
