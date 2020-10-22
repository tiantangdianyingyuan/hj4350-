<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common;


use app\forms\common\order\CommonOrder;
use app\forms\mall\copyright\CopyrightForm;
use app\forms\mall\home_page\HomePageForm;
use app\forms\mall\navbar\NavbarForm;
use app\forms\mall\poster\PosterForm;
use app\forms\mall\recharge\RechargeSettingForm;
use app\forms\mall\sms\SmsForm;
use app\forms\mall\user_center\UserCenterForm;
use app\forms\PickLinkForm;
use app\models\AliappConfig;
use app\models\ClerkUser;
use app\models\GoodsCats;
use app\models\HomeBlock;
use app\models\MallMembers;
use app\models\Option;
use yii\helpers\ArrayHelper;

class CommonAppConfig
{
    /**
     * 底部导航设置
     * @return null
     */
    public static function getNavbar()
    {
        $option = CommonOption::get(
            Option::NAME_NAVBAR,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new NavbarForm())->getDefault()
        );

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        //todo 临时解决底部导航问题
        $is_live = true;
        $is_pick = true;
        if (!in_array('live', $permission)) {
            $is_live = false;
        }

        if (!in_array('pick', $permission)) {
            $is_pick = false;
        }

        foreach ($option['navs'] as $key => $nav) {
            if (isset($nav['params'])) {
                $urlList = explode('?', $nav['url']);
                $url = $urlList[0] . '?';
                foreach ($nav['params'] as $item) {
                    if ($item['value'] !== '') {
                        $url .= $item['key'] . '=' . ($nav['open_type'] == 'web' ? urlencode($item['value']) : $item['value']) . '&';
                    }
                }
                $option['navs'][$key]['url'] = substr($url, 0, -1);
            }

            // TODO 小程序插件权限统一处理
            if (isset($nav['key']) && $nav['key'] && !isset($permissionFlip[$nav['key']])) {
                unset($option['navs'][$key]);
            }

            $check = strpos($nav['url'], 'wx2b03c6e691cd7370') !== false;
            if (($nav['url'] == '/pages/live/index' || $check) && !$is_live) {
                unset($option['navs'][$key]);
            }
            if (($nav['url'] == '/plugins/pick/index/index') && !$is_pick) {
                unset($option['navs'][$key]);
            }
        }
        $option['navs'] = array_values($option['navs']);

        if (gettype($option['shadow']) === 'string') {
            $option['shadow'] = json_decode($option['shadow']);
        }

        return $option;
    }

    /**
     * 用户中心配置
     * @return null
     * @throws \Exception
     * 处理太复杂了，已移动到\app\forms\common\config\UserCenterConfig
     */
    public static function getUserCenter($isApi = false)
    {
        $userCenterDefault = (new UserCenterForm())->getDefault();
        $option = CommonOption::get(
            Option::NAME_USER_CENTER,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            $userCenterDefault
        );
        if (!isset($option['account_bar'])) $option['account_bar'] = $userCenterDefault['account_bar'];
        $option['account_bar']['integral']['navigate_enabled'] = false;
        foreach ($option['account_bar'] as $key => &$item) {
            switch ($key) {
                case 'balance':
                    $item['page_url'] = '/page/balance/balance';
                    break;
                case 'integral':
                    $item['page_url'] = '/plugins/integral_mall/index/index';
                    break;
                case 'coupon':
                    $item['page_url'] = '/page/coupon/index/index';
                    break;
                case 'card':
                    $item['page_url'] = '/page/card/index/index';
                    break;
            }
        }
        unset($item);

        $arr = [];
        foreach ($option['order_bar'] as $k => $item) {
            $item['link_url'] = '/pages/order/index/index?status=' . ((int)$k + 1);
            if ((int)$k + 1 === 5) {
                $item['link_url'] = '/pages/order/refund/index';
            }
            $item['open_type'] = PickLinkForm::OPEN_TYPE_2;
            $arr[] = $item;
        }
        $orderInfoCount = (new CommonOrder())->getOrderInfoCount();
        $arr[0]['text'] = $orderInfoCount[0] ?: '';
        $arr[1]['text'] = $orderInfoCount[1] ?: '';
        $arr[2]['text'] = $orderInfoCount[2] ?: '';
        $arr[3]['text'] = $orderInfoCount[3] ?: '';
        $arr[4]['text'] = $orderInfoCount[4] ?: '';
        $option['order_bar'] = $arr;

        // TODO 代码兼容 2019-07-03
        if (!isset($option['member_bg_pic_url'])) {
            $option['member_bg_pic_url'] = $userCenterDefault['member_bg_pic_url'];
        }

        $adminInfo = \Yii::$app->mall->user->adminInfo;
        if (!$adminInfo) {
            throw new \Exception('商城管理员不存在');
        }
        $permissions = \Yii::$app->branch->childPermission($adminInfo);

        // TODO 代码兼容 2019-07-03
        if (count($option['account']) != 3) {
            $option['account'] = $userCenterDefault['account'];
            $res = CommonOption::set(Option::NAME_USER_CENTER, $option, \Yii::$app->mall->id, Option::GROUP_APP);
        }

        $newArr = [];
        $name = 'integral_mall';
        foreach ($option['account'] as $key => $item) {
            $item['is_show'] = 0;
            if ($key == 1) {
                $plugins = \Yii::$app->plugin->list;
                foreach ($plugins as $plugin) {
                    if ($plugin->name == $name) {
                        $item['is_show'] = 1;
                        $option['account_bar']['integral']['navigate_enabled'] = true;
                    }
                }

                // 判断是否为子账号商城，判断子账号商城是否有积分商城插件权限
                $userIdentity = \Yii::$app->mall->user->identity;
                if ($item['is_show'] && $userIdentity->is_super_admin != 1) {
                    if (in_array($name, $permissions)) {
                        $item['is_show'] = 1;
                        $option['account_bar']['integral']['navigate_enabled'] = true;
                    } else {
                        $item['is_show'] = 0;
                        $option['account_bar']['integral']['navigate_enabled'] = false;
                        $option['account_bar']['integral']['page_url'] = '';
                    }
                }
            } else {
                $item['is_show'] = 1;
            }
            $newArr[] = $item;
        }
        $option['account'] = $newArr;

        if ($isApi) {
            $option = self::checkPermission($option);
        }

        //钱包配置
        $option['recharge_setting'] = CommonAppConfig::getRechargeSetting();
        $option['order_bar'] = self::updateOrderBar($option['order_bar']);

        return $option;
    }

    // 兼容旧数据  将待评价改为已完成
    private static function updateOrderBar($orderBar)
    {
        if ($orderBar && is_array($orderBar)) {
            foreach ($orderBar as $key => $value) {
                if ($value['name'] == '待评价') {
                    $orderBar[$key]['name'] = '已完成';
                }
            }
        }

        return $orderBar;
    }

    private static function checkPermission($option)
    {
        $adminInfo = \Yii::$app->mall->user->adminInfo;
        if (!$adminInfo) {
            throw new \Exception('商城管理员不存在');
        }

        $permissions = \Yii::$app->branch->childPermission($adminInfo);

        //小程序管理入口权限
        if (!\Yii::$app->user->isGuest) {
            $app_admin = true;

            if (\Yii::$app->user->identity->identity->is_super_admin != 1) {
                if (\Yii::$app->user->identity->identity->is_operator == 1) {
                    if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin'))
                        || !in_array('app_admin', $permissions)) {
                        $app_admin = false;
                    }
                } else {
                    if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin'))
                        || !in_array('app_admin', $permissions)
                        || \Yii::$app->user->identity->identity->is_admin != 1) {
                        $app_admin = false;
                    }
                }
            }
            //小程序核销入口权限
            $clerk = true;
            $clerkUser = ClerkUser::findOne([
                'user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
            ]);
            if (\Yii::$app->user->identity->identity->is_super_admin != 1 && \Yii::$app->user->identity->identity->is_admin != 1) {
                if (empty(\Yii::$app->plugin->getInstalledPlugin('clerk'))
                    || !in_array('clerk', $permissions) || empty($clerkUser)) {
                    $clerk = false;
                }
            }

            // 加载会员图标
            if (\Yii::$app->user->identity->identity->member_level != 0) {
                $level = MallMembers::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'level' => \Yii::$app->user->identity->identity->member_level,
                    'status' => 1, 'is_delete' => 0
                ]);
                if ($level) {
                    $option['member_pic_url'] = $level->pic_url;
                }
            }
        } else {
            $app_admin = false;
            $clerk = false;
        }


        //剔除无权限入口
        $menu = [];
        foreach ($option['menus'] as $i => $v) {
            if ($v['open_type'] == 'app_admin' && !$app_admin) {
                continue;
            }
            if (strstr($v['link_url'], 'clerk')) {
                if (\Yii::$app->user->id) {
                    $clerkUser = ClerkUser::find()->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'user_id' => \Yii::$app->user->id
                    ])->one();
                    if (!$clerkUser) {
                        continue;
                    }
                }

                if (!$clerk) {
                    continue;
                }
            }
            if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == APP_PLATFORM_TTAPP) {
                continue;
            }

            if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform == APP_PLATFORM_ALIAPP) {
                $aliappConfig = AliappConfig::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                ]);
                if ($aliappConfig) {
                    $v['tnt_inst_id'] = $aliappConfig->cs_tnt_inst_id;
                    $v['scene'] = $aliappConfig->cs_scene;
                }
            }

            $menu[] = $v;
        }
        $option['menus'] = $menu;

        return $option;
    }

    /**
     * 商城版权设置
     * @return null
     */
    public static function getCoryRight()
    {
        $option = CommonOption::get(
            Option::NAME_COPYRIGHT,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new CopyrightForm())->getDefault();
        $option = self::check($option, $default);

        // TODO 兼容 2019-6-24
        if (!isset($option['link'])) {
            $option['params'] = [];
            $option['link'] = [];
        }

        return $option;
    }

    /**
     * @param $mchId
     * @return null
     */
    public static function getSmsConfig($mchId = null)
    {
        if ($mchId === null || $mchId === '') {
            $isGuest = true;
            try {
                $isGuest = \Yii::$app->user->isGuest;
            } catch (\Exception $exception) {
            }
            if (!$isGuest) {
                $mchId = \Yii::$app->user->identity->mch_id;
            } else {
                $mchId = 0;
            }
        }
        $option = CommonOption::get(
            Option::NAME_SMS,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN,
            null,
            $mchId
        );
        $default = (new SmsForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }

    /**
     * 商城海报设置
     * @return null
     */
    public static function getPosterConfig()
    {
        $option = CommonOption::get(
            Option::NAME_POSTER,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        $default = (new PosterForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    public static function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = self::check($list[$key], $value);
            }
        }
        return $list;
    }

    /**
     * 小程序首页配置
     * @return null
     */
    public static function getHomePageConfig()
    {
        $option = CommonOption::get(
            Option::NAME_HOME_PAGE,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new HomePageForm())->getDefault()
        );

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        $catList = [];
        $cat = [];
        $blockList = [];
        $block = [];
        foreach ($option as $item) {
            switch ($item['key']) {
                case 'cat':
                    if ($item['relation_id'] > 0) {
                        $catList[] = $item['relation_id'];
                    }
                    break;
                case 'block':
                    if ($item['relation_id'] > 0) {
                        $blockList[] = $item['relation_id'];
                    }
                    break;
                default:
            }
        }
        if (!empty($catList)) {
            $cat = GoodsCats::find()->where([
                'is_delete' => 0,
                'id' => $catList,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => 0,
            ])->select(['id'])->column();
        }
        if (!empty($blockList)) {
            $block = HomeBlock::find()->where([
                'is_delete' => 0,
                'id' => $blockList,
                'mall_id' => \Yii::$app->mall->id,
            ])->select(['id'])->column();
        }

        // 排除分类 魔方已被删除的数据
        foreach ($option as $key => $item) {
            // 小程序端插件权限统一处理
            if (isset($item['permission_key'])
                && $item['permission_key']
                && !isset($permissionFlip[$item['permission_key']])
            ) {
                unset($option[$key]);
                continue;
            }

            // 移除被删除的分类
            if ($item['key'] == 'cat' && $item['relation_id'] > 0 && !in_array($item['relation_id'], $cat)) {
                unset($option[$key]);
                continue;
            }
            // 移除被删除的魔方
            if ($item['key'] == 'block' && $item['relation_id'] > 0 && !in_array($item['relation_id'], $block)) {
                unset($option[$key]);
                continue;
            }

            $baseUri = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            if ($item['key'] == 'coupon') {
                $option[$key]['coupon_url'] = $item['coupon_url'] ?: $baseUri . '/statics/img/mall/home_block/coupon-open.png';
                $option[$key]['coupon_not_url'] = $item['coupon_not_url'] ?: $baseUri . '/statics/img/mall/home_block/coupon-close.png';
                $option[$key]['discount_not_url'] = isset($item['discount_not_url']) && $item['discount_not_url'] ? $item['discount_not_url'] : $baseUri . '/statics/img/mall/home_block/discount-bg.png';
            }
        }
        $arr = ArrayHelper::toArray($option);

        return array_values($arr);
    }

    /**
     * 小程序充值
     * @return null
     */
    public static function getRechargeSetting()
    {
        $option = CommonOption::get(
            Option::NAME_RECHARGE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );

        $default = (new RechargeSettingForm())->getDefault();
        $option = self::check($option, $default);

        return $option;
    }

    /**
     * 页面转发标题、图片设置
     */
    public static function getAppShareSetting()
    {
        $option = CommonOption::get(
            Option::NAME_APP_SHARE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            []
        );

        return $option;
    }

    /**
     * @return array
     * 分类样式
     */
    public static function getAppCatStyle($mch_id = 0)
    {
        $option = CommonOption::get(
            Option::NAME_CAT_STYLE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            [],
            $mch_id
        );

        $default = [
            'cat_style' => '3',
            'recommend_count' => '3',
            'cat_goods_count' => '1',
            'cat_goods_cols' => '1'
        ];
        $option = self::check($option, $default);

        return $option;
    }

    // 获取小程序自定义标题
    public static function getBarTitle()
    {
        $option = CommonOption::get(Option::NAME_PAGE_TITLE, \Yii::$app->mall->id, Option::GROUP_APP);

        $newOption = [];
        if ($option) {
            foreach ($option as $item) {
                $newOption[$item['name']] = $item;
            }
        }

        $default = PickLinkForm::getCommon()->getTitle();
        foreach ($default as $key => $item) {
            if ($item['value'] == '/pages/index/index') {
                unset($default[$key]);
            }
        }
        $default = array_values($default);
        foreach ($default as &$item) {
            if (isset($newOption[$item['name']])) {
                $item['new_name'] = $newOption[$item['name']]['new_name'];
            }
        }
        unset($item);

        return $default;
    }

    public static function getRecommendSetting()
    {
        $option = CommonOption::get(
            Option::NAME_RECHARGE_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            (new RechargeSettingForm())->getDefault()
        );
    }


    /**
     * @return array
     * 获取所有页面的默认配置（暂时只有授权页面）
     */
    public static function getDefaultPageList()
    {
        $picUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/mall';
        return [
            'auth' => [
                'pic_url' => $picUrl . '/auth-default.png',
                'hotspot' => [
                    'width' => '224',
                    'height' => '80',
                    'left' => '340',
                    'top' => '566',
                    'defaultX' => '340',
                    'defaultY' => '566',
                    'link' => '',
                    'open_type' => 'cancel'
                ],
                'hotspot_cancel' => [
                    'width' => '224',
                    'height' => '80',
                    'left' => '84',
                    'top' => '566',
                    'defaultX' => '84',
                    'defaultY' => '566',
                    'link' => '',
                    'open_type' => 'cancel'
                ]
            ]
        ];
    }
}
