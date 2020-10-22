<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\scratch\forms\mall;

use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\scratch\forms\common\CommonScratch;
use app\plugins\scratch\models\ScratchSetting;

class ScratchSettingForm extends Model
{
    public $probability;
    public $oppty;
    public $type;
    public $title;
    public $rule;
    public $start_at;
    public $end_at;
    public $deplete_integral_num;
    public $send_type;
    public $payment_type;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $bg_pic;

    public function rules()
    {
        return [
            [['probability', 'oppty', 'type', 'deplete_integral_num', 'is_sms', 'is_mail', 'is_print'], 'integer'],
            [['probability', 'oppty', 'type', 'start_at', 'end_at'], 'required'],
            [['probability', 'oppty', 'deplete_integral_num'], 'integer', 'max' => 2000000000],
            [['title', 'rule'], 'default', 'value' => ''],
            [['deplete_integral_num'], 'default', 'value' => 0],
            [['title'], 'string', 'max' => 20],
            [['rule'], 'string'],
            [['bg_pic'], 'string', 'max' => 255],
            [['payment_type', 'send_type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'probability' => '概率',
            'oppty' => '抽奖次数',
            'type' => '1.天 2 用户',
            'title' => '小程序标题',
            'rule' => '规则',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'deplete_integral_num' => '消耗积分',
            'send_type' => '发货方式',
            'payment_type' => '支付方式',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'bg_pic' => '背景图',
        ];
    }

    public function getList()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => CommonScratch::getSetting(),
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        if (!$this->payment_type || empty($this->payment_type)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写支付方式'
            ];
        }
        if (!$this->send_type || empty($this->send_type)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写发货方式'
            ];
        }
        $this->payment_type = \Yii::$app->serializer->encode($this->payment_type);
        $this->send_type = \Yii::$app->serializer->encode($this->send_type);

        $model = ScratchSetting::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            $model = new ScratchSetting();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
