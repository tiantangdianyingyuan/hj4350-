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
use app\forms\common\attachment\CommonAttachment;
use app\models\Mall;
use app\models\Model;
use app\validators\PhoneNumberValidator;

class UserEditForm extends Model
{
    public $user_id;
    public $username;
    public $password;
    public $mobile;
    public $app_max_count;
    public $remark;
    public $expired_at;
    public $permissions;
    public $isCheckExpired;
    public $isAppMaxCount;
    public $secondary_permissions;

    public function rules()
    {
        return [
            [['username', 'password', 'mobile', 'app_max_count',
                'expired_at', 'isCheckExpired', 'isAppMaxCount'], 'required'],
            [['user_id'], 'integer'],
            [['mobile'], 'trim'],
            [['mobile'], PhoneNumberValidator::className()],
            [['remark', 'secondary_permissions'], 'safe'],
            [['permissions'], 'default', 'value' => []],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'mobile' => '手机号',
            'app_max_count' => '小程序数量'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->isAppMaxCount && $this->app_max_count < 0) {
                throw new \Exception('可创建小程序数量不能小于0');
            }
            $count = Mall::find()->where([
                'user_id' => $this->user_id,
                'is_delete' => 0,
            ])->count();
            if ($this->app_max_count != -1 && $count > $this->app_max_count) {
                throw new \Exception('可创建小程序数量不能小于' . $count);
            }
            
            $expiredAt = !$this->isCheckExpired ? $this->expired_at : '0000-00-00 00:00:00';
            if (!$this->secondary_permissions) {
                $this->secondary_permissions = CommonAuth::secondaryDefault();;
            }
            if (in_array('attachment', $this->permissions)
                && (!isset($this->secondary_permissions['attachment']) || empty($this->secondary_permissions['attachment']))) {
                throw new \Exception('请选择上传权限');
            }

            if ($this->user_id) {
                $adminUser = CommonAdminUser::updateAdminUser([
                    'user_id' => $this->user_id,
                    'mobile' => $this->mobile,
                    'app_max_count' => $this->app_max_count,
                    'remark' => $this->remark,
                    'expired_at' => $expiredAt,
                    'permissions' => $this->permissions,
                    'secondary_permissions' => $this->secondary_permissions
                ]);
            } else {
                // 判断可创建最大子账户
                // $this->checkAuth();

                $adminUser = CommonAdminUser::createAdminUser([
                    'username' => $this->username,
                    'password' => $this->password,
                    'mobile' => $this->mobile,
                    'app_max_count' => $this->app_max_count,
                    'remark' => $this->remark,
                    'we7_user_id' => 0,
                    'expired_at' => $expiredAt,
                    'permissions' => $this->permissions,
                    'secondary_permissions' => $this->secondary_permissions
                ]);
            }

            if (!$this->isCheckExpired) {
                $expiredAt = strtotime($this->expired_at) - time();
                \Yii::$app->queue->delay($expiredAt > 0 ? $expiredAt : 0)->push(new UserUpdateJob([
                    'user_id' => $adminUser->user_id
                ]));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'url' => \Yii::$app->urlManager->createUrl('admin/user/index'),
                    'user_id' => $adminUser->user_id
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    // private function checkAuth()
    // {
    //     $res = \Yii::$app->cloud->auth->getAuthInfo();
    //     $userNum = CommonAuth::getChildrenNum();

    //     $accountNum = $res['host']['account_num'];

    //     // 总管理员自身不算入总数限制 -1
    //     if ($accountNum > -1 && $userNum >= $accountNum) {
    //         throw new \Exception('子账户数量超出限制');
    //     }
    // }
}
