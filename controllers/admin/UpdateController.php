<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/1
 * Time: 15:58
 */

namespace app\controllers\admin;


use app\controllers\behaviors\SuperAdminFilter;
use app\core\cloud\CloudException;
use app\core\response\ApiCode;
use app\forms\admin\PluginUpdateDataForm;

class UpdateController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            try {
                $versions = \Yii::$app->cloud->update->getVersionData();
            } catch (CloudException $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage(),
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $versions,
            ];
        } else {
            return $this->render('index');
        }
    }

    public function actionUpdate()
    {
        if (\Yii::$app->request->isPost) {
            try {
                $result = \Yii::$app->cloud->update->update();
                \Yii::$app->cache->flush();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功。',
                    'data' => $result,
                ];
            } catch (\Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage(),
                ];
            }
        }
    }

    public function actionPluginUpdateData()
    {
        $form = new PluginUpdateDataForm();
        return $form->search();
    }
}
