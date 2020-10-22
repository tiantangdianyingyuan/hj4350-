<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\controllers\behaviors\SuperAdminFilter;
use app\core\response\ApiCode;
use app\forms\common\attachment\CommonAttachment;
use app\forms\common\CommonAuth;
use app\forms\common\UploadForm;
use app\forms\mall\we7\AuthForm;
use app\forms\mall\we7\AuthPermissionsForm;
use yii\web\UploadedFile;

class We7Controller extends MallController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
            ],
        ]);
    }

    public function actionAuth()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new AuthForm();
                $form->attributes = \Yii::$app->request->get();
                $form->search = \Yii::$app->request->get('search');

                return $this->asJson($form->getList());
            }
        }
        return $this->render('auth');
    }

    public function actionPermissions()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AuthPermissionsForm();
                $form->attributes = \Yii::$app->request->post();

                return $this->asJson($form->updatePermissions());
            } else {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'permissions' => CommonAuth::getPermissionsList(),
                        'storage' => CommonAttachment::getCommon()->getStorage()
                    ]
                ]);
            }
        }
    }

    public function actionStatus()
    {
        $form = new AuthForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->updateStatus());
    }

    /**
     * 批量设置账户权限
     */
    public function actionBatchUpdatePermissions()
    {
        $form = new AuthPermissionsForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->batchUpdatePermissions());
    }

    public function actionBaseSetting()
    {
        return $this->render('base-setting');
    }

    public function actionUploadLogo($name = 'file')
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }
}
