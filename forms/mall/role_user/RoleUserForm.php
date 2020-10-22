<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role_user;


use app\core\response\ApiCode;
use app\forms\common\RoleSettingForm;
use app\models\AuthRole;
use app\models\AuthRoleUser;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use yii\rbac\Role;

class RoleUserForm extends Model
{
    public $id;
    public $password;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['password', 'keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '操作员ID',
            'password' => '密码',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $operatorIds = UserIdentity::find()->alias('i')->where(['i.is_operator' => 1])->select('i.user_id');

        $query = User::find()->alias('u')->where(['u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id])
            ->andWhere(['id' => $operatorIds]);

        if ($this->keyword) {
            $query->andWhere(['like', 'nickname', $this->keyword]);
        }

        $list = $query->page($pagination, 10)->orderBy('created_at DESC')->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function roleList()
    {
        $query = AuthRole::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);

        $list = $query->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function getDetail()
    {
        $detail = User::find()->with('role')->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->one();

        if ($detail) {
            $checkedKeys = [];
            if ($detail['role'] && is_array($detail['role'])) {
                foreach ($detail['role'] as $item) {
                    $checkedKeys[] = $item['id'];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'checkedKeys' => $checkedKeys,
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
            $user = User::find()->alias('u')->where([
                'u.id' => $this->id, 'u.mall_id' => \Yii::$app->mall->id, 'u.is_delete' => 0])
                ->joinWith(['identity' => function ($query) {
                    $query->andWhere(['is_operator' => 1]);
                }])->one();

            if (!$user) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $user->is_delete = 1;
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            $userIdentity = UserIdentity::find()->where(['user_id' => $user->id])->one();
            $userIdentity->is_delete = 1;
            $res = $userIdentity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            $res = AuthRoleUser::updateAll([
                'is_delete' => 1,
            ], [
                'user_id' => $user->id,
            ]);


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
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function editPassword()
    {
        /** @var User $user */
        $user = User::find()->alias('u')
            ->joinWith(['identity' => function ($query) {
                $query->andWhere(['is_operator' => 1]);
            }])
            ->where([
                'u.mall_id' => \Yii::$app->mall->id,
                'u.id' => $this->id,
                'u.is_delete' => 0
            ])->one();

        if (!$user) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '用户不存在',
            ];
        }


        $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
        $res = $user->save();

        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '密码修改成功',
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '密码修改失败',
        ];
    }

    public function route()
    {
        $mallId = base64_encode(\Yii::$app->mall->id);
        $url = \Yii::$app->urlManager->createAbsoluteUrl('admin/passport/login&mall_id=' . $mallId);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => urldecode($url),
            ]
        ];
    }

    public function updatePassword()
    {
        try {
            $setting = (new RoleSettingForm())->getSetting();
            if (!$setting['update_password_status']) {
                throw new \Exception('员工无权限修改密码');
            }
            $user = User::findOne(\Yii::$app->user->id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            $logout = \Yii::$app->user->logout();
            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '密码修改成功',
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
