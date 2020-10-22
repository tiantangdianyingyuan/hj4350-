<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 9:44
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;
use app\plugins\advance\forms\common\BannerListForm;
use app\plugins\advance\forms\common\CommonOption;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceBanner;

class AdvanceSettingForm extends Model
{
    public $id;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $payment_type;
    public $deposit_payment_type;
    public $goods_poster;
    public $over_time;
    public $is_advance;
    public $is_coupon;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $banner_ids;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'over_time', 'is_advance'
                , 'is_coupon', 'is_member_price', 'is_integral', 'svip_status'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation',
                'payment_type', 'deposit_payment_type', 'is_coupon', 'is_member_price', 'is_integral', 'svip_status', 'is_full_reduce'], 'required'],
            [['payment_type', 'goods_poster', 'deposit_payment_type', 'send_type', 'banner_ids'], 'safe'],
            [['is_advance',], 'default', 'value' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'payment_type' => '尾款支付方式',
            'deposit_payment_type' => '定金支付方式',
            'send_type' => '发货方式',
            'is_coupon' => '使用优惠券',
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
                'is_share' => $this->is_share,
                'is_sms' => $this->is_sms,
                'is_mail' => $this->is_mail,
                'is_print' => $this->is_print,
                'is_territorial_limitation' => $this->is_territorial_limitation,
                'send_type' => \Yii::$app->serializer->encode($this->send_type),
                'goods_poster' => \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster)),
                'payment_type' => json_encode($this->payment_type),
                'deposit_payment_type' => \Yii::$app->serializer->encode($this->deposit_payment_type),
                'over_time' => $this->over_time,
                'is_advance' => $this->is_advance,
                'is_coupon' => $this->is_coupon,
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_full_reduce' => $this->is_full_reduce
            ];

            $result = \app\forms\common\CommonOption::set('advance_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }

            $this->saveBanner();
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
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

    public function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            throw new \Exception('请填写支付方式');
        }
    }

    public function getSetting()
    {
        $setting = (new SettingForm())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }

    // 保存拼团轮播图
    private function saveBanner()
    {
        if (!$this->banner_ids) {
            return;
        }
        $this->banner_ids = json_decode($this->banner_ids, true);
        if (!is_array($this->banner_ids)) {
            throw new \Exception('轮播图数据需为数组');
        }
        AdvanceBanner::updateAll(
            ['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')],
            ['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]
        );

        foreach ($this->banner_ids as $id) {
            $form = new AdvanceBanner();
            $form->banner_id = $id;
            $form->mall_id = \Yii::$app->mall->id;
            $form->is_delete = 0;
            if (!$form->save()) {
                throw new \Exception($this->getErrorMsg($form));
            }
        }
    }
}
