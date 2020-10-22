<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/7 14:03
 */


namespace app\controllers\api;

use app\forms\api\BuyDataForm;
use app\forms\api\ConfigForm;
use app\forms\api\index\IndexExtraForm;
use app\forms\api\index\NewIndexForm;
use app\forms\api\IndexForm;
use app\forms\api\index\TemplateForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    public function actionConfig()
    {
        return (new ConfigForm())->search();
    }

    public function actionPurchase()
    {
        return (new BuyDataForm())->search();
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getIndex());
    }

    // 新的首页接口
    public function actionNewIndex()
    {
        $form = new NewIndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getIndex());
    }

    //↓↓↓
    public function actionTplIndex()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getIndex());
    }


    public function actionIndexExtra()
    {
        $form = new IndexExtraForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getData());
    }
}
