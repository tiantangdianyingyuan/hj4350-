<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\mall\role\RoleEditForm;
use app\forms\mall\role\RoleForm;
use app\forms\mall\role\RolePermissionForm;
use app\models\AuthRolePermission;

class RoleController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new RoleForm();
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
                $form = new RoleEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = $data['form'];
                $form->permissions = $data['permissions'] ? json_decode($data['permissions']) : [];
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new RoleForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 角色添加、编辑时 所能分配的权限
     */
    public function actionPermissions()
    {
        $id = \Yii::$app->request->get('id');

        $form = new RolePermissionForm();
        $list = $form->getList();

        $defaultCheckedKeys = [];
        if ($id) {
            $rolePermissions = AuthRolePermission::find()->where(['role_id' => $id])->one();
            if ($rolePermissions && $rolePermissions->permissions) {
                $defaultCheckedKeys = json_decode($rolePermissions->permissions, true);
            }
        }
        $newKeys = [];
        foreach ($defaultCheckedKeys as $key => $value) {
            if ($value && $value != 'mall/plugin/index') {
                $newKeys[] = $value;
            }
        }

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'permissions' => $list,
                'defaultCheckedKeys' => $newKeys
            ]
        ]);
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new RoleForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }
}
