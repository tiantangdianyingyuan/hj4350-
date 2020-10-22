<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\lottery\forms\common\CommonLottery;
use app\plugins\lottery\forms\common\CommonOption;
use app\plugins\lottery\models\LotterySetting;

class LotterySettingForm extends Model
{
    public $type;
    public $rule;
    public $title;
    public $send_type;
    public $payment_type;
    public $goods_poster;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $cs_status;
    public $cs_prompt_pic;
    public $cs_wechat;
    public $cs_wechat_flock_qrcode_pic;

    public function rules()
    {
        return [
            [['type', 'is_sms', 'is_mail', 'is_print', 'cs_status'], 'integer'],
            [['rule'], 'string'],
            [['title', 'cs_prompt_pic'], 'string', 'max' => 255],
            [['payment_type', 'send_type'], 'safe'],
            [['goods_poster', 'cs_wechat_flock_qrcode_pic', 'cs_wechat'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '0：分享即送 1： 被分享人参与抽奖',
            'title' => '小程序标题',
            'rule' => '规则',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'goods_poster' => '自定义海报',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'cs_status' => '是否开启客服提示',
            'cs_prompt_pic' => '客服提示图片',
            'cs_wechat' => '客服微信号',
            'cs_wechat_flock_qrcode_pic' => 'Cs Wechat Flock Qrcode Pic',
        ];
    }

    public function getList()
    {
        $setting = CommonLottery::getSetting();
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting,
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
                'msg' => '请先填写支付方式'
            ];
        }

        if (!$this->send_type || empty($this->send_type)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请先填写发货方式'
            ];
        }

        $model = LotterySetting::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$model) {
            $model = new LotterySetting();
        }

        $model->attributes = $this->attributes;
        $model->payment_type = \Yii::$app->serializer->encode($this->payment_type);
        $model->send_type = \Yii::$app->serializer->encode($this->send_type);
        $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
        $model->cs_wechat = \Yii::$app->serializer->encode($this->cs_wechat);
        $model->cs_wechat_flock_qrcode_pic = \Yii::$app->serializer->encode($this->cs_wechat_flock_qrcode_pic);
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
