<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\Model;
use app\models\Store;
use app\models\User;

class ClerkEditForm extends Model
{
    public $id;
    public $user_id;
    public $store_id;

    public function rules()
    {
        return [
            [['id', 'store_id', 'user_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = User::findOne(['id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $store = Store::findOne(['id' => $this->store_id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$store) {
                throw new \Exception('门店不存在');
            }

            // 检测是否重复添加
            if (!$this->id) {
                $checkUser = ClerkUser::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => $this->user_id,
                    'is_delete' => 0,
                ])->one();
                if ($checkUser) {
                    throw new \Exception('该用户已经是其他店铺核销员');
                }
            }


            // 判断之前是否为核销员 没有is_delete 条件
            /** @var ClerkUser $clerkUser */
            $clerkUser = ClerkUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'user_id' => $this->user_id
            ])->one();

            if (!$clerkUser) {
                $clerkUser = new ClerkUser();
                $clerkUser->mall_id = \Yii::$app->mall->id;
                $clerkUser->mch_id = \Yii::$app->user->identity->mch_id;
                $clerkUser->user_id = $this->user_id;
            } else {
                // 如果之前是核销员 is_delete 改为0就好
                $clerkUser->is_delete = 0;
            }
            $res = $clerkUser->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($clerkUser));
            }

            // 绑定分销门店关系
            $model = ClerkUserStoreRelation::findOne(['clerk_user_id' => $clerkUser->id]);
            if (!$model) {
                $model = new ClerkUserStoreRelation();
            }
            $model->clerk_user_id = $clerkUser->id;
            $model->store_id = $this->store_id;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "保存成功"
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
