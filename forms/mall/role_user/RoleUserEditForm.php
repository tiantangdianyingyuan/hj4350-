<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role_user;


use app\core\response\ApiCode;
use app\models\AuthRoleUser;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;

class RoleUserEditForm extends Model
{
    public $nickname;
    public $username;
    public $password;
    public $roles;
    public $user_id;

    public $user;
    public $isNewRecord;

    public function rules()
    {
        return [
            [['nickname', 'username', 'password'], 'string'],
            [['user_id',], 'integer'],
            [['roles'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'nickname' => '昵称',
            'roles' => '角色'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 区分是添加还是编辑
            if ($this->user_id) {
                $user = User::findOne($this->user_id);
                if (!$user) {
                    throw new \Exception('数据异常,该条数据不存在');
                }

                $userIdentity = UserIdentity::find()->where(['user_id' => $user->id])->one();
            } else {
                $user = new User();
                $userIdentity = new UserIdentity();
            }
            $this->user = $user;

            // 检测是否有重复数据
            $query = User::find()->alias('u')->joinWith(['identity' => function ($query) {
                $query->andWhere(['is_operator' => 1]);
            }])->where([
                'u.username' => $this->username,
                'u.is_delete' => 0,
                'u.mall_id' => \Yii::$app->mall->id,
                'u.mch_id' => \Yii::$app->user->identity->mch_id
            ]);

            if ($this->user_id) {
                $query = $query->andWhere(['!=', 'u.id', $this->user_id]);
            }

            if ($query->one()) {
                throw new \Exception('用户名已存在');
            }

            if ($user->isNewRecord) {
                $user->username = $this->username;
                $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
                $user->access_token = \Yii::$app->security->generateRandomString();
                $user->auth_key = \Yii::$app->security->generateRandomString();
                $user->mall_id = \Yii::$app->mall->id;
            }
            $user->nickname = $this->nickname;
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            if ($userIdentity->isNewRecord) {
                $userIdentity->user_id = $user->id;
                $userIdentity->is_operator = 1;
                $res = $userIdentity->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($userIdentity));
                }
            }

            $this->setRoleUser();

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
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 设置用户角色关联
     */
    private function setRoleUser()
    {
        if (!$this->user->isNewRecord) {
            AuthRoleUser::updateAll([
                'is_delete' => 1,
            ], [
                'user_id' => $this->user->id
            ]);
        }
        if ($this->roles && is_array($this->roles)) {
            $attributes = [];
            foreach ($this->roles as $item) {
                $authRoleUser = AuthRoleUser::findOne(['user_id' => $this->user->id, 'role_id' => $item]);
                if ($authRoleUser) {
                    $authRoleUser->is_delete = 0;
                    $res = $authRoleUser->save();

                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($authRoleUser));
                    }
                } else {
                    $attributes[] = [
                        $item, $this->user->id,
                    ];
                }
            }

            $query = \Yii::$app->db->createCommand();
            $res = $query->batchInsert(AuthRoleUser::tableName(), ['role_id', 'user_id'], $attributes)
                ->execute();

            if ($res != count($attributes)) {
                throw new \Exception('保存失败, 角色数据异常');
            }
        }
    }
}
