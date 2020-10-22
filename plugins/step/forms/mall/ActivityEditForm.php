<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\step\forms\mall;

use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\step\models\StepActivity;
use app\plugins\step\models\StepActivityLog;
use app\plugins\step\jobs\StepActivityJob;

class ActivityEditForm extends Model
{
    public $id;
    public $title;
    public $currency;
    public $step_num;
    public $bail_currency;
    public $status;
    public $type;
    public $begin_at;
    public $end_at;

    public function rules()
    {
        return [
            [['title', 'status', 'begin_at', 'end_at', 'step_num'], 'required'],
            [['id', 'step_num', 'type'], 'integer'],
            [['currency', 'bail_currency'], 'number'],
            [['begin_at', 'end_at'], 'safe'],
            [['title', 'status'], 'string', 'max' => 255],
            [['currency', 'bail_currency', 'step_num', 'type'], 'default', "value" => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title' => '名称',
            'currency' => '奖金池',
            'step_num' => '挑战步数',
            'bail_currency' => '保证金',
            'status' => 'Status',
            'type' => '0进行中 1 已完成 2 已解散',
            'begin_at' => '开始时间',
            'end_at' => '结束时间',
        ];
    }
    //GET
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepActivity::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if ($model) {
            if (StepActivityLog::findOne(['mall_id' => \Yii::$app->mall->id, 'activity_id' => $model->id])) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '用户已报名，无法编辑',
                ];
            }
        } else {
            $model = new StepActivity();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {

            $time = strtotime($model->end_at) - time() + 86400 > 0 ? strtotime($model->end_at) - time() + 86400 + 60: 0;

            $id = \Yii::$app->queue->delay($time)->push(new StepActivityJob([
                'model' => $model,
            ]));
                        
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
