<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 18:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\controllers\mall;


use app\plugins\Controller;
use app\plugins\diy\forms\mall\GoodsForm;
use app\plugins\diy\forms\mall\PosterForm;
use app\plugins\diy\forms\mall\SyncTemplateTypeForm;
use app\plugins\diy\forms\mall\TemplateEditForm;
use app\plugins\diy\forms\mall\TemplateForm;

class TemplateController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionMarketSearch()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        $form->templateType = 'page';
        return $this->asJson($form->getMarketList());
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateEditForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            if (\Yii::$app->request->isPost) {
                return $this->asJson($form->save());
            } else {
                return $this->asJson($form->get());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        $form = new TemplateForm();
        $id = \Yii::$app->request->get('id');
        return $this->asJson($form->destroy($id));
    }

    public function actionGetGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->setSign(\Yii::$app->request->get('sign'));
        return $this->asJson($form->search());
    }

    public function actionChangeHomeStatus()
    {
        if (\Yii::$app->request->isPost) {
            $form = new TemplateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeHasHomeStatus());
        }
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    public function actionSyncTemplateType()
    {
        return $this->asJson((new SyncTemplateTypeForm())->sync());
    }
}
