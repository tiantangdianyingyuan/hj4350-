<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;
use app\plugins\booking\models\BookingSetting;

class BookingSettingEditForm extends Model
{
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_cat;
    public $form_data;
    public $is_form;
    public $payment_type;
    public $goods_poster;
    public $is_coupon;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $is_territorial_limitation;

    public function rules()
    {
        return [
            [['is_cat'], 'required'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_cat', 'is_form', 'is_coupon', 'is_member_price',
                'is_integral', 'svip_status', 'is_territorial_limitation', 'is_full_reduce'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_cat', 'is_form', 'is_coupon',  'is_member_price',
                'is_integral', 'svip_status'], 'required'],
            [['form_data', 'goods_poster'], 'trim'],
            [['payment_type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_share' => '是否开启分销',
            'is_sms' => '是否开启短信通知',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_cat' => '开启分类',
            'form_data' => 'form默认表单',
            'payment_type' => '支付方式',
            'goods_poster' => '自定义海报',
            'is_coupon' => '使用优惠券',
            'is_member_price' => '是否启用会员价',
            'is_integral' => '是否使用积分',
            'svip_status' => '超级会员卡',
            'is_full_reduce' => '是否参加满减活动',
            'is_territorial_limitation' => '是否开启区域允许购买'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();

            $array = [
                'is_share' => $this->is_share,
                'is_sms' => $this->is_sms,
                'is_mail' => $this->is_mail,
                'is_print' => $this->is_print,
                'is_cat' => $this->is_cat,
                'form_data' => json_encode($this->form_data),
                'is_form' => $this->is_form,
                'payment_type' => json_encode($this->payment_type),
                'goods_poster' => \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster)),
                'is_coupon' => $this->is_coupon,
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_full_reduce' => $this->is_full_reduce,
                'is_territorial_limitation' => $this->is_territorial_limitation
            ];

            $result = CommonOption::set('booking_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
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
            ];
        }
    }

    // 检测数据
    public function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请选择支付方式');
        }

        if (!$this->form_data) {
            return;
        }
        foreach ($this->form_data as $item) {
            if (!$item['name']) {
                throw new \Exception('请填写表单组件名称');
            }
            if (isset($item['list'])) {
                foreach ($item['list'] as $item2) {
                    if (!$item2['label']) {
                        throw new \Exception('请检查信息是否填写完整');
                    }
                }
            }
        }
    }
}
