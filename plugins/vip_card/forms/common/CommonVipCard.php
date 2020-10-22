<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/18
 * Time: 14:13
 */

namespace app\plugins\vip_card\forms\common;

use app\forms\common\card\CommonCard;
use app\forms\common\card\CommonSend;
use app\jobs\UserCardCreatedJob;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\Model;
use app\models\UserCard;
use app\models\UserCoupon;
use app\plugins\vip_card\models\VipCardDetail;
use yii\helpers\ArrayHelper;

class CommonVipCard
{
    /**
     * 获取子卡详情
     * @param $id
     * @return array|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public static function getCardDetail($id)
    {
        $data = VipCardDetail::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $id])
            ->with('vipCards')
            ->with('vipCoupons')
            ->with('main')
            ->asArray()
            ->one();
        if (isset($data['content'])) {
            $data['content'] = '';
        }
        if (isset($data['vipCards'])) {
            foreach ($data['vipCards'] as $key => $item) {
                $data['vipCards'][$key]['description'] = '';
            }
        }
        if (isset($data['vipCoupons'])) {
            foreach ($data['vipCoupons'] as $key => $item) {
                $data['vipCoupons'][$key]['desc'] = '';
            }
        }
        if (!$data) {
            throw new \Exception('该会员卡不存在');
        }
        return $data;
    }

    /**
     * 超级会员卡赠送优惠券
     * @param array $dataArr 子卡信息
     * @param int $mall_id 商城id
     * @param int $user_id 用户id
     * @throws \yii\db\Exception
     */
    public static function sendCoupon($dataArr, $mall_id, $user_id)
    {
        foreach ($dataArr['coupons'] as $coupon) {
            try {
                $res = (new Coupon())->updateCount($coupon['send_num'], 'sub', $coupon['coupon_id']);
            } catch (\Exception $exception) {
                continue;
            }
            /** @var Coupon $newCoupon */
            $newCoupon = Coupon::find()->where(['id' => $coupon['coupon_id'], 'is_delete' => 0, 'mall_id' => $mall_id])
                ->with('goods', 'cat')->one();
            if (!$newCoupon) {
                continue;
            }
            for ($i = 1; $i <= $coupon['send_num']; $i++) {
                $userCoupon = new UserCoupon();
                $userCoupon->mall_id = $mall_id;
                $userCoupon->user_id = $user_id;
                $userCoupon->coupon_id = $newCoupon->id;
                $userCoupon->coupon_min_price = $newCoupon->min_price;
                $userCoupon->sub_price = $newCoupon->sub_price;
                $userCoupon->discount = $newCoupon->discount;
                $userCoupon->type = $newCoupon->type;
                $userCoupon->is_use = 0;
                $userCoupon->receive_type = ($dataArr['main']['name'] ?? '超级会员卡') . '赠送优惠券';
                if ($newCoupon->expire_type == 1) {
                    $time = time();
                    $userCoupon->start_time = date('Y-m-d H:i:s', $time);
                    $userCoupon->end_time = date('Y-m-d H:i:s', $time + $newCoupon->expire_day * 86400);
                } else {
                    $userCoupon->start_time = $newCoupon->begin_time;
                    $userCoupon->end_time = $newCoupon->end_time;
                }
                $cat = $newCoupon->cat;
                $goods = $newCoupon->goods;
                $arr = ArrayHelper::toArray($newCoupon);
                $arr['cat'] = ArrayHelper::toArray($cat);
                $arr['goods'] = ArrayHelper::toArray($goods);
                $userCoupon->coupon_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
                if (!$userCoupon->save()) {
                    throw new \Exception((new Model())->getErrorMsg($userCoupon));
                }

                // 记录
                $couponData = ArrayHelper::toArray($newCoupon);
                if ($couponData['expire_type'] == 1) {
                    $couponData['desc'] = "本券有效期为发放后{$couponData['expire_day']}天内";
                } else {
                    $couponData['desc'] = "本券有效期" . $couponData['begin_time'] . "至" . $couponData['end_time'];
                }
                $couponData['content'] = ($dataArr['main']['name'] ?? '超级会员卡') . '赠送优惠券';

                $couponList[] = $couponData;
            }
        }
    }

    /**
     * 超级会员卡赠送卡券
     * @param array $dataArr 子卡信息
     * @param int $mall_id 商城id
     * @param int $user_id 用户id
     * @param int $order_id
     * @param int $order_detail_id
     * @throws \yii\db\Exception
     */
    public static function sendCard($dataArr, $mall_id, $user_id, $order_id = 0, $order_detail_id = 0)
    {
        $commonCard = new CommonCard();
        foreach ($dataArr['cards'] as $card) {
            $commonCard->user_id = $user_id;

            /** @var GoodsCards $cards */
            $cards = GoodsCards::findOne(
                ['id' => $card['card_id'], 'is_delete' => 0, 'mall_id' => $mall_id]
            );
            if (!$cards) {
                continue;
            }

            $count = 0;
            while ($count < $card['send_num']) {
                $commonCard->receive($cards, $order_id, $order_detail_id);
                $count++;
            }
        }
    }
}
