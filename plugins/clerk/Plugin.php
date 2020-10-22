<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\clerk;

use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\handlers\HandlerBase;
use app\models\ClerkUser;
use app\plugins\bargain\forms\api\StatisticsForm;
use app\plugins\bargain\forms\common\CommonSetting;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\handlers\HandlerRegister;
use app\forms\OrderConfig;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '核销中心',
                'route' => 'plugin/clerk/mall/index/index',
                'icon' => 'el-icon-star-on',
                'is_jump' => 0
            ]
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'clerk';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '核销员';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'bargain_time' => $imageBaseUrl . '/icon-bargain-time.png',
                'bargain_hb_good' => $imageBaseUrl . '/bargain-hb-good.png',
                'buy_now' => $imageBaseUrl . '/buy-now.png',
                'buy_small' => $imageBaseUrl . '/buy-small.png',
                'find' => $imageBaseUrl . '/find.png',
                'go_on' => $imageBaseUrl . '/go-on.png',
                'header' => $imageBaseUrl . '/header.png',
                'join_big' => $imageBaseUrl . '/join-big.png',
                'join_small' => $imageBaseUrl . '/join-small.png',
                'top1' => $imageBaseUrl . '/top1.png',
                'top' => $imageBaseUrl . '/top.png',
                'activity_header' => $imageBaseUrl . '/icon-bargain-activity-header-3.gif'
            ],
        ];
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'clerk',
                'name' => '核销中心',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-clerk.png',
                'value' => '/plugins/clerk/index/index',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getSpecialNotSupport()
    {
        $path = '/plugins/clerk/index/index';
        $res = [];
        if (\Yii::$app->user->isGuest) {
            $res = [
                'user_center' => [
                    $path
                ]
            ];
        } else {
            $clerkUser = ClerkUser::findOne([
                'user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
            ]);
            if (!$clerkUser) {
                $res = [
                    'user_center' => [
                        $path
                    ]
                ];
            }
        }
        return $res;
    }
}
