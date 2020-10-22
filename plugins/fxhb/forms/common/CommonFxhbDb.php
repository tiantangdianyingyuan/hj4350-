<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 11:21
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\common;


use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\GoodsCats;
use app\models\Mall;
use app\models\Model;
use app\models\UserCoupon;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbActivityGoodsRelation;
use app\plugins\fxhb\models\FxhbUserActivity;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class CommonFxhbDb extends Model
{
    public $mall;

    public static function getCommon($mall)
    {
        $form = new self();
        $form->mall = $mall;
        return $form;
    }

    /**
     * @param string|array $params
     * @param array $where
     * @return array|\yii\db\ActiveRecord|null|FxhbActivity
     * 活动配置
     */
    public function getActivity($params = '*', $where = [])
    {
        $query = FxhbActivity::find()->where(['mall_id' => $this->mall->id, 'status' => 1, 'is_delete' => 0]);
        !empty($where) && $query->andWhere($where);
        return $query->select($params)->one();
    }

    /**
     * @param $userId
     * @param $activityId
     * @return FxhbUserActivity|null
     * 通过用户id和活动id获取某个用户正在进行中的活动
     */
    public function getUserActivityByUserId($userId, $activityId)
    {
        $userActivity = FxhbUserActivity::findOne([
            'user_id' => $userId, 'fxhb_activity_id' => $activityId, 'mall_id' => $this->mall->id,
            'status' => 0, 'is_delete' => 0
        ]);
        return $userActivity;
    }

    /**
     * @param $userId
     * @param $activityId
     * @return int|string
     * 某个用户某场活动发起次数
     */
    public function getSponsorNum($userId, $activityId)
    {
        $userActivity = FxhbUserActivity::find()->where([
            'mall_id' => $this->mall->id,
            'user_id' => $userId,
            'fxhb_activity_id' => $activityId,
            'is_delete' => 0,
            'parent_id' => 0
        ])->count('id');

        return $userActivity ? $userActivity : 0;
    }

    /**
     * @param $userId
     * @param $activityId
     * @return int|string
     * 某个用户某场活动帮拆红包次数
     */
    public function getHelpNum($userId, $activityId)
    {
        $userActivity = FxhbUserActivity::find()->where([
            'mall_id' => $this->mall->id,
            'user_id' => $userId,
            'fxhb_activity_id' => $activityId,
            'is_delete' => 0,
        ])->andWhere(['!=', 'parent_id', 0])->count('id');

        return $userActivity ? $userActivity : 0;
    }

    /**
     * @param array $attributes
     * @return FxhbUserActivity
     * @throws \Exception
     * 用户发起拆红包或者用户帮拆红包
     */
    public function joinActivity($attributes)
    {
        $form = new FxhbUserActivity();
        $form->attributes = $attributes;
        $form->is_delete = 0;
        $form->user_coupon_id = 0;
        if (!$form->save()) {
            throw new \Exception($this->getErrorMsg($form));
        }
        return $form;
    }

    /**
     * @param $userActivityId
     * @return array|\yii\db\ActiveRecord[]|FxhbUserActivity[]
     * 获取某个用户红包活动的参与情况
     */
    public function getUserActivityAllById($userActivityId)
    {
        $userActivityAll = FxhbUserActivity::find()
            ->where([
                'mall_id' => $this->mall->id, 'is_delete' => 0
            ])->andWhere([
                'or',
                ['id' => $userActivityId],
                ['parent_id' => $userActivityId]
            ])->all();
        return $userActivityAll;
    }

    /**
     * @param FxhbUserActivity $userActivity
     * @return false|float|int
     * 获取拆红包剩余时间
     */
    public function getResetTime($userActivity)
    {
        if (!$userActivity) {
            return 0;
        }
        $activity = \Yii::$app->serializer->decode($userActivity->data);
        $resetTime = $activity->open_effective_time * 3600 - (time() - strtotime($userActivity->created_at));
        return ($resetTime <= 0 ? 0 : $resetTime);
    }

    /**
     * @param FxhbUserActivity $userActivity
     * @param int $status 1--成功 2--失败
     * @param int $userCouponId
     * @return bool
     * @throws \Exception
     * 设置用户参与拆红包的状态及红包ID
     */
    public function setUserActivityStatus($userActivity, $status, $userCouponId = 0)
    {
        $userActivity->status = $status;
        $userActivity->user_coupon_id = $userCouponId;
        if (!$userActivity->save()) {
            throw new \Exception($this->getErrorMsg($userActivity));
        }
        return true;
    }

    /**
     * @param $attributes
     * @return UserCoupon
     * @throws \Exception
     * 拆红包成功后，添加红包到用户优惠券
     */
    public function addUserCoupon($attributes)
    {
        $coupon = new Coupon();
        $coupon->attributes = $attributes;
        if (!$coupon->save()) {
            throw new \Exception($this->getErrorMsg($coupon));
        }

        $catList = [];
        if ($coupon->appoint_type == 1 && isset($attributes['cats']) && is_array($attributes['cats'])) {
            foreach ($attributes['cats'] as $item) {
                /* @var GoodsCats $item */
                $cat = new CouponCatRelation();
                $cat->coupon_id = $coupon->id;
                $cat->cat_id = $item->id;
                if (!$cat->save()) {
                    throw new \Exception($this->getErrorMsg($cat));
                }
                $catList[] = $cat;
            }
        }
        $goodsList = [];
        if ($coupon->appoint_type == 2 && isset($attributes['goods']) && is_array($attributes['goods'])) {
            foreach ($attributes['goods'] as $item) {
                /* @var FxhbActivityGoodsRelation $item */
                $goods = new CouponGoodsRelation();
                $goods->coupon_id = $coupon->id;
                $goods->goods_warehouse_id = $item->goods_warehouse_id;
                if (!$goods->save()) {
                    throw new \Exception($this->getErrorMsg($goods));
                }
                $goodsList[] = $goods;
            }
        }

        $time = time();
        $userCoupon = new UserCoupon();
        $userCoupon->mall_id = $attributes['mall_id'];
        $userCoupon->user_id = $attributes['user_id'];
        $userCoupon->coupon_id = $coupon->id;
        $userCoupon->sub_price = $coupon->sub_price;
        $userCoupon->coupon_min_price = $coupon->min_price;
        $userCoupon->type = $coupon->type;
        $userCoupon->receive_type = '裂变红包领取';
        $userCoupon->start_time = date('Y-m-d H:i:s', $time);
        $userCoupon->end_time = date('Y-m-d H:i:s', $time + $coupon->expire_day * 86400);

        $arr = ArrayHelper::toArray($coupon);
        $arr['cat'] = ArrayHelper::toArray($catList);
        $arr['goods'] = ArrayHelper::toArray($goodsList);
        $userCoupon->coupon_data = \Yii::$app->serializer->encode($arr);
        if (!$userCoupon->save()) {
            throw new \Exception($this->getErrorMsg($userCoupon));
        }
        return $userCoupon;
    }

    /**
     * @param FxhbActivity $activity
     * @param $type
     * @return bool
     * @throws \Exception
     * 改变指定活动可拆红包数量
     */
    public function updateSponsorCount($activity, $type)
    {
        if ($activity->sponsor_count < 0) {
            return true;
        }
        if ($type === 'sub') {
            if ($activity->sponsor_count == 0) {
                throw new \Exception('本次活动可拆红包数量为0');
            }
            $activity->sponsor_count--;
        } elseif ($type === 'add') {
            $activity->sponsor_count++;
        } else {
            throw new \Exception('错误的参数$type');
        }
        if (!$activity->save()) {
            throw new \Exception($this->getErrorMsg($activity));
        }
        return true;
    }

    /**
     * @param $parentId
     * @param $userId
     * @return array|\yii\db\ActiveRecord|null|FxhbUserActivity
     * 获取指定用户参与指定活动的记录
     */
    public function getUserActivityByParentId($parentId, $userId)
    {
        $userActivity = FxhbUserActivity::find()->where([
            'parent_id' => $parentId, 'user_id' => $userId, 'mall_id' => $this->mall->id, 'is_delete' => 0
        ])->one();

        return $userActivity;
    }

    /**
     * @param $token
     * @return FxhbUserActivity|null
     * 根据token获取用户参与拆红包信息
     */
    public function getUserActivityByToken($token)
    {
        $userActivity = FxhbUserActivity::findOne(['token' => $token]);
        return $userActivity;
    }

    /**
     * @param $userActivityId
     * @param int $status
     * @return array|\yii\db\ActiveRecord|null|FxhbUserActivity
     * 通过转发用户的活动ID获取红包活动
     */
    public function getUserActivityById($userActivityId, $status = 0)
    {
        $userActivity = FxhbUserActivity::find()->where([
            'id' => $userActivityId, 'mall_id' => $this->mall->id, 'is_delete' => 0
        ])->keyword($status !== null, ['status' => $status])->one();
        return $userActivity;
    }

    /**
     * @param $userId
     * @param $activityId
     * @return FxhbUserActivity|null
     * 获取最近一次未到期的活动
     */
    public function getLastUserActivity($userId, $activityId)
    {
        /* @var FxhbUserActivity $userActivity */
        $userActivity = FxhbUserActivity::find()->where([
            'user_id' => $userId, 'mall_id' => $this->mall->id, 'is_delete' => 0, 'fxhb_activity_id' => $activityId,
        ])->andWhere(['status' => 0])->orderBy(['id' => SORT_DESC])->one();
        $resetTime = $this->getResetTime($userActivity);
        if ($resetTime <= 0) {
            return null;
        }
        return $userActivity;
    }

    /**
     * @param $id
     * @param $userId
     * @return array|\yii\db\ActiveRecord|null|FxhbUserActivity
     * 获取指定用户参与指定活动的记录
     */
    public function getUserActivity($id, $userId)
    {
        $userActivity = FxhbUserActivity::find()->where([
            'user_id' => $userId, 'mall_id' => $this->mall->id, 'is_delete' => 0
        ])->andWhere([
            'or',
            ['parent_id' => $id],
            ['id' => $id]
        ])->one();

        return $userActivity;
    }
}
