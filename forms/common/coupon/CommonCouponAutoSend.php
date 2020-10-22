<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/13
 * Time: 17:26
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\coupon;


use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserCouponAuto;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonCouponAutoSend extends Model
{
    public $event;// 自动发放的事件具体参数内容参考 app/models/CouponAutoSend的触发事件
    public $user;
    public $mall;

    /**
     * @return array
     * @throws Exception
     */
    public function send()
    {
        $auto = CouponAutoSend::find()->with(['coupon.cat', 'coupon.goodsWarehouse'])
            ->where(['mall_id' => $this->mall->id, 'event' => $this->event, 'is_delete' => 0])
            ->all();
        if (!$auto) {
            throw new Exception('没有为该事件添加优惠券发放');
        }
        $couponList = [];

        /* @var CouponAutoSend[] $auto*/
        foreach ($auto as $item) {
            $userList = $item->user_list ? json_decode($item->user_list, true) : [];
            if ($item->type == 1 && !in_array($this->user->id, $userList)) {
                continue;
            }
            $count = UserCoupon::find()->alias('uc')->where([
                'uc.coupon_id' => $item->coupon_id, 'uc.is_delete' => 0, 'uc.mall_id' => $this->mall->id,
                'user_id' => $this->user->id
            ])->innerJoin([
                'uca' => UserCouponAuto::find()->where(['auto_coupon_id' => $item->id])
            ], 'uca.user_coupon_id=uc.id')
                ->count();

            if ($item->send_count != 0 && $item->send_count <= $count) {
                continue;
            }

            /* @var $coupon Coupon*/
            $coupon = $item->coupon;
            if ($coupon->is_delete !== 0) {
                \Yii::warning('优惠券（id：'.$coupon->id.'）已被删除');
                continue;
            }

            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = $this->mall;
            $commonCoupon->user = $this->user;

            $commonUserCouponAuto = new CommonUserCouponAuto($coupon, $item);
            if ($commonCoupon->receive($coupon, $commonUserCouponAuto, '自动发放优惠券')) {
                $coupon = ArrayHelper::toArray($coupon);
                if ($coupon['expire_type'] == 1) {
                    $coupon['desc'] = "本券有效期为发放后{$coupon['expire_day']}天内";
                } else {
                    $coupon['desc'] = "本券有效期" . $coupon['begin_time'] . "至" . $coupon['end_time'];
                }
                $coupon['content'] = '限';
                $coupon['page_url'] = '/pages/goods/list?coupon_id=' . $item->coupon->id;
                if ($item->coupon->appoint_type == 1) {
                    $catList = [];
                    foreach ($item->coupon->cat as $cat) {
                        $catList[] = $cat->name;
                    }
                    $coupon['content'] .= implode('、', $catList) . '使用';
                } elseif ($item->coupon->appoint_type == 2) {
                    $goodsWarehouseList = [];
                    foreach ($item->coupon->goodsWarehouse as $goodsWarehouse) {
                        $goodsWarehouseList[] = $goodsWarehouse->name;
                        $coupon['content'] .= $goodsWarehouse->name . '、';
                    }
                    $coupon['content'] .= implode('、', $goodsWarehouseList) . '使用';
                } elseif ($item->coupon->appoint_type == 4) {
                    $coupon['content'] = '仅限当面付使用';
                    $coupon['page_url'] = '/plugins/scan_code/index/index';
                } else {
                    $coupon['content'] = '全场通用';
                }
                /** 发放优惠券需要 */
                $coupon['share_type'] = 4;

                $couponList[] = $coupon;
            }
        }

        return $couponList;
    }
}
