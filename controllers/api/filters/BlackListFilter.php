<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\api\filters;


use app\core\Plugin;
use app\core\response\ApiCode;
use app\models\UserInfo;
use yii\base\ActionFilter;

class BlackListFilter extends ActionFilter
{
    private $routeList = [
        'api/order/submit'
    ];

    public function beforeAction($action)
    {
        /** @var UserInfo $userInfo */
        $userInfo = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        if ($userInfo && $userInfo->is_blacklist) {
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getBlackList')) {
                    $routeList = array_merge($this->routeList, $object->getBlackList());
                    $this->routeList = $routeList;
                }
            }
            $route = \Yii::$app->requestedRoute;
            if (strpos($route, '/') == 0) {
                $route = substr($route, 1);
            }

            // 黑名单用户无法访问相关路由
            if (in_array($route, $this->routeList)) {
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '下单被限制 请联系管理员',
                ];
                return false;
            }
        }

        return true;
    }
}
