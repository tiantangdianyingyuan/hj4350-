<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\common;

use app\models\Model;
use app\plugins\step\models\StepUser;

class CommonStepNewUser extends Model
{
    public $parent_id;

    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['parent_id'], 'default', 'value' => 0]
        ];
    }
    public function save($class)
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        
        $t = \Yii::$app->db->beginTransaction();
        $model = new StepUser();
        if ($this->parent_id) {
            $parent = StepUser::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $this->parent_id,
                'is_delete' => 0
            ]);

            if ($parent && $parent->user_id != \Yii::$app->mall->id) {
                $invite_ratio = CommonStep::getSetting()['invite_ratio'];
                $parent->ratio = $parent->ratio + $invite_ratio;
                $parent->save();

                $model->invite_ratio = $invite_ratio ? : 0;
                $model->parent_id = $parent->id;
            }
        }
        $model->step_currency = 0;
        $model->user_id = \Yii::$app->user->id;
        $model->mall_id = \Yii::$app->mall->id;

        if (!$model->save()) {
            $t->rollBack();
            throw new \Exception($this->getErrorMsg($model));
        }

        $query = StepUser::find()->where(['mall_id' => \Yii::$app->mall->id,'user_id' => \Yii::$app->user->id,'is_delete' => 0]);
        $count = $query->count();
        if ($count > 1) {
            $t->rollBack();
        } else {
            $t->commit();
        }
        return $class;
    }
}
