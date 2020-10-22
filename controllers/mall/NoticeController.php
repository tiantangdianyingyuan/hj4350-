<?php



namespace app\controllers\mall;


use app\forms\common\notice\NoticeForm;

class NoticeController extends MallController
{
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $from = new NoticeForm();
            $from->attributes = \Yii::$app->request->post();
            return $this->asJson($from->getList());
        } else {
            return $this->render('list');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $from = new NoticeForm();
            $from->attributes = \Yii::$app->request->get();
            return $this->asJson($from->getDetail());
        } else {
            return $this->render('detail');
        }
    }
}
