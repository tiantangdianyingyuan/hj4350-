<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\region\controllers\mall;

use app\plugins\Controller;
use app\plugins\region\forms\mall\RegionEditForm;
use app\plugins\region\forms\mall\RegionForm;
use app\plugins\region\forms\mall\RegionLevelUpForm;
use app\plugins\region\forms\mall\ShareForm;

class RegionController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new RegionForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new RegionForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            }
        }
        return $this->render('index');
    }

    public function actionDetail()
    {
        return $this->render('detail');
    }

    /**区域代理申请审核**/
    public function actionApply()
    {
        $form = new RegionEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->become());
    }

    /**解除区域代理**/
    public function actionRemove()
    {
        $form = new RegionEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remove());
    }

    /**添加区域代理备注**/
    public function actionRemark()
    {
        $form = new RegionForm();
        $form->scenario = 'remark';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remark());
    }

    /**添加区域代理**/
    public function actionAdd()
    {
        if (\Yii::$app->request->isPost) {
            $form = new RegionEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**删除被拒绝的区域代理申请**/
    public function actionDelete()
    {
        $form = new RegionForm();
        $form->scenario = 'delete';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**分销商**/
    public function actionShare()
    {
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**代理升级审核**/
    public function actionLevelUp()
    {
        $form = new RegionLevelUpForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->levelUp());
    }
}
