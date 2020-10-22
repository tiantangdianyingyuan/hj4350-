<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\MallSetting;
use app\models\Option;
use function GuzzleHttp\Psr7\str;

class StoreImporting extends BaseImporting
{
    public function import()
    {
        try {
            // 支付方式
            $payment = \Yii::$app->serializer->decode($this->v3Data['payment']);
            $newPayment = [];
            foreach ($payment as $key => $item) {
                if ($item == 1) {
                    if ($key == 'wechat') {
                        $newPayment[] = 'online_pay';
                    } else {
                        $newPayment[] = $key;
                    }
                }
            }
            // 面议联系方式
            $goodsNegotiable = \Yii::$app->serializer->decode($this->v3Data['good_negotiable']);
            $newGoodsNegotiable = [];
            foreach ($goodsNegotiable as $key => $item) {
                if ($item == 1) {
                    if ($key == 'tel') {
                        $newGoodsNegotiable[] = 'contact_tel';
                    } elseif ($key == 'web_contact') {
                        $newGoodsNegotiable[] = 'contact_web';
                    } else {
                        $newGoodsNegotiable[] = $key;
                    }
                }
            }

            $latlong = explode(',', $this->v3Data['quick_map']['lal']);

            $wxapp = \Yii::$app->serializer->decode($this->v3Data['wxapp']);
            $quickNavigation = \Yii::$app->serializer->decode($this->v3Data['quick_navigation']);
            $data = [
                'share_title' => '', //分享标题
                'share_pic' => '', //分享图片
                'contact_tel' => $this->v3Data['contact_tel'],// 联系电话
                'over_time' => (string)$this->v3Data['over_day'], // 未支付订单超时时间（分钟）
                'delivery_time' => (string)$this->v3Data['delivery_time'], // 收货时间（天）
                'after_sale_time' => (string)$this->v3Data['after_sale_time'], // 售后时间（天）
                /**
                 * 支付方式
                 * online_pay 线上支付
                 * balance 余额支付
                 * huodao 货到付款
                 */
                'payment_type' => $newPayment,
                'send_type' => (string)($this->v3Data['send_type'] + 1),// 发货方式 1.快递或自提 2.仅快递 3.仅自提
                'kdniao_mch_id' => $this->v3Data['kdniao_mch_id'],// 快递鸟商户ID
                'kdniao_api_key' => $this->v3Data['kdniao_api_key'],// 快递鸟API KEY
                'member_integral' => $this->v3Data['integral'],// 会员积分抵扣比例
                'member_integral_rule' => $this->v3Data['integration'],// 会员积分使用规格
                /**
                 * 商品面议联系方式
                 * contact 客服
                 * contact_tel 联系电话
                 * contact_web 外链客服
                 */
                'good_negotiable' => $newGoodsNegotiable,
                'mobile_verify' => $this->v3Data['mobile_verify'], // 商城手机号是否验证 0.关闭 1.开启
                'is_small_app' => isset($wxapp['status']) ? $wxapp['status'] : 0,//跳转小程序开关
                'small_app_id' => $wxapp['appid'],// 跳转小程序APP ID
                'small_app_url' => $wxapp['path'],// 跳转小程序APP URL
                'small_app_pic' => $wxapp['pic_url'],// 跳转小程序APP 图标
                'is_customer_services' => (string)$this->v3Data['show_customer_service'], // 是否开启在线客服 0.关闭 1.开启
                'customer_services_pic' => $this->v3Data['service'],// 在线客服图标
                'is_dial' => (string)$this->v3Data['dial'],// 是否开启一键拨号 0.关闭 1.开启
                'dial_pic' => $this->v3Data['dial_pic'],// 一键拨号图标
                'is_web_service' => $this->v3Data['web_service_status'],// 客服外链开关
                'web_service_url' => $this->v3Data['web_service_url'], // 客服外链
                'web_service_pic' => $this->v3Data['web_service'], // 客服外链图标
                /**
                 * 快捷导航样式
                 * 1.样式1（点击收起）
                 * 2.样式2（全部展示）
                 */
                'is_quick_navigation' => $quickNavigation['type'] == 0 ? '0' : '1',
                'quick_navigation_style' => $quickNavigation['type'] > 0 ? (string)$quickNavigation['type'] : '1',
                'quick_navigation_opened_pic' => $quickNavigation['home_img'],// 快捷导航展开图标
                'quick_navigation_closed_pic' => $quickNavigation['home_img'],// 快捷导航收起图标
                /**
                 * 分类样式
                 * 1.大图模式（不显示侧栏）
                 * 2.大图模式（显示侧栏）
                 * 3.小图模式（不显示侧栏）
                 * 4.小图模式（显示侧栏）
                 * 5.商品列表模式
                 */
                'is_member_price' => (string)$this->v3Data['is_member_price'],// 会员价显示开关 0.关闭 1.开启
                'is_share_price' => (string)$this->v3Data['is_share_price'],// 分销价显示开关 0.关闭 1.开启
                'is_purchase_frame' => (string)$this->v3Data['purchase_frame'],// 首页购买记录框 0.关闭 1.开启
                'is_comment' => (string)$this->v3Data['is_comment'], // 商城评价开关 0.关闭 1.开启
                'is_sales' => (string)$this->v3Data['is_sales'],// 商城商品销量开关 0.关闭 1.开启
                // 'is_recommend' => '1',// TODO 即将废弃 推荐商品状态 0.关闭 1.开启
                'is_mobile_auth' => (string)$this->v3Data['phone_auth'],// 首页授权手机号 0.关闭 1.开启
                'is_official_account' => (string)$this->v3Data['is_official_account'], // 关联公众号组件 0.关闭 1.开启
                'is_manual_mobile_auth' => '1', // 手动授权手机号 0.关闭 1.开启
                'is_quick_map' => $this->v3Data['quick_map']['status'], // 一键导航是否开启 0.关闭 1.开启
                'quick_map_pic' => $this->v3Data['quick_map']['icon'], // 一键导航图标
                'quick_map_address' => $this->v3Data['quick_map']['address'], // 商家地址
                'latitude' => count($latlong) > 2 ? (string)$latlong[0] : '0', //纬度
                'longitude' => count($latlong) > 2 ? (string)$latlong[1] : '0', // 经度
                'is_quick_home' => '0',//返回首页开关
                'quick_home_pic' => '',// 返回首页图标
                // 'nav_row_num' => '4',//导航图标每行显示个数

                'logo' => '' //手机端商城管理店铺设置页面，logo可自定义图片上传
            ];

            foreach ($data as $k => $item) {
                $arr = ['name', 'latitude_longitude'];
                if (in_array($k, $arr)) {
                    continue;
                }
                if (in_array($k, ['good_negotiable', 'payment_type'])) {
                    $newItem = json_encode($item, true);
                } else {
                    $newItem = $item;
                }
                if ($k == 'web_service_url') {
                    $newItem = urlencode($item);
                }

                $mallSetting = MallSetting::findOne(['key' => $k, 'mall_id' => \Yii::$app->mall->id]);
                if ($mallSetting) {
                    $mallSetting->value = (string)$newItem;
                } else {
                    $mallSetting = new MallSetting();
                    $mallSetting->key = $k;
                    $mallSetting->value = (string)$newItem;
                    $mallSetting->mall_id = $this->mall->id;
                }
                $res = $mallSetting->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mallSetting));
                }
            }

            $this->mall->name = $this->v3Data['name'];
            if (!$this->mall->save()) {
                throw new \Exception('商城数据保存异常');
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}