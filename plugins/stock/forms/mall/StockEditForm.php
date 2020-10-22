<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:22
 */

namespace app\plugins\stock\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;

class StockEditForm extends Model
{
    public $user_id;
    public $level_id;
    public $name;
    public $phone;

    public function rules()
    {
        return [
            [['user_id', 'level_id', 'phone'], 'integer'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '姓名',
            'phone' => '手机号'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->user_id) {
                throw new \Exception('错误的用户');
            }
            if (!$this->level_id) {
                throw new \Exception('错误的等级');
            }
            //判断是否是分销商
            $user = UserIdentity::findOne([
                'user_id' => $this->user_id,
            ]);
            if ($user->is_distributor != 1) {
                throw new \Exception('该用户不是分销商');
            }
            $level_info = StockLevel::findOne(['id' => $this->level_id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (empty($level_info)) {
                throw new \Exception('股东等级不存在');
            }
            //添加股东开始
            $model = StockUser::findOne(['user_id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id]);
            if (empty($model)) {
                $model = new StockUser();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = $this->user_id;
                $model->level_id = $this->level_id;

                $user_info = new StockUserInfo();
                $user_info->user_id = $this->user_id;
            } else {
                $model->level_id = $this->level_id;

                $user_info = StockUserInfo::findOne(['user_id' => $this->user_id]);
            }
            if ($model->status != 1) {
                $model->status = 1;
                $model->created_at = mysql_timestamp();//添加时间重置
                $model->applyed_at = '0000-00-00 00:00:00';
                $model->agreed_at = mysql_timestamp();
            }
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $user_info->name = $this->name;
            $user_info->phone = $this->phone;
            $user_info->remark = '';
            $user_info->reason = '';
            if (!$user_info->save()) {
                throw new \Exception($this->getErrorMsg($user_info));
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}