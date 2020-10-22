<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/6
 * Time: 17:14
 */

namespace app\plugins\flash_sale\controllers\mall;

use app\plugins\Controller;
use app\plugins\flash_sale\forms\mall\ActivityEditForm;
use app\plugins\flash_sale\forms\mall\ActivityForm;
use app\plugins\flash_sale\forms\mall\ActivityGoodsEditForm;
use app\plugins\flash_sale\forms\mall\GoodsEditForm;
use Yii;
use yii\web\Response;

class ActivityController extends Controller
{

    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $form->attributes = Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new ActivityForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionCheck()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isGet) {
                $form = new ActivityForm();
                $form->attributes = Yii::$app->request->get();
                $form->scenario = 'check';
                return $this->asJson($form->check());
            }
        }
    }

    public function actionGoods()
    {
        if (Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = Yii::$app->request->get();
            return $this->asJson($form->getGoods());
        }
    }

    /**
     * 选择活动商品
     * @return Response
     */
    public function actionEditGoods()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isPost) {
                $form = new ActivityGoodsEditForm();
                $form->scenario = 'activity';
                $form->attributes = Yii::$app->request->post();
                $form->add = json_decode($form->add, true);
                $form->edit = json_decode($form->edit, true);
                $form->del = json_decode($form->del, true);
                return $this->asJson($form->editGoods());
            }
        }
    }

    /**
     * 编辑活动商品详情
     * @return string|Response
     */
    public function actionEditActivityGoods()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->isGet) {
                $form = new ActivityGoodsEditForm();
                $form->attributes = Yii::$app->request->get();
                return $this->asJson($form->getGoods());
            } else {
                $data = Yii::$app->request->post();
                $form = new GoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attrGroups = json_decode($data['attrGroups'], true);
                $form->isGoodsDetail = true;
                $res = $form->save();
                return $this->asJson($res);
            }
        } else {
            return $this->render('edit-activity-goods');
        }
    }

    public function actionEditStatus()
    {
        if (Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = Yii::$app->request->post();
            return $this->asJson($form->status());
        }
    }

    public function actionMallGoods()
    {
        if (Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = Yii::$app->request->get();
            return $this->asJson($form->getMallGoods());
        }
    }
}
