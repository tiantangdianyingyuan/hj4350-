<?php

namespace app\plugins\gift\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\gift\forms\api\GiftListForm;
use app\plugins\gift\forms\common\CommonGift;

class OrderListController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    //我送出的
    public function actionSendList()
    {
        $form=new GiftListForm();
        $form->attributes=\Yii::$app->request->get();
        return $this->asJson($form->getSendList());
    }

    //我送出的
    public function actionSendDetail()
    {
        $form=new GiftListForm();
        $form->attributes=\Yii::$app->request->get();
        return $this->asJson($form->getSendDetail());
    }
}
