<?php


namespace app\controllers\admin;


use app\controllers\behaviors\SuperAdminFilter;
use app\forms\common\notice\NoticeCreateForm;
use app\forms\common\notice\NoticeForm;

class NoticeController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
                'safeRoutes' => [
                    'admin/notice/list',
                ]
            ],
        ]);
    }

    public function actionNotice()
    {
        if (\Yii::$app->request->isAjax) {
            $from = new NoticeCreateForm();
            $from->attributes = \Yii::$app->request->post();
            return $this->asJson($from->save());
        } else {
            return $this->render('notice');
        }
    }

    public function actionNoticeDel()
    {
        $from = new NoticeCreateForm();
        $from->attributes = \Yii::$app->request->get();
        return $this->asJson($from->del());
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $from = new NoticeForm();
            $from->attributes = \Yii::$app->request->post();
            $from->type = 2;
            return $this->asJson($from->getList());
        } else {
            return $this->render('list');
        }
    }
}
