<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 13:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\card\GiveAndReceive;
use app\forms\api\card\UserCardForm;
use app\forms\api\card\UserCardListForm;

class CardController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionUserCard()
    {
        $form = new UserCardListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->search());
    }

    public function actionUserCardDetail()
    {
        $form = new UserCardForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->getDetail());
    }

    public function actionCardClerk()
    {
        $form = new UserCardForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->clerk());
    }

    public function actionCardQrcode()
    {
        $form = new UserCardForm();
        $form->attributes = \Yii::$app->request->get();
        $form->mall = \Yii::$app->mall;
        $form->user = \Yii::$app->user->identity;
        return $this->asJson($form->qrcode());
    }

    public function actionClerkHistory()
    {
        $form = new UserCardListForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getHistoryList();

        return $this->asJson($res);
    }

    public function actionGive()
    {
        $form = new GiveAndReceive();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->give());
    }

    public function actionReceive()
    {
        $form = new GiveAndReceive();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->receive());
    }

    public function actionGivePoster()
    {
        $form = new GiveAndReceive();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
