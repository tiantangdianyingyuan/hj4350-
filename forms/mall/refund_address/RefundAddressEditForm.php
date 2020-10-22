<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\refund_address;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\RefundAddress;
use app\validators\PhoneNumberValidator;

class RefundAddressEditForm extends Model
{
    public $name;
    public $address;
    public $address_detail;
    public $mobile;
    public $remark;
    public $id;

    public function rules()
    {
        return [
            [['name', 'address', 'mobile'], 'required'],
            [['name', 'mobile', 'remark', 'address_detail'], 'string'],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '收件人名称',
            'address' => '省市区',
            'address_detail' => '收件人详细地址',
            'mobile' => '收件人手机号',
            'remark' => '备注',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $refundAddress = RefundAddress::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                ]);

                if (!$refundAddress) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $refundAddress = new RefundAddress();
                $refundAddress->mall_id = \Yii::$app->mall->id;
                $refundAddress->mch_id = \Yii::$app->user->identity->mch_id;
            }

            $refundAddress->name = $this->name;
            $refundAddress->address = \Yii::$app->serializer->encode($this->address);
            $refundAddress->address_detail = $this->address_detail;
            $refundAddress->mobile = $this->mobile;
            $refundAddress->remark = $this->remark;
            $res = $refundAddress->save();

            if (!$res) {
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
                    'line' => $e->getLine(),
                    'code' => $e->getMessage(),
                ]
            ];
        }
    }
}
