<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/8
 * Time: 14:36
 */

namespace app\controllers\common;


use app\controllers\Controller;
use app\core\response\ApiCode;
use app\forms\common\CheckEnvForm;
use app\forms\common\convert\Convert;
use app\forms\common\convert\TestQueueJob;

class ConvertController extends Controller
{
    public function behaviors()
    {
        if (\Yii::$app->session->get('convertpass') !== 1 && 0) {
            \Yii::$app->response->data = '禁止访问。';
            \Yii::$app->end();
        }
        return parent::behaviors();
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCheckEnv()
    {
        $checkEnvForm = new CheckEnvForm();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $checkEnvForm->check(),
        ];
    }

    public function actionCheckQueue($action)
    {
        if ($action === 'create') {
            \Yii::warning('TestQueueJob Push At --->' . date('Y-m-d H:i:s'));
            $id = \Yii::$app->queue->push(new TestQueueJob());
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'id' => $id,
                ],
            ];
        } else {
            $job = new TestQueueJob();
            if ($job->checkJob() === true) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'retry' => 0,
                    ],
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'retry' => 1,
                    ],
                ];
            }
        }
    }

    public function actionCheckStore()
    {
        $form = new Convert();
        return $form->checkStore();
    }

    public function actionConvertStore()
    {
        if (\Yii::$app->request->isPost) {
            $form = new Convert();
            return $form->convertStore(\Yii::$app->request->post('id'));
        }
    }

    public function actionConvertSystemData()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
        ];
    }
}
