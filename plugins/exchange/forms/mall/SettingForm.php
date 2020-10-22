<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonOption;
use app\plugins\exchange\forms\common\CommonSetting;

class SettingForm extends Model
{
    public $is_share = 0; //是否分销
    public $payment_type = ['online_pay', 'balance']; //支付方式 ['online_pay', 'balance']

    public $is_coupon = 0; //是否优惠券
    public $svip_status = 0; //是否超级会员卡
    public $is_member_price = 0; //是否会员价
    public $is_integral = 0; //是否积分抵扣

    public $is_rules = 0; //使用说明开关
    public $rules = ''; //是否使用说明

    public $is_anti_brush = 0; //防刷
    public $anti_brush_minute = 0; //N分
    public $exchange_error = 0; //兑换次数
    public $freeze_hour = 0;  //冻结小时

    public $is_limit = 0;       //限制兑换次数
    public $limit_user_num = 0; //每位用户每天限制兑换成功次数
    public $limit_user_success_num = 0; //每位用户永久兑换成功次数
    public $limit_user_type; //day //all

    public $is_to_exchange = 0;  //跳兑换
    public $is_to_gift = 0;  // 跳卡

    public $to_exchange_pic = '';
    public $to_gift_pic = '';

    public $poster = [];
    public $is_full_reduce;

    public function rules()
    {
        return [
            [['is_share', 'payment_type', 'is_coupon', 'svip_status', 'is_member_price', 'is_rules',
                'is_anti_brush', 'anti_brush_minute', 'exchange_error',
                'freeze_hour', 'is_limit', 'limit_user_num', 'is_to_exchange',
                'is_to_gift', 'limit_user_success_num', 'limit_user_type'], 'required'],
            [['payment_type', 'poster'], 'trim'],
            [['is_share', 'is_coupon', 'svip_status', 'is_member_price', 'is_integral', 'is_rules',
                'is_anti_brush', 'anti_brush_minute', 'exchange_error','freeze_hour', 'is_limit',
                'limit_user_num', 'is_to_exchange', 'is_to_gift', 'is_full_reduce', 'limit_user_success_num'], 'integer'],
            [['rules', 'to_exchange_pic', 'to_gift_pic', 'limit_user_type'], 'string'],
        ];
    }

    public function get()
    {
        $setting =  (new CommonSetting())->get();
        $setting['poster'] = (new CommonOptionP())->poster($setting['poster'], CommonOption::getPosterDefault());
        $default_poster = (new CommonOptionP())->poster([], CommonOption::getPosterDefault());
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'setting' => $setting,
                'default_poster' => $default_poster,
                'default_setting' => [
                    'to_exchange_pic' => (new CommonSetting())->getDefault()['to_exchange_pic'],
                    'to_gift_pic' => (new CommonSetting())->getDefault()['to_gift_pic'],
                ]
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $value = \yii\helpers\ArrayHelper::toArray($this->attributes);
        $value['poster'] = (new CommonOptionP())->saveEnd($this->poster);
        $data = (new CommonSetting())->set($value);
        if ($data) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        }
    }
}
