<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\mall\role_user\ActionForm;
use app\forms\mall\role_user\RoleUserEditForm;
use app\forms\mall\role_user\RoleUserForm;

class RoleUserController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new RoleUserForm();
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
                $form = new RoleUserEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = $data['form'];
                $form->roles = isset($data['roles']) ? $data['roles'] : '';
                $form->user_id = isset($data['form']['id']) ? $data['form']['id'] : '';

                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new RoleUserForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 用户添加、编辑时角色列表
     */
    public function actionRoleList()
    {
        $id = \Yii::$app->request->get('id');

        $form = new RoleUserForm();
        $list = $form->roleList();

        return $this->asJson($list);
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new RoleUserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    /**
     * 修改密码
     * @return \yii\web\Response
     */
    public function actionEditPassword()
    {
        $form = new RoleUserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editPassword();

        return $this->asJson($res);
    }

    /**
     * 员工自己修改密码
     */
    public function actionUpdatePassword()
    {
        $form = new RoleUserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updatePassword();

        return $this->asJson($res);
    }

    /**
     * 员工登录入口链接
     * @return \yii\web\Response
     */
    public function actionRoute()
    {
        $form = new RoleUserForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->route();

        return $this->asJson($res);
    }

    public function actionAction()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new ActionForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getList();

                return $this->asJson($res);
            }
        } else {
            return $this->render('action');
        }
    }

    public function actionActionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActionForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getDetail();

            return $this->asJson($res);
        } else {
            return $this->render('action-detail');
        }
    }
}
