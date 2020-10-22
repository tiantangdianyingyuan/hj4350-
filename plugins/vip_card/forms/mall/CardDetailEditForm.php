<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/9
 * Time: 10:40
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardCards;
use app\plugins\vip_card\models\VipCardCoupons;
use app\plugins\vip_card\models\VipCardDetail;

class CardDetailEditForm extends Model
{
    public $id;
    public $name;
    public $send_integral_num;
    public $send_integral_type;
    public $send_balance;
    public $expire_day;
    public $price;
    public $num;
    public $status;
    public $cards;
    public $coupons;
    public $vip_id;
    public $detail_id;
    public $detail;
    public $title;
    public $content;

    public function rules()
    {
        return [
            [['vip_id', 'expire_day', 'price', 'title', 'content'] , 'required'],
            [['id'], 'integer'],
            [['send_integral_num', 'send_integral_type', 'send_balance', 'vip_id', 'price', 'num'], 'number', 'min' => 0],
            [['expire_day'], 'number', 'min' => 1,'tooSmall' => '{attribute}不能小于{min}天'],
            [['detail', 'cards', 'coupons',], 'safe'],
            [['status'], 'default', 'value' => 0],
            [['name', 'title', 'content'], 'string', 'max' => 255],
            [['num', 'price', 'send_integral_num', 'send_balance'], 'number', 'max' => 9999999],
            [['expire_day',], 'number', 'max' => 2000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => '使用说明',
            'content' => '内容',
            'expire_day' => '有效时长',
            'num' => '库存',
            'price' => '价格',
            'send_integral_num' => '赠送积分',
            'send_balance' => '赠送余额',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $detail = VipCardDetail::findOne(['mall_id' => \Yii::$app->mall->id,'id' => $this->id, 'vip_id' => $this->vip_id, 'is_delete' => 0]);

                if (!$detail) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $detail = new VipCardDetail();
            }

            if (mb_strlen($this->title) > 8) {
                throw new \Exception('最多输入8个字符');
            }

            $detail->mall_id = \Yii::$app->mall->id;
            $detail->name = $this->name;
            $detail->vip_id = $this->vip_id;
            $detail->send_balance = $this->send_balance ?: 0;
            $detail->send_integral_num = $this->send_integral_num ?: 0;
            $detail->expire_day = $this->expire_day;
            $detail->price = $this->price;
            $detail->num = $this->num;
            $detail->status = $this->status;
            $detail->title = $this->title;
            $detail->content = $this->content;
            $res = $detail->save();
            $this->detail_id = $detail->id;

            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }


            VipCardCards::updateAll(['is_delete' => 1], ['detail_id' => $detail->id]);
            if ($this->cards) {
                $this->saveCards();
            }

            VipCardCoupons::updateAll(['is_delete' => 1], ['detail_id' => $detail->id]);
            if ($this->coupons) {
                $this->saveCoupons();
            }

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

    private function saveCards()
    {
        $this->cards = json_decode($this->cards,true);
        foreach ($this->cards as $card) {

            $dataRuleCards = VipCardCards::find()->where(['detail_id' => $this->detail_id])->all();

            /** @var VipCardCards $cardsModel */
            $cardsModel = null;
            foreach ($dataRuleCards as $dataRuleCard) {
                if (isset($card['id']) && $dataRuleCard->id == $card['id']) {
                    $cardsModel = $dataRuleCard;
                    break;
                }
            }

            if (!$cardsModel) {
                $cardsModel = new VipCardCards();
            }
            $cardsModel->detail_id = $this->detail_id;
            $cardsModel->is_delete = 0;
            $cardsModel->card_id = $card['card_id'];
            $cardsModel->send_num = $card['send_num'];
            $res = $cardsModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($cardsModel));
            }
        }
    }

    private function saveCoupons()
    {
        $this->coupons = json_decode($this->coupons,true);
        foreach ($this->coupons as $coupon) {

            $dataRuleCoupons = VipCardCoupons::find()->where(['detail_id' => $this->detail_id])->all();

            /** @var VipCardCoupons $couponModel */
            $couponModel = null;
            foreach ($dataRuleCoupons as $dataRuleCoupin) {
                if (isset($coupon['id']) && $dataRuleCoupin->id == $coupon['id']) {
                    $couponModel = $dataRuleCoupin;
                    break;
                }
            }

            if (!$couponModel) {
                $couponModel = new VipCardCoupons();
            }
            $couponModel->detail_id = $this->detail_id;
            $couponModel->is_delete = 0;
            $couponModel->coupon_id = $coupon['coupon_id'];
            $couponModel->send_num = $coupon['send_num'];
            $res = $couponModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($couponModel));
            }
        }
    }
}
