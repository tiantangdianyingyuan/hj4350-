<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 15:54
 */

namespace app\forms\common\coupon;

use app\models\Coupon;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserCouponCenter;
use app\models\UserCouponMember;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property User $user
 * @property Mall $mall
 */
class CommonCoupon extends Model
{
    public $mall;
    public $user;

    public $isArray;
    public $coupon_id;

    public function __construct(array $config = [], $isArray = true)
    {
        parent::__construct($config);
        $this->mall = \Yii::$app->mall;
        $this->isArray = $isArray;
    }

    /**
     * @return array|Coupon|null
     * @throws Exception
     */
    public function getDetail()
    {
        $res = Coupon::find()->where([
            'mall_id' => $this->mall->id, 'id' => $this->coupon_id
        ])->with(['cat', 'goods'])->select(['*'])
            ->asArray($this->isArray)->one();
        if (!$res) {
            throw new Exception('优惠券不存在');
        }

        return $res;
    }

    public function getAutoDetail()
    {
        $res = Coupon::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->coupon_id
        ])->with(['cat', 'goods'])->select(['*'])
            ->asArray($this->isArray)->one();
        if (!$res) {
            throw new \Exception('优惠券不存在');
        }
        return $res;
    }

    /**
     * @param integer $couponId
     * @return int|string
     * 检测用户领券中心领取指定优惠券数量
     */
    public function checkReceive($couponId)
    {
        if (!$this->user) {
            return 0;
        }
        $count = UserCouponCenter::find()->alias('ucc')->where([
            'ucc.is_delete' => 0, 'ucc.mall_id' => $this->mall->id
        ])->leftJoin(['uc' => UserCoupon::tableName()], 'uc.id = ucc.user_coupon_id')->andWhere([
            'uc.mall_id' => $this->mall->id, 'uc.is_delete' => 0, 'uc.coupon_id' => $couponId
        ])->andWhere(['ucc.user_id' => $this->user->id, 'uc.user_id' => $this->user->id])->count(1);

        return $count;
    }

    /**
     * @param $coupon Coupon
     * @param $class UserCouponData
     * @param $content String
     * @param int $send_num
     * @return bool
     * @throws Exception
     */
    public function receive(Coupon $coupon, UserCouponData $class, $content, $send_num = 1)
    {
        $t = \Yii::$app->db->beginTransaction();

        for ($i = 0; $i < $send_num; $i++) {
            $userCoupon = new UserCoupon();
            $userCoupon->mall_id = $this->mall->id;
            $userCoupon->user_id = $this->user->id;
            $userCoupon->coupon_id = $coupon->id;
            $userCoupon->coupon_min_price = $coupon->min_price;
            $userCoupon->sub_price = $coupon->sub_price;
            $userCoupon->discount = $coupon->discount;
            $userCoupon->discount_limit = $coupon->discount_limit;
            $userCoupon->type = $coupon->type;
            $userCoupon->is_use = 0;
            $userCoupon->receive_type = $content;
            if ($coupon->expire_type == 1) {
                $time = time();
                $userCoupon->start_time = date('Y-m-d H:i:s', $time);
                $userCoupon->end_time = date('Y-m-d H:i:s', $time + $coupon->expire_day * 86400);
            } else {
                $userCoupon->start_time = $coupon->begin_time;
                $userCoupon->end_time = $coupon->end_time;
            }
            $cat = $coupon->cat;
            $goods = $coupon->goods;
            $arr = ArrayHelper::toArray($coupon);
            $arr['cat'] = ArrayHelper::toArray($cat);
            $arr['goods'] = ArrayHelper::toArray($goods);
            $userCoupon->coupon_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
            if ($userCoupon->save()) {
                $class->userCoupon = $userCoupon;
                try {
                    if (!$class->save()) {
                        $t->rollBack();
                        return false;
                    }
                } catch (\Exception $exception) {
                    \Yii::warning('优惠券领取问题');
                    $t->rollBack();
                    return false;
                }
            } else {
                $t->rollBack();
                throw new Exception(isset($userCoupon->errors) ? current($userCoupon->errors)[0] : '数据异常！');
            }
        }
        $t->commit();
        return true;
    }

    /**
     * @param $couponId int
     * @return int|string
     * 获取指定优惠券（指定|所有）用户已领取数量
     */
    public function getCount($couponId)
    {
        return UserCoupon::find()->where(['coupon_id' => $couponId, 'is_delete' => 0])->keyword($this->user, ['user_id' => $this->user ? $this->user->id : 0])->count();
    }

    /**
     * @param $userCouponId
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定用户指定用户优惠券ID的用户优惠券
     */
    public function getUserCoupon($userCouponId)
    {
        $userCoupon = UserCoupon::find()->where(['id' => $userCouponId, 'user_id' => $this->user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0])
            ->with('coupon')->one();
        return $userCoupon;
    }

    /**
     * @param integer $couponId
     * @return int|string
     * 检测用户会员中心领取指定优惠券数量
     */
    public function checkMemberReceive($couponId)
    {
        if (!$this->user) {
            return 0;
        }
        $userCouponMember = UserCouponMember::find()
            ->where(['mall_id' => $this->mall->id, 'user_id' => $this->user->id, 'is_delete' => 0])
            ->select('user_coupon_id');
        $count = UserCoupon::find()
            ->where([
                'mall_id' => $this->mall->id, 'user_id' => $this->user->id, 'is_delete' => 0, 'coupon_id' => $couponId
            ])->andWhere(['id' => $userCouponMember])->count(1);

        return $count;
    }

    /**
     * @param integer $couponId
     * @return int|string
     * 检测用户会员中心和领券中心领取指定优惠券数量
     */
    public function checkAllReceive($couponId)
    {
        if (!$this->user) {
            return 0;
        }
        $userCouponTable = $this->getTableTemp($this->mall->id, $this->user->id);
        $userCouponId = (new Query())->from(['a' => $userCouponTable])->select('a.user_coupon_id');
        $count = UserCoupon::find()
            ->where([
                'mall_id' => $this->mall->id, 'user_id' => $this->user->id, 'is_delete' => 0, 'coupon_id' => $couponId
            ])->andWhere(['id' => $userCouponId])->count(1);

        return price_format($count, 'string', 0);
    }

    /**
     * @param integer $mallId
     * @param integer $userId
     * @return int|string
     * 获取领券中心和会员专享的
     */
    public function getTableTemp($mallId, $userId)
    {
        $userCouponTable = UserCouponMember::find()->where([
            'is_delete' => 0, 'mall_id' => $mallId, 'user_id' => $userId
        ])->select('user_coupon_id')->union(UserCouponCenter::find()->where([
            'is_delete' => 0, 'mall_id' => $mallId, 'user_id' => $userId
        ])->select('user_coupon_id'));
        if (class_exists('\app\plugins\diy\models\DiyCouponLog')) {
            $userCouponTable->union(\app\plugins\diy\models\DiyCouponLog::find()->where([
                'is_delete' => 0, 'mall_id' => $mallId, 'user_id' => $userId
            ])->select('user_coupon_id'));
        }
        return $userCouponTable;
    }
}
