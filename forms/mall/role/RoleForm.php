<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role;


use app\core\response\ApiCode;
use app\models\AuthRole;
use app\models\AuthRolePermission;
use app\models\Model;

class RoleForm extends Model
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = AuthRole::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with('user');
        if ($this->keyword) {
            $query->where(['like', 'name', $this->keyword]);
        }

        $list = $query->page($pagination, 10)->orderBy('created_at DESC')->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = AuthRole::find()->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id])
            ->asArray()->one();

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '请求失败',
        ];
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $role = AuthRole::find()->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id])->one();

            if (!$role) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $role->is_delete = 1;
            $res = $role->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($role));
            }

            $rolePermission = AuthRolePermission::find()->where(['role_id' => $this->id])->one();
            $rolePermission->is_delete = 1;
            $res = $rolePermission->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($rolePermission));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
