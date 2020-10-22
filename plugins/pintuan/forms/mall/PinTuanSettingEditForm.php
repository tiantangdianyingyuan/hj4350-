<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;
use app\plugins\pintuan\models\PintuanBanners;

class PinTuanSettingEditForm extends Model
{
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $payment_type;
    public $goods_poster;
    public $advertisement;
    public $is_advertisement = 1;
    public $bannerIds;
    public $is_coupon = 1;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $new_rules;

    public function rules()
    {
        return [
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_advertisement', 'is_coupon',
                'is_member_price', 'is_integral', 'svip_status', 'is_full_reduce'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_advertisement',
                'is_coupon', 'is_member_price', 'is_integral', 'svip_status'], 'required'],
            [['payment_type', 'goods_poster', 'send_type', 'bannerIds', 'advertisement'], 'safe'],
            [['new_rules'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_share' => '是否开启分销状态',
            'is_sms' => '是否开启短信状态',
            'is_mail' => '是否开启邮件状态',
            'is_print' => '是否开启打印状态',
            'is_territorial_limitation' => '是否开启区域购买限制状态',
            'is_advertisement' => '是否开启拼团广告状态',
            'bannerIds' => '轮播图',
            'is_coupon' => '是否使用优惠券',
            'is_member_price' => '是否启用会员价',
            'is_integral' => '是否使用积分',
            'svip_status' => '超级会员卡',
            'is_full_reduce' => '是否参加满减活动'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();

            $array = [
                'is_mail' => $this->is_mail,
                'is_print' => $this->is_print,
                'is_share' => $this->is_share,
                'is_sms' => $this->is_sms,
                'is_coupon' => $this->is_coupon,
                'is_territorial_limitation' => $this->is_territorial_limitation,
                'new_rules' => $this->new_rules,
                'payment_type' => json_encode($this->payment_type),
                'send_type' => json_encode($this->send_type),
                'goods_poster' =>\Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster)),
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_advertisement' => $this->is_advertisement,
                'advertisement' => json_encode($this->advertisement),
                'is_full_reduce' => $this->is_full_reduce
            ];
            $result = CommonOption::set('pintuan_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }

            $this->saveBanner();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请选择支付方式');
        }

        try {
            $this->advertisement = \Yii::$app->serializer->decode($this->advertisement);
        } catch (\Exception $exception) {
            $this->advertisement = [];
        }

        if (!count($this->advertisement)) {
            throw new \Exception('请选择板块样式');
        }

        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请选择支付方式');
        }

        if (!$this->send_type || empty($this->send_type)) {
            throw new \Exception('请选择发货方式');
        }

        try {
            $this->goods_poster = json_decode($this->goods_poster, true);
        } catch (\Exception $exception) {
            $this->goods_poster = [];
        }
    }


    // 保存拼团轮播图
    private function saveBanner()
    {
        if (!is_array($this->bannerIds)) {
            // throw new \Exception('轮播图数据需为数组');
            $this->bannerIds = [];
        }
        PintuanBanners::updateAll(
            ['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')],
            ['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]
        );

        foreach ($this->bannerIds as $id) {
            $form = new PintuanBanners();
            $form->banner_id = $id;
            $form->mall_id = \Yii::$app->mall->id;
            $form->is_delete = 0;
            if (!$form->save()) {
                throw new \Exception($this->getErrorMsg($form));
            }
        }
    }
}
