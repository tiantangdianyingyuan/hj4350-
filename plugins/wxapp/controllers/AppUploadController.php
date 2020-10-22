<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/26
 * Time: 10:55
 */

namespace app\plugins\wxapp\controllers;


use app\core\response\ApiCode;
use app\plugins\Controller;
use app\plugins\wxapp\forms\AppPluginsForm;
use app\plugins\wxapp\forms\AppQrcodeForm;
use app\plugins\wxapp\forms\AppUploadForm;
use app\plugins\wxapp\forms\JumpAppidForm;
use app\plugins\wxapp\models\WxappJumpAppid;

class AppUploadController extends Controller
{
    public function actionIndex($branch = null)
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AppUploadForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getResponse());
        } else {
            return $this->render('index', [
                'branch' => $branch,
            ]);
        }
    }

    public function actionNoMch()
    {
        return $this->actionIndex('nomch');
    }

    public function actionAppQrcode()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AppQrcodeForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getResponse());
        }
    }

    public function actionJumpAppid()
    {
        if (\Yii::$app->request->isPost) {
            $form = new JumpAppidForm();
            $form->appid_list = \Yii::$app->request->post('appid_list');
            return $form->getResponseData();
        } else {
            $list = WxappJumpAppid::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->all();
            $newList = [];
            foreach ($list as $item) {
                $newList[] = $item->appid;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                ],
            ];
        }
    }

    public function actionAppPlugins()
    {
        $form = new AppPluginsForm();
        if (\Yii::$app->request->isPost) {
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            return $form->search();
        }
    }
}
