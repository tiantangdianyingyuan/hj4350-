<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\mall\service\ServiceEditForm;
use app\forms\mall\service\ServiceForm;

class ServiceController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new ServiceForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ServiceEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new ServiceForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new ServiceForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    /**
     * 默认开关
     * @return \yii\web\Response
     */
    public function actionSwitchChange()
    {
        $form = new ServiceForm();
        $form->attributes = \Yii::$app->request->post('form');
        $res = $form->switchChange();

        return $this->asJson($res);
    }

    /**
     * 获取商品服务列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new ServiceForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }
}
