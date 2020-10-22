<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\mall_member\CouponForm;
use app\forms\api\mall_member\MallMemberForm;
use app\forms\api\mall_member\MallMemberOrderForm;

class MallMemberController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new MallMemberForm();

        return $this->asJson($form->getIndex());
    }

    public function actionAllMember()
    {
        $form = new MallMemberForm();

        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new MallMemberForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getDetail());
    }

    public function actionMemberCoupon()
    {
        $form = new MallMemberForm();

        return $this->asJson($form->getMemberCoupons());
    }

    public function actionMemberGoods()
    {
        $form = new MallMemberForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getMemberGoods());
    }

    public function actionGoodsCats()
    {
        $form = new MallMemberForm();

        return $this->asJson($form->getMemberGoodsCats());
    }

    public function actionPurchaseMember()
    {
        $form = new MallMemberOrderForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->purchaseMallMember());
    }

    public function actionCouponReceive()
    {
        $form = new CouponForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->receive();
    }
}
