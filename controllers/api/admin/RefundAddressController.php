<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/31
 * Time: 13:53
 */

namespace app\controllers\api\admin;

use app\forms\mall\refund_address\RefundAddressEditForm;
use app\forms\mall\refund_address\RefundAddressForm;

class RefundAddressController extends AdminController
{
    public function actionIndex()
    {
        $form = new RefundAddressForm();
        $form->attributes = \Yii::$app->request->get();
        $list = $form->getList();

        return $this->asJson($list);
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RefundAddressEditForm();
                $form->attributes = json_decode(\Yii::$app->request->post('form'),true);
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new RefundAddressForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        }
    }

    public function actionDestroy()
    {
        $form = new RefundAddressForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }
}