<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 14:50
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\components;


use app\forms\common\template\TemplateForm;
use yii\base\Action;

class TemplateAction extends Action
{
    public $templateClass;

    public function run()
    {
        /* @var TemplateForm $form */
        $form = \Yii::createObject([
            'class' => $this->templateClass
        ]);
        if (\Yii::$app->request->isAjax) {
            $data = [];
            if (\Yii::$app->request->isGet) {
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                $data = $form->getDetail($add, $platform);
            }
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                $data = $form->save();
            }
            \Yii::$app->response->data = $data;
        } else {
            return $this->controller->render('@app/views/mall/template-msg/template.php', [
                'url' => $form->url,
                'addUrl' => $form->addUrl,
                'submitUrl' => $form->submitUrl,
            ]);
        }
    }
}
