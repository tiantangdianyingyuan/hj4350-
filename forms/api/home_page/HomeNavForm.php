<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\home_page;


use app\forms\api\app_platform\Transform;
use app\models\ClerkUser;
use app\models\HomeNav;
use app\models\Model;

class HomeNavForm extends Model
{
    /**
     * @return mixed
     */
    public function getHomeNav()
    {
        $homeNavs = HomeNav::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'status' => 1,
                'is_delete' => 0,
            ])
            ->orderBy(['sort' => SORT_ASC])
            ->all();

        //小程序管理入口权限
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        $app_admin = true;
        if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin')) || !in_array('app_admin', $permission) || empty(\Yii::$app->user->identity->identity->is_admin) || \Yii::$app->user->identity->identity->is_admin != 1) {
            $app_admin = false;
        }
        $is_live = true;
        if (!in_array('live', $permission)) {
            $is_live = false;
        }

        $is_clerk = true;
        if (!in_array('clerk', $permission)) {
            $is_clerk = false;
        }

        $newData = [];
        /** @var HomeNav $homeNav */
        foreach ($homeNavs as $homeNav) {
            // TODO 小程序端插件权限统一处理 无需再单独处理
            if ($homeNav->sign && !isset($permissionFlip[$homeNav->sign])) {
                continue;
            }

            if ($homeNav->open_type == 'app_admin' && !$app_admin) {
                continue;
            }

            $check = strpos($homeNav->url, 'wx2b03c6e691cd7370') !== false;
            if (($homeNav->url == '/pages/live/index' || $check) && !$is_live) {
                continue;
            }

            if ($homeNav->url == '/plugins/clerk/index/index') {
                if (\Yii::$app->user->id) {
                    $clerkUser = ClerkUser::find()->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->user->identity->mch_id,
                        'is_delete' => 0,
                        'user_id' => \Yii::$app->user->id
                    ])->one();
                    if (!$clerkUser) {
                        continue;
                    }
                }

                if (!$is_clerk) {
                    continue;
                }
            }
            if ($homeNav->open_type == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                continue;
            }
            $arr = [
                'id' => $homeNav->id,
                'icon_url' => $homeNav->icon_url,
                'link_url' => $homeNav->url,
                'name' => $homeNav->name,
                'open_type' => $homeNav->open_type,
                'params' => $homeNav->params ? json_decode($homeNav->params, true) : [],
            ];
            $newData[] = $arr;
        }
        $newData = Transform::getInstance()->transformHomeNav($newData);

        return $newData;
    }
}
