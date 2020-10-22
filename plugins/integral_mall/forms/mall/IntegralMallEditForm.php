<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;

class IntegralMallEditForm extends Model
{
    public $id;
    public $desc;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $payment_type;
    public $goods_poster;
    public $is_coupon;
    public $rule;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_coupon'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_coupon'], 'required'],
            [['payment_type', 'desc', 'goods_poster', 'send_type'], 'safe'],
            [['rule'], 'trim'],
            [['rule'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'desc' => "积分说明",
            'is_coupon' => "是否使用优惠券",
            'rule' => '积分说明',
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
                'desc' => $this->desc ? json_encode($this->desc) : json_encode([]),
                'payment_type' => json_encode($this->payment_type),
                'send_type' => json_encode($this->send_type),
                'goods_poster' => \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster)),
                'rule' => $this->rule,
            ];
            $result = CommonOption::set('integral_mall_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }

            $transaction->commit();
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

    public function checkData()
    {
        if (!$this->payment_type || empty($this->payment_type)) {
            $this->payment_type = ['online_pay'];
        }
        if ($this->desc) {
            foreach ($this->desc as $key => $item) {
                if (!$item['title']) {
                    throw new \Exception('请完善积分说明标题');
                }
                if (!$item['content']) {
                    throw new \Exception('请完善积分说明内容');
                }
            }
        }
    }
}
