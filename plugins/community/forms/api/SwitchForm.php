<?php

namespace app\plugins\community\forms\api;


use app\core\response\ApiCode;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunitySwitch;


class SwitchForm extends Model
{
    public $goods_id;
    public $activity_id;

    public function rules()
    {
        return [
            [['goods_id', 'activity_id'], 'integer'],
            [['goods_id', 'activity_id'], 'required']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = CommunitySwitch::findOne(['activity_id' => $this->activity_id, 'goods_id' => $this->goods_id, 'middleman_id' => \Yii::$app->user->id]);
            if (empty($model)) {
                $model = new CommunitySwitch();
                $model->activity_id = $this->activity_id;
                $model->goods_id = $this->goods_id;
                $model->middleman_id = \Yii::$app->user->id;
            } else {
                $model->is_delete = $model->is_delete == 0 ? 1 : 0;
            }
            if (!$model->save()) {
                throw new \Exception((new Model())->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
