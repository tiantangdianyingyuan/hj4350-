<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\user;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\Model;
use app\models\Option;

class BatchPermissionForm extends Model
{
    public $formData;
    public $globalData;

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->formData = json_decode($this->formData, true);
            $res = AdminInfo::updateAll([
                'permissions' => json_encode($this->formData['basePermission']),
                'secondary_permissions' => json_encode($this->formData['secondaryPermissions'])
            ], [
                'user_id' => $this->formData['chooseList'],
                'is_delete' => 0
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    public function saveGlobalPermission()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $option = CommonOption::set(Option::NAME_GLOBAL_PERMISSION, $this->globalData, 0, Option::GROUP_ADMIN);
            if (!$option) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    public function getGlobalPermission()
    {
        $option = CommonOption::get(Option::NAME_GLOBAL_PERMISSION, 0, Option::GROUP_ADMIN, $this->defaultGlobalPermission());
        $option['is_open'] = (int)$option['is_open'];
        $option['permission_type'] = (int)$option['permission_type'];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'option' => $option
            ]
        ];
    }

    private function defaultGlobalPermission()
    {
        return [
            'is_open' => 0,
            'permission_type' => 1, // 1.有所有权限，2.无权限
        ];
    }
}