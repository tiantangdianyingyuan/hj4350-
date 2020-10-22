<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\we7;


use app\core\response\ApiCode;
use app\models\AdminInfo;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use yii\db\Query;

class AuthPermissionsForm extends Model
{
    public $user_id;
    public $permissions;
    public $user_ids;
    public $is_default;
    public $secondary_permissions;

    public function rules()
    {
        return [
            [['user_id', 'is_default'], 'integer'],
            [['permissions', 'user_ids', 'secondary_permissions'], 'safe'],
            [['is_default'], 'in', 'range' => [0, 1]],
            [['is_default'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function updatePermissions()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (empty($this->permissions) || !is_array($this->permissions)) {
                $this->permissions = [];
            }

            /* @var AdminInfo $adminInfo */
            $adminInfo = AdminInfo::find()->where(['we7_user_id' => $this->user_id, 'is_delete' => 0])->one();
            if (!$adminInfo) {
                $adminInfo = $this->createUser($this->user_id);
                if (!$adminInfo) {
                    throw new \Exception('用户不存在');
                }
            }
            if (in_array('attachment', $this->permissions)
                && (!isset($this->secondary_permissions['attachment']) || empty($this->secondary_permissions['attachment']))) {
                throw new \Exception('请选择上传权限');
            }

            $adminInfo->permissions = \Yii::$app->serializer->encode($this->permissions);
            $adminInfo->secondary_permissions = \Yii::$app->serializer->encode($this->secondary_permissions);
            $adminInfo->is_default = $this->is_default;
            $res = $adminInfo->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($adminInfo));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function batchUpdatePermissions()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($this->user_ids as $id) {
                $adminInfo = AdminInfo::find()->where(['we7_user_id' => $id, 'is_delete' => 0])
                    // TODO 后期加上
//                    ->andWhere(['>', 'we7_user_id', 0])
                    ->one();

                if (!$adminInfo) {
                    $adminInfo = $this->createUser($id);
                    if (!$adminInfo) {
                        throw new \Exception('ID为:' . $id . '的用户不存在');
                    }
                }
                if (empty($this->permissions) || !is_array($this->permissions)) {
                    $this->permissions = [];
                }
                if (in_array('attachment', $this->permissions)
                    && (!isset($this->secondary_permissions['attachment']) || empty($this->secondary_permissions['attachment']))) {
                    throw new \Exception('请选择上传权限');
                }
                $adminInfo->permissions = \Yii::$app->serializer->encode($this->permissions);
                $adminInfo->secondary_permissions = \Yii::$app->serializer->encode($this->secondary_permissions);
                $adminInfo->is_default = $this->is_default;
                $res = $adminInfo->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($adminInfo));
                }
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
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

    /**
     * 将微擎用户转入
     */
    private function createUser($we7UserId)
    {
        $query = new Query();
        $insUser = $query->from(['u' => we7_table_name('users')])->where(['u.uid' => $we7UserId])->one();
        if (!$insUser) {
            throw new \Exception('用户不存在,ID:' . $we7UserId);
        }

        $baseModel = new Model();

        $user = new User();
        $user->mall_id = 0;
        $user->username = $insUser['username'];
        $user->password = \Yii::$app->security->generatePasswordHash(\Yii::$app->security->generateRandomString());
        $user->nickname = $insUser['username'];
        $user->auth_key = \Yii::$app->security->generateRandomString();
        $user->access_token = \Yii::$app->security->generateRandomString();
        $user->created_at = mysql_timestamp();
        if (!$user->save()) {
            throw new \Exception($baseModel->getErrorMsg($user));
        }

        $userIdentity = new UserIdentity();
        $userIdentity->user_id = $user->id;
        $userIdentity->is_super_admin = $insUser['uid'] == 1 ? 1 : 0;
        $userIdentity->is_admin = $insUser['uid'] == 1 ? 0 : 1;
        if (!$userIdentity->save()) {
            throw new \Exception($baseModel->getErrorMsg($userIdentity));
        }

        $adminInfo = new AdminInfo();
        $adminInfo->user_id = $user->id;
        $adminInfo->app_max_count = 0;
        $adminInfo->permissions = \Yii::$app->serializer->encode([]);
        $adminInfo->secondary_permissions = \Yii::$app->serializer->encode([1, 2, 3, 4]);
        $adminInfo->we7_user_id = $insUser['uid'];
        $adminInfo->is_default = 0;
        if (!$adminInfo->save()) {
            throw new \Exception($baseModel->getErrorMsg($adminInfo));
        }

        return $adminInfo;
    }
}
