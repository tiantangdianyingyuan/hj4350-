<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role;


use app\core\response\ApiCode;
use app\forms\Menus;
use app\models\AuthRole;
use app\models\AuthRolePermission;
use app\models\Model;

class RoleEditForm extends Model
{
    public $name;
    public $remark;
    public $permissions;
    public $id;

    private $newPermissions = [];

    public function rules()
    {
        return [
            [['name', 'remark'], 'string'],
            [['permissions'], 'safe'],
            [['permissions'], 'default', 'value' => []],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '角色名称',
            'remark' => '备注',
            'permissions' => '权限'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $authRole = AuthRole::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$authRole) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $authRole = new AuthRole();
                $authRole->mall_id = \Yii::$app->mall->id;
                $authRole->creator_id = \Yii::$app->user->id;
            }

            $authRole->name = $this->name;
            $authRole->remark = $this->remark;
            $res = $authRole->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($authRole));
            }

            $authRolePermission = AuthRolePermission::findOne(['role_id' => $this->id]);
            if (!$authRolePermission) {
                $authRolePermission = new AuthRolePermission();
            }
            // TODO 特殊处理
            if (in_array('mall/coupon-auto-send/index', $this->permissions)) {
                $this->permissions[] = 'mall/coupon-auto-send/edit';
            }

            $menus = Menus::getMallMenus(true);
            // 获取action 权限路由
            $this->getPermissions($menus);
            $newPermissions = array_merge($this->permissions, $this->newPermissions);
            $newPermissions = array_unique($newPermissions);
            $newPermissions = array_values($newPermissions);

            $authRolePermission->role_id = $authRole->id;
            $authRolePermission->permissions = json_encode($newPermissions, JSON_UNESCAPED_UNICODE);
            $res = $authRolePermission->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($authRolePermission));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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


    /**
     * 获取路由权限路由
     * @param $menus
     * @param array $newData
     * @return array
     */
    private function getPermissions($menus)
    {
        foreach ($menus as $k => $item) {
            if (!isset($item['route'])) {
                $item['route'] = '';
            }
            if (in_array($item['route'], $this->permissions) && isset($item['action'])) {
                foreach ($item['action'] as $actionItem) {
                    $this->newPermissions[] = $actionItem['route'];
                }
            }

            if (isset($item['children'])) {
                $menus[$k]['children'] = $this->getPermissions($item['children']);
            }
        }
        $menus = array_values($menus);
        return $menus;
    }
}
