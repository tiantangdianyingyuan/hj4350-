<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/10
 * Time: 16:19
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\controllers\mall;


use app\plugins\Controller;
use app\plugins\ecard\forms\mall\EditForm;
use app\plugins\ecard\forms\mall\ImportForm;
use app\plugins\ecard\forms\mall\IndexForms;
use app\plugins\ecard\forms\mall\ListEditForm;
use app\plugins\ecard\forms\mall\ListForm;

class IndexController extends Controller
{
    // 电子卡密列表
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new IndexForms();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    // 电子卡密编辑
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getOne());
            }
        } else {
            return $this->render('edit');
        }
    }

    // 电子卡密删除
    public function actionEcardDestroy()
    {
        if (\Yii::$app->request->isGet) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->destroy());
        }
    }

    // 卡密数据列表
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    // 新建卡密数据
    public function actionListEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ListEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->batch());
            }
        } else {
            return $this->render('list-edit');
        }
    }

    // 卡密数据删除
    public function actionDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListEditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->destroy());
        }
    }

    // 卡密数据编辑
    public function actionEditData()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editData());
        }
    }

    // 模板下载和导出数据
    public function actionExport()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->export());
        }
    }

    // 导入卡密数据
    public function actionImport()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ImportForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->import());
        }
    }
}
