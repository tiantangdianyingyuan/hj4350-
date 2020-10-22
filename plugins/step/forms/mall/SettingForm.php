<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\step\forms\common\CommonOption;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\jobs\StepRemindJob;
use app\plugins\step\models\StepSetting;

class SettingForm extends Model
{
    public $convert_max;
    public $convert_ratio;
    public $currency_name;
    public $activity_pic;
    public $ranking_pic;
    public $qrcode_pic;
    public $invite_ratio;
    public $remind_at;
    public $rule;
    public $activity_rule;
    public $ranking_num;
    public $title;
    public $share_title;
    public $share_pic;
    public $qrcode_title;
    public $send_type;
    public $payment_type;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $step_poster;
    public $goods_poster;

    public function rules()
    {
        return [
            [['convert_max', 'convert_ratio', 'invite_ratio', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['rule', 'activity_rule','share_pic', 'share_title'], 'string', 'max' => 2000],
            [['qrcode_title'], 'string', 'min' => 0, 'max' => 12],
            [['title', 'remind_at', 'currency_name', 'ranking_pic', 'activity_pic'], 'string', 'max' => 255],
            [['invite_ratio'], 'integer','min' => 0, 'max' => 1000],
            [['convert_ratio'], 'integer','min' => 1, 'max' => 999999999],
            [['convert_max', 'ranking_num'], 'integer','min' => 0, 'max' => 999999999],
            [['qrcode_pic', 'step_poster', 'goods_poster'], 'trim'],
            [['convert_ratio', 'remind_at', 'invite_ratio', 'qrcode_pic'], 'required'],
            [['ranking_num', 'convert_max', 'send_type'], 'default', 'value' => 0],
            [['title', 'currency_name', 'activity_rule', 'qrcode_title', 'share_title', 'share_pic', 'activity_pic', 'ranking_pic', 'rule'], 'default', 'value' => ''],
            [['payment_type', 'send_type'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'convert_max' => '每日最高兑换数',
            'convert_ratio' => '兑换比率',
            'currency_name' => '活力币别名',
            'activity_pic' => '活动背景',
            'ranking_pic' => '排行榜背景',
            'qrcode_pic' => '海报缩略图',
            'invite_ratio' => '邀请比率',
            'remind_at' => '提醒时间',
            'rule' => '活动规则',
            'activity_rule' => '活动规则',
            'ranking_num' => '全国排行限制',
            'title' => '小程序标题',
            'share_title' => '转发标题',
            'share_pic' => '转发图片',
            'qrcode_title' => '海报文字',
            'send_type' => '发货方式',
            'payment_type' => '支付方式',
            'is_share' => '是否开启分销',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'is_territorial_limitation' => '是否开启区域允许购买',
            'step_poster' => '步数宝海报',
            'goods_poster' => '商品海报',
        ];
    }

    public function getList()
    {
        $setting = CommonStep::getSetting();
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        $setting['step_poster'] = (new CommonOptionP())->poster($setting['step_poster'], CommonOption::getStepPosterDefault());

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $setting,
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        try {
            if (!$this->payment_type || empty($this->payment_type)) {
                throw new \Exception('请填写支付方式');
            }
            if (!$this->send_type || empty($this->send_type)) {
                throw new \Exception('请填写发货方式');
            }
            $this->payment_type = \Yii::$app->serializer->encode($this->payment_type);
            $this->send_type = \Yii::$app->serializer->encode($this->send_type);

            $model = StepSetting::findOne([
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$model) {
                $model = new StepSetting();
            }
            if ($this->step_poster['poster_bg']['is_show'] && !$this->step_poster['poster_bg']['file_path']) {
                throw new \Exception('分享海报标识图片不能为空');
            }
            if ($this->goods_poster['poster_bg']['is_show'] && !$this->goods_poster['poster_bg']['file_path']) {
                throw new \Exception('商品海报标识图片不能为空');
            }
            $model->attributes = $this->attributes;
            $model->step_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->step_poster));
            $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $model->mall_id = \Yii::$app->mall->id;
            if ($model->save()) {
                $time = strtotime($model->remind_at) - time() + 10;
                if ($time < 0) {
                    $time +=24*60*60;
                }
                $id = \Yii::$app->queue->delay($time)->push(new StepRemindJob([
                    'model' => $model,
                ]));
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            } else {
                throw new \Exception($this->getErrorMsg($model));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
