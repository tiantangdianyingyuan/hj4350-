<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\user;


use app\core\response\ApiCode;
use app\forms\common\CommonAdminUser;
use app\forms\common\CommonAuth;
use app\models\Model;

class UserBindForm extends Model
{
    public $user_id;
    public $we7_user_id;

    public function rules()
    {
        return [
            [['user_id', 'we7_user_id'], 'integer'],
        ];
    }

    public function bind()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // TODO 这里为什么要判断用户权限？
//        $app_admin = false;
//        $permission_arr = \Yii::$app->role->getPermission();
//        if (!is_array($permission_arr) && $permission_arr) {
//            $app_admin = true;
//        } else {
//            foreach ($permission_arr as $value) {
//                if ($value == 'app_admin') {
//                    $app_admin = true;
//                }
//            }
//        }
//        if (!$app_admin) {
//            return [
//                'code' => ApiCode::CODE_ERROR,
//                'msg' => '无权限操作',
//            ];
//        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CommonAdminUser::bindAdminUser(['user_id' => $this->user_id]);
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '服务器错误:' . $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
