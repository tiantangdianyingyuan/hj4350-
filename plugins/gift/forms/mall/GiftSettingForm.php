<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Option;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\forms\common\CommonOption;
use app\models\Model;

class GiftSettingForm extends Model
{
    public $title;
    public $type;
    public $auto_refund;
    public $auto_remind;
    public $bless_word;
    public $ask_gift;

    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $payment_type;
    public $send_type;
    public $poster;

    public $background;
    public $theme;
    public $explain;

    public $is_coupon = 1;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $is_territorial_limitation;


    public function rules()
    {
        return [
            [['auto_refund', 'auto_remind', 'is_share', 'is_sms', 'is_mail', 'is_print',
                'is_coupon', 'is_member_price', 'is_integral', 'svip_status', 'is_territorial_limitation',
                'is_full_reduce'], 'integer'],
            [['poster'], 'trim'],
            [['payment_type', 'type', 'theme', 'background', 'send_type'], 'safe'],
            [['title', 'bless_word', 'ask_gift', 'explain'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => '标题',
            'type' => '玩法',
            'auto_refund' => '自动退款时间',
            'auto_remind' => '未领提醒时间',
            'bless_word' => '送礼祝福语',
            'ask_gift' => '求礼物话术',
            'is_share' => '是否开启分销',
            'is_sms' => '是否开启短信通知',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'payment_type' => '支付方式',
            'poster' => '自定义海报',
            'background_pic' => '背景图',
            'theme' => '主题',
            'is_coupon' => '是否使用优惠券',
            'is_member_price' => '是否启用会员价',
            'is_integral' => '是否使用积分',
            'svip_status' => '超级会员卡',
            'is_full_reduce' => '是否参加满减活动',
            'is_territorial_limitation' => '是否允许区域购买'
        ];
    }

    public function getList()
    {
        $setting = CommonGift::getSetting();
        $setting['poster'] = (new CommonOptionP())->poster($setting['poster'], CommonOption::getPosterDefault());
        $setting['default']['poster'] = (new CommonOptionP())->poster($setting['default']['poster'], CommonOption::getPosterDefault());
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();

            $this->background['left'] = floatval($this->background['left']);
            $this->background['top'] = floatval($this->background['top']);

            $array = [
                'type' => \Yii::$app->serializer->encode($this->type),
                'title' => $this->title,
                'auto_refund' => $this->auto_refund,
                'auto_remind' => $this->auto_remind,
                'bless_word' => $this->bless_word,
                'ask_gift' => $this->ask_gift,
                'is_share' => $this->is_share,
                'is_sms' => $this->is_sms,
                'is_print' => $this->is_print,
                'payment_type' => \Yii::$app->serializer->encode($this->payment_type),
                'mall_id' => \Yii::$app->mall->id,
                'poster' => \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->poster)),
                'background' => \Yii::$app->serializer->encode($this->background),
                'theme' => \Yii::$app->serializer->encode($this->theme),
                'send_type' => \Yii::$app->serializer->encode($this->send_type),
                'explain' => $this->explain,
                'is_territorial_limitation' => $this->is_territorial_limitation,
                'is_coupon' => $this->is_coupon,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_full_reduce' => $this->is_full_reduce,
                'is_member_price' => $this->is_member_price,
            ];
            $result = \app\forms\common\CommonOption::set('gift_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
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

    private function checkData()
    {
        if (!$this->type || empty($this->type)) {
            throw new \Exception('请选择玩法');
        }
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请选择支付方式');
        }
        if (!$this->send_type || empty($this->send_type)) {
            throw new \Exception('请选择发货方式');
        }
        if ($this->auto_refund <= 0 && $this->auto_refund != -1) {
            throw new \Exception('自动退款天数不能为0');
        }
        if ($this->auto_remind == 0 && $this->auto_remind != -1) {
            throw new \Exception('送礼未成功提醒天数不能为0');
        }
    }
}
