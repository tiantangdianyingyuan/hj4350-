<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 13:49
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\coupon\CouponDetailForm;
use app\forms\api\coupon\CouponForm;
use app\forms\api\coupon\ShareCouponForm;
use app\forms\api\coupon\UserCouponDetailForm;
use app\forms\api\coupon\UserCouponForm;

class CouponController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['list', 'detail']
            ],
        ]);
    }

    // 领券中心优惠券列表
    public function actionList()
    {
        $form = new CouponForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    // 优惠券详情
    public function actionDetail()
    {
        $form = new CouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    // 领取优惠券
    public function actionReceive()
    {
        $form = new CouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->receive();
    }

    // 我的优惠券
    public function actionUserCoupon()
    {
        $form = new UserCouponForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    // 分享领取优惠券
    public function actionShareCoupon()
    {
        $form = new ShareCouponForm();
        $form->user = \Yii::$app->user->identity;
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->send());
    }

    public function actionUserCouponDetail()
    {
        $form = new UserCouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionGive()
    {
        $form = new CouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->give());
    }

    public function actionGivePoster()
    {
        $form = new CouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
