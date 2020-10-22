<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 10:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\controllers\mall;


use app\plugins\bargain\controllers\Controller;
use app\plugins\bargain\forms\mall\GoodsEditForm;
use app\plugins\bargain\forms\mall\GoodsForm;
use app\plugins\bargain\forms\mall\GoodsListForm;
use yii\helpers\Json;

class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $search = \Yii::$app->request->get('search');
            $form->attributes = $search ? Json::decode($search, true) : [];
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            return $this->render('index');
        }
    }
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new GoodsForm();
                $form->mall = \Yii::$app->mall;
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $data = \Yii::$app->request->post();
                $dataForm = json_decode($data['form'], true);
                $form->attributes = isset($dataForm) ? $dataForm : [];
                $form->attrGroups = [];
                // TODO 临时
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionChangeSort()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->post();
            $form->mall = \Yii::$app->mall;
            return $this->asJson($form->setSort());
        }
    }
}
