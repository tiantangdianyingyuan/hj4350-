<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/26
 * Time: 16:20
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\core\response\ApiCode;
use app\forms\api\message\TemplateForm;

class MessageController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index']
            ],
        ]);
    }

    public function actionIndex()
    {
        \Yii::$app->trigger(\Yii::$app->appMessage::EVENT_APP_MESSAGE_REQUEST);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => \Yii::$app->appMessage->getList(),
        ];
    }

    public function actionTemplate()
    {
        $form = new TemplateForm();
        if (\Yii::$app->request->isPost) {
            $form->attributes = \Yii::$app->request->post();
            return $form->send();
        } else {
            return $form->getList();
        }
    }
}
