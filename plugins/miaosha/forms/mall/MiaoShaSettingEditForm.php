<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;

class MiaoShaSettingEditForm extends Model
{
    public $over_time;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $goods_poster;
    public $is_coupon;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $is_territorial_limitation;
    public $is_offer_price;

    public function rules()
    {
        return [
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'over_time', 'is_coupon', 'is_member_price',
                'is_integral', 'svip_status', 'is_territorial_limitation', 'is_full_reduce', 'is_offer_price'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'over_time', 'is_coupon', 'is_member_price',
                'is_integral', 'svip_status', 'goods_poster', 'is_territorial_limitation', 'is_full_reduce'], 'required'],
            [['goods_poster'], 'safe'],
            [['over_time'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_share' => '是否开启分销状态',
            'is_sms' => '是否开启短信状态',
            'is_mail' => '是否开启邮件状态',
            'is_print' => '是否开启打印状态',
            'over_time' => '未支付订单取消时间',
            'goods_poster' => '自定义海报',
            'is_territorial_limitation' => '区域允许购买',
            'is_coupon' => "是否使用优惠券",
            'is_member_price' => '是否启用会员价',
            'is_integral' => '是否使用积分',
            'svip_status' => '超级会员卡',
            'is_full_reduce' => '是否参加满减活动',
            'is_offer_price' => '是否开启起送规则',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->over_time < 0) {
                throw new \Exception('未支付订单时间不能小于0');
            }

            $maxMinute = 30 * 24 * 60;
            if ($this->over_time > $maxMinute) {
                throw new \Exception('未支付订单时间不能大于' . $maxMinute);
            }

            $array = [
                'over_time' => $this->over_time,
                'is_mail' => $this->is_mail,
                'is_print' => $this->is_print,
                'is_share' => $this->is_share,
                'is_sms' => $this->is_sms,
                'goods_poster' => \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster)),
                'is_coupon' => $this->is_coupon,
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_full_reduce' => $this->is_full_reduce,
                'is_territorial_limitation' => $this->is_territorial_limitation,
				'is_offer_price' => $this->is_offer_price
            ];
            $result = CommonOption::set('miaosha_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
