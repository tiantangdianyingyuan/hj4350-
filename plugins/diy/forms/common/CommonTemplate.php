<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 15:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\common;


use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\DiyTemplate;

/**
 * Class CommonTemplate
 * @package app\plugins\diy\forms\common
 * @property Mall $mall
 */
class CommonTemplate extends Model
{
    public $mall;

    public static function getCommon($mall = null)
    {
        $instance = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance->mall = $mall;
        return $instance;
    }

    /**
     * @param $pagination
     * @param array $search
     * @return array|\yii\db\ActiveRecord[]
     * 获取模板列表
     */
    public function getList(&$pagination, $search = [])
    {
        $query = DiyTemplate::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
        ]);
        isset($search['type']) && $query->andWhere(['type' => $search['type']]);
        isset($search['keyword']) && $query->keyword($search['keyword'], ['like', 'name', $search['keyword']]);

        return $query->page($pagination, 10)->orderBy(['created_at' => SORT_DESC])->all();
    }

    /**
     * @param $id
     * @return DiyTemplate|null
     */
    public function getTemplate($id)
    {
        return DiyTemplate::findOne([
            'mall_id' => $this->mall->id,
            'id' => $id
        ]);
    }

    /**
     * @param $id
     * @return DiyTemplate|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        $template = $this->getTemplate($id);
        if (!$template) {
            throw new \Exception('模板不存在');
        }
        if ($template->is_delete == 1) {
            throw new \Exception('模板已删除');
        }
        $template->is_delete = 1;
        if (!$template->save()) {
            throw new \Exception($this->getErrorMsg($template));
        }
        return $template;
    }

    public function allComponents()
    {
        $pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
        $result = [
            [
                'groupName' => '基础组件',
                'list' => [
                    [
                        'id' => 'search',
                        'name' => '搜索',
                        'icon' => $pluginUrl . '/images/new-tpl/form_search_icon.png',
                    ],
                    [
                        'id' => 'nav',
                        'name' => '图文导航',
                        'icon' => $pluginUrl . '/images/new-tpl/form_nav_icon.png',
                    ],
                    [
                        'id' => 'banner',
                        'name' => '轮播广告',
                        'icon' => $pluginUrl . '/images/new-tpl/form_banner_icon.png',
                    ],
                    [
                        'id' => 'notice',
                        'name' => '公告',
                        'icon' => $pluginUrl . '/images/new-tpl/form_notice_icon.png',
                    ],
                    [
                        'id' => 'topic',
                        'name' => '专题',
                        'icon' => $pluginUrl . '/images/new-tpl/form_topic_icon.png',
                        'key' => 'topic'
                    ],
                    [
                        'id' => 'link',
                        'name' => '标题',
                        'icon' => $pluginUrl . '/images/new-tpl/form_title_icon.png',
                    ],
                    [
                        'id' => 'rubik',
                        'name' => '图片广告',
                        'icon' => $pluginUrl . '/images/new-tpl/form_rubik_icon.png',
                    ],
                    [
                        'id' => 'video',
                        'name' => '视频',
                        'icon' => $pluginUrl . '/images/new-tpl/form_video_icon.png',
                        'key' => 'video'
                    ],
                    [
                        'id' => 'goods',
                        'name' => '商品',
                        'icon' => $pluginUrl . '/images/new-tpl/form_goods_icon.png',
                    ],
                    [
                        'id' => 'store',
                        'name' => '门店',
                        'icon' => $pluginUrl . '/images/new-tpl/form_mch_icon.png',
                    ],
                    [
                        'id' => 'copyright',
                        'name' => '版权',
                        'icon' => $pluginUrl . '/images/new-tpl/form_copyright_icon.png',
                        'key' => 'copyright'
                    ],
                    [
                        'id' => 'check-in',
                        'name' => '签到',
                        'icon' => $pluginUrl . '/images/new-tpl/form_checkin_icon.png',
                        'key' => 'check_in'
                    ],
                    [
                        'id' => 'user-info',
                        'name' => '用户信息',
                        'icon' => $pluginUrl . '/images/new-tpl/form_userinfo_icon.png',
                    ],
                    [
                        'id' => 'user-order',
                        'name' => '订单入口',
                        'icon' => $pluginUrl . '/images/new-tpl/form_userorder_icon.png',
                    ],
                    [
                        'id' => 'map',
                        'name' => '地图',
                        'icon' => $pluginUrl . '/images/new-tpl/form_map_icon.png',
                    ],
                    [
                        'id' => 'mp-link',
                        'name' => '微信公众号',
                        'icon' => $pluginUrl . '/images/new-tpl/form_mplink_icon.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'form',
                        'name' => '自定义表单',
                        'icon' => $pluginUrl . '/images/new-tpl/form_form_icon.png',
                    ],
                    [
                        'id' => 'image-text',
                        'name' => '图文详情',
                        'icon' => $pluginUrl . '/images/new-tpl/form_imgtext_icon(1).png',
                    ],
                ]
            ],
            [
                'groupName' => '营销组件',
                'list' => [
                    [
                        'id' => 'coupon',
                        'name' => '优惠券',
                        'icon' => $pluginUrl . '/images/new-tpl/form_imgtext_icon.png',
                        'key' => 'coupon'
                    ],
                    [
                        'id' => 'timer',
                        'name' => '倒计时',
                        'icon' => $pluginUrl . '/images/new-tpl/form_time_icon.png',
                    ],
                    [
                        'id' => 'mch',
                        'name' => '好店推荐',
                        'icon' => $pluginUrl . '/images/new-tpl/form_shop_icon.png',
                        'key' => 'mch'
                    ],
                    [
                        'id' => 'pintuan',
                        'name' => '拼团',
                        'icon' => $pluginUrl . '/images/new-tpl/form_assemble_icon.png',
                        'key' => 'pintuan'
                    ],
                    [
                        'id' => 'booking',
                        'name' => '预约',
                        'icon' => $pluginUrl . '/images/new-tpl/form_appointment_icon.png',
                        'key' => 'booking'
                    ],
                    [
                        'id' => 'miaosha',
                        'name' => '秒杀',
                        'icon' => $pluginUrl . '/images/new-tpl/form_flashsale_icon.png',
                        'key' => 'miaosha'
                    ],
                    [
                        'id' => 'bargain',
                        'name' => '砍价',
                        'icon' => $pluginUrl . '/images/new-tpl/form_bargain_icon.png',
                        'key' => 'bargain'
                    ],
                    [
                        'id' => 'integral-mall',
                        'name' => '积分商城',
                        'icon' => $pluginUrl . '/images/new-tpl/form_integral_icon.png',
                        'key' => 'integral_mall'
                    ],
                    [
                        'id' => 'lottery',
                        'name' => '抽奖',
                        'icon' => $pluginUrl . '/images/new-tpl/form_lottery_icon.png',
                        'key' => 'lottery'
                    ],
                    [
                        'id' => 'advance',
                        'name' => '预售',
                        'icon' => $pluginUrl . '/images/new-tpl/form_advaance_icon.png',
                        'key' => 'advance'
                    ],
                    [
                        'id' => 'vip-card',
                        'name' => '超级会员卡',
                        'icon' => $pluginUrl . '/images/new-tpl/form_svip_icon.png',
                        'key' => 'vip_card'
                    ],
                    [
                        'id' => 'pick',
                        'name' => 'N元任选',
                        'icon' => $pluginUrl . '/images/new-tpl/form_pick_icon.png',
                        'key' => 'pick'
                    ],
                    [
                        'id' => 'live',
                        'name' => '微信直播',
                        'icon' => $pluginUrl . '/images/new-tpl/form_live_icon.png',
                        'key' => 'live',
                    ],
                    [
                        'id' => 'composition',
                        'name' => '套餐组合',
                        'icon' => $pluginUrl . '/images/new-tpl/form_combination_icon.png',
                        'key' => 'composition',
                    ],
                    [
                        'id' => 'gift',
                        'name' => '社交送礼',
                        'icon' => $pluginUrl . '/images/new-tpl/form_socialgifts_icon.png',
                        'key' => 'gift',
                    ],
                    [
                        'id' => 'flash-sale',
                        'name' => '限时抢购',
                        'icon' => $pluginUrl . '/images/live.png',
                        'key' => 'flash_sale'
                    ],
                    [
                        'id' => 'exchange',
                        'name' => '礼品卡',
                        'icon' => $pluginUrl . '/images/new-tpl/form_exchange_icon.png',
                        'key' => 'exchange',
                    ],
                ]
            ],
            [
                'groupName' => '其他组件',
                'list' => [
                    [
                        'id' => 'empty',
                        'name' => '空白块',
                        'icon' => $pluginUrl . '/images/new-tpl/form_empty_icon.png',
                    ],
                    [
                        'id' => 'ad',
                        'name' => '流量主广告',
                        'icon' => $pluginUrl . '/images/new-tpl/form_ad_icon.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'modal',
                        'name' => '弹窗广告',
                        'icon' => $pluginUrl . '/images/new-tpl/form_Popupads_icon.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'quick-nav',
                        'name' => '快捷导航',
                        'icon' => $pluginUrl . '/images/new-tpl/form_float_icon.png',
                        'single' => true,
                    ],
                    [
                        'id' => 'module',
                        'name' => '自定义模块',
                        'icon' => $pluginUrl . '/images/new-tpl/form_custom_icon.png',
                    ]
                ]
            ]
        ];
        $permission = \Yii::$app->role->getAccountPermission();
        if ($permission !== true) {
            $newList = [];
            foreach ($result as $item) {
                if (isset($item['list'])) {
                    $list = [];
                    foreach ($item['list'] as $value) {
                        if (!$permission || (isset($value['key']) && !in_array($value['key'], $permission))) {
                            continue;
                        }
                        $list[] = $value;
                    }
                    if (count($list) > 0) {
                        $newItem = $item;
                        $newItem['list'] = $list;
                        $newList[] = $newItem;
                    }
                } else {
                    $newItem = $item;
                    $newList[] = $newItem;
                }
            }
        } else {
            $newList = $result;
        }
        return $newList;
    }
}
