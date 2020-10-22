<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 11:21
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;

class CardEditForm extends Model
{
    public $name;
    public $cover;
    public $type;
    public $type_info;
    public $discount;
    public $is_free_delivery;
    public $is_discount;
    public $status;
    public $vip_id;
    public $detail;

    public function rules()
    {
        return [
            [['cover', 'name'], 'required'],
            [['discount', 'is_free_delivery', 'is_discount', 'vip_id'], 'number', 'min' => 0],
            [['detail', 'type_info'], 'safe'],
            [['status'], 'default', 'value' => 0],
            [['discount'], 'number', 'max' => 9.9],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '会员卡名称',
            'cover' => '会员卡样式',
            'type_info' => '指定类型',
            'is_free_delivery' => '包邮开关',
            'is_discount' => '折扣开关',
            'discount' => '会员卡折扣'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $card = CommonVip::getCommon()->getMainCard();
            if (!$card) {
                $card = new VipCard();
            }

            $card->name = $this->name;
            $card->mall_id = \Yii::$app->mall->id;
            $card->cover = $this->cover;
            $card->type = $this->type ?? '1';
            $card->type_info = $this->type_info ?? '';
            $card->is_discount = $this->is_discount;
            if ($this->is_discount) {
                if (!is_numeric($this->discount) && empty($this->discount)) {
                    throw  new \Exception('请填写折扣');
                }

                if ($this->discount < 0 || $this->discount >= 10) {
                    throw new \Exception('折扣信息错误，折扣范围必须是`0 ≤ 折扣 < 10`。');
                }
                $card->discount = $this->discount;
            }
            $card->is_free_delivery = $this->is_free_delivery ? $this->is_free_delivery : '0';
            $card->status = $this->status;
            $res = $card->save();

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
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
