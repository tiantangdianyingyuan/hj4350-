<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 15:44
 */

namespace app\forms\api\coupon;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\coupon\UserCouponCenter;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CouponDetailForm extends Model
{
    public $coupon_id;
    public $user_id;

    public function rules()
    {
        return [
            ['coupon_id', 'required'],
            [['coupon_id', 'user_id'], 'integer'],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => $this->getDetail()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCoupon(['coupon_id' => $this->coupon_id], false);
            $common->user = \Yii::$app->user->identity;
            $coupon = $common->getDetail();
            if ($coupon->is_delete == 1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券不存在'
                ];
            }
            if ($coupon->expire_type == 2 && $coupon->end_time < mysql_timestamp()) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券已过期'
                ];
            }
            $count = $common->checkAllReceive($coupon->id);
            if ($count >= $coupon->can_receive_count && $coupon->can_receive_count != -1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券领取次数已达上限'
                ];
            } else {
                $class = new UserCouponCenter($coupon, $common->user);
                if ($common->receive($coupon, $class, '领券中心领取')) {
                    if ($coupon->can_receive_count == -1) {
                        $rest = -1;
                    } elseif ($coupon->can_receive_count <= $count + 1) {
                        $rest = 0;
                    } else {
                        $rest = $coupon->can_receive_count - $count - 1;
                    }
                    $coupon = ArrayHelper::toArray($coupon);
                    $coupon['rest'] = $rest;
                    $coupon['type'] = (string)$coupon['type'];
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '领取成功',
                        'data' => $coupon
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '优惠券已领完'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        $common = new CommonCoupon(['coupon_id' => $this->coupon_id], true);
        $common->user = \Yii::$app->user->identity;
        $res = $common->getDetail();
        if ($res['is_delete'] == 1) {
            throw new \Exception('优惠券不存在');
        }
        if (isset($res['couponCat'])) {
            unset($res['couponCat']);
        }
        if (isset($res['couponGood'])) {
            unset($res['couponGood']);
        }
        if ($res['appoint_type'] == 1) {
            $res['goods'] = [];
        }
        if ($res['appoint_type'] == 2) {
            $res['cat'] = [];
        }
        if ($res['appoint_type'] == 3) {
            $res['goods'] = [];
            $res['cat'] = [];
        }
        $res['page_url'] = '/pages/goods/list?coupon_id=' . $res['id'];
        if ($res['appoint_type'] == 4) {
            $res['page_url'] = '/plugins/scan_code/index/index';
        }
        if ($res['appoint_type'] == 5) {
            $res['page_url'] = '/plugins/exchange/list/list';
        }
        $res['begin_time'] = new_date($res['begin_time']);
        $res['end_time'] = new_date($res['end_time']);
        $count = !\Yii::$app->user->isGuest ? $common->checkAllReceive($res['id']) : '0';
        $res['user_count'] = $res['receive_count'] = $count;
        $res['status'] = 0;
        $res['discount'] = price_format($res['discount'], 'string', 1);
        if ($count > 0) {
            $userCouponTable = $common->getTableTemp(\Yii::$app->mall->id, \Yii::$app->user->id);
            $userCouponId = (new Query())->from(['a' => $userCouponTable])->select('a.user_coupon_id');
            /* @var UserCoupon $userCoupon */
            $userCoupon = UserCoupon::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'coupon_id' => $res['id']])
                ->andWhere(['id' => $userCouponId, 'is_use' => 0])
                ->andWhere(['>', 'end_time', mysql_timestamp()])->one();
            if ($userCoupon) {
                $res['status'] = 1;
                $res['begin_time'] = new_date($userCoupon->start_time);
                $res['end_time'] = new_date($userCoupon->end_time);
                $res['expire_type'] = 2;
            } elseif ($count >= $res['can_receive_count'] && $res['can_receive_count'] != -1) {
                $res['status'] = 4;
            }
        }
        if (!$res['app_share_title']) {
            $res['app_share_title'] = '我抢到优惠券啦，召唤小伙伴一起啊来抢，名额有限';
        }
        return $res;
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = new CommonCoupon(['coupon_id' => $this->coupon_id], false);
            $common->user = \Yii::$app->user->identity;
            $coupon = $common->getDetail();
            /* @var User $user */
            // 发送人信息
            $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app';
            $user = \Yii::$app->user->identity;
            $qrcodeFactory = new CommonQrCode();
            $qrcode = $qrcodeFactory->getQrCode(['user_id' => \Yii::$app->user->id, 'coupon_id' => $this->coupon_id], 430, 'pages/coupon/give/give');
            $res = [
                'id' => $coupon->id,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'discount' => price_format($coupon->discount, 'string', 1),
                'discount_limit' => round($coupon->discount_limit, 2),
                'min_price' => round($coupon->min_price, 2),
                'sub_price' => round($coupon->sub_price, 2),
                'expire_type' => $coupon->expire_type,
                'expire_day' => $coupon->expire_day,
                'begin_time' => new_date($coupon->begin_time),
                'end_time' => new_date($coupon->end_time),
                'appoint_type' => $coupon->appointTypeText,
                'avatar' => $user->userInfo->avatar,
                'nickname' => $user->nickname,
                'qrcode' => $qrcode['file_path'],
                'poster_bg' => $url . '/coupon/poster_bg.png'
            ];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $res
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function give()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app';
        try {
            $res = $this->getDetail();
            $res['nickname'] = \Yii::$app->mall->name;
            $res['avatar'] = \Yii::$app->mall->getMallSettingOne('mall_logo_pic');
            if ($this->user_id) {
                /* @var User $user */
                // 发送人信息
                $user = User::find()->with('userInfo')->where(['id' => $this->user_id])->one();
                $res['avatar'] = $user->userInfo->avatar;
                $res['nickname'] = $user->nickname;
            }
            $res['img_finish_receiving'] = $url . '/card/img_finish_receiving.png';
            $res['coupon_bg'] = $url . '/coupon/coupon_bg.png';
            $res['receive_coupon_bg'] = $url . '/coupon/receive_coupon_bg.png';
            $res['img_receive'] = $url . '/card/receive.png';
            $res['img_use'] = $url . '/card/use.png';
            $res['img_share'] = $url . '/card/share.png';
            $res['img_coupon'] = $url . '/poster/img_coupon.png';
            $res['discount'] = round($res['discount'], 2);
            $res['discount_limit'] = round($res['discount_limit'], 2);
            $res['min_price'] = round($res['min_price'], 2);
            $res['sub_price'] = round($res['sub_price'], 2);
            switch ($res['appoint_type']) {
                case 1:
                    $text = '指定商品';
                    break;
                case 2:
                    $text = '指定商品类别';
                    break;
                case 3:
                    $text = '全场通用';
                    break;
                case 4:
                    $text = '仅限当面付';
                    break;
                default:
                    $text = '';
            }
            $res['appoint_type_text'] = $text;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $res
            ];
        } catch (\Exception $exception) {
            $res['img_finish_receiving'] = $url . '/card/img_finish_receiving.png';
            $res['coupon_bg'] = $url . '/coupon/coupon_bg.png';
            $res['img_coupon'] = $url . '/poster/img_coupon.png';
            $res['id'] = $this->coupon_id;
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'data' => $res
            ];
        }
    }
}
