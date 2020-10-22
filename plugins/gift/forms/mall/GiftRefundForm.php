<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/11/7
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\mall;


use app\core\response\ApiCode;
use app\models\GoodsAttr;
use app\models\Model;
use app\models\User;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;
use app\plugins\gift\models\GiftUserOrder;

class GiftRefundForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];

    }

    //中奖未领，手动退款
    public function refundGift()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $user_order = GiftUserOrder::find()->alias('guo')
                ->select(['guo.*', 'go.goods_id', 'go.goods_attr_id', 'go.num'])
                ->rightJoin(['go' => GiftOrder::tableName()], 'go.user_order_id = guo.id')
                ->andWhere(['guo.id' => $this->id, 'guo.is_delete' => 0, 'guo.is_receive' => 0, 'guo.is_turn' => 0,
                    'go.order_id' => 0, 'go.is_delete' => 0])
                ->asArray()->all();

            if (empty($user_order)) {
                throw new \Exception('礼物退款——礼物信息有误');
            }
            $gift_log = GiftLog::findOne($user_order[0]['gift_id']);
            //一个人大礼包，等于全退款
            if ($gift_log['open_type'] == 0) {
                $order = GiftSendOrder::find()
                    ->andWhere(['gift_id' => $user_order[0]['gift_id'], 'is_pay' => 1, 'is_delete' => 0, 'is_refund' => 0])
                    ->with('detail')
                    ->asArray()->all();
                foreach ($order as $value) {
                    $refund_price = 0;
                    foreach ($value['detail'] as $item) {
                        $price = $item['total_price'];
                        if ($price < 0) {
                            throw new \Exception('礼物退款——退款金额发生错误');
                        }
                        $refund_price += $price;
                        $detail_model = GiftSendOrderDetail::findOne($item['id']);
                        $detail_model->refund_price = $price;
                        $detail_model->is_refund = 1;
                        $detail_model->receive_num = 0;
                        if (!$detail_model->save()) {
                            throw new \Exception($detail_model->errors[0]);
                        }
                        //回退库存
                        $goods_info = \Yii::$app->serializer->decode($detail_model->goods_info);
                        $attr = GoodsAttr::findOne($goods_info['goods_attr']['id']);
                        $attr->updateStock(bcsub($item['num'], $item['receive_num']), 'add');
                    }
                    $order_model = GiftSendOrder::findOne($value['id']);
                    if ($refund_price > 0) {
                        $re = \Yii::$app->payment->refund($value['order_no'], $refund_price);
                        if ($re) {
                            GiftOrder::updateAll(['is_refund' => 1], ['user_order_id' => $this->id]);
                            $order_model->is_refund = 1;
                            if (!$order_model->save()) {
                                throw new \Exception($order_model->errors[0]);
                            }
                            $user = User::find()->where(['id' => $value['user_id']])->with('userInfo')->one();
                            CommonGift::sendRefundMsg(['order_no' => $value['order_no'], 'name' => '礼物', 'user' => $user], $refund_price, '礼物退款');
                        }
                    }
                }
            } else {
                foreach ($user_order as $v) {
                    $refund_price = 0;
                    $order = GiftSendOrder::find()->alias('o')
                        ->leftJoin(['od' => GiftSendOrderDetail::tableName()], 'od.send_order_id = o.id')
                        ->where(['o.gift_id' => $v['gift_id'], 'od.goods_id' => $v['goods_id'], 'od.goods_attr_id' => $v['goods_attr_id'],
                            'o.is_pay' => 1, 'o.is_delete' => 0, 'od.is_delete' => 0])
                        ->select('o.user_id,od.id,o.order_no,od.total_price,od.num')
                        ->asArray()->one();
                    $price = bcmul(bcdiv($order['total_price'], $order['num']), $v['num']);
                    if ($price < 0) {
                        throw new \Exception('礼物退款——退款金额发生错误');
                    }
                    $refund_price += $price;
                    $detail_model = GiftSendOrderDetail::findOne($order['id']);
                    $detail_model->refund_price += $price;
                    $detail_model->is_refund = 1;
                    $detail_model->receive_num -= $v['num'];
                    if (!$detail_model->save()) {
                        throw new \Exception($detail_model->errors[0]);
                    }
                    $order_model = GiftSendOrder::findOne($detail_model->send_order_id);
                    if ($refund_price > 0) {
                        $re = \Yii::$app->payment->refund($order['order_no'], $refund_price);
                        if ($re) {
                            GiftOrder::updateAll(['is_refund' => 1], ['user_order_id' => $this->id]);
                            $order_model->is_refund = 1;
                            if (!$order_model->save()) {
                                throw new \Exception($order_model->errors[0]);
                            }
                            $user = User::find()->where(['id' => $order['user_id']])->with('userInfo')->one();
                            CommonGift::sendRefundMsg(['order_no' => $order['order_no'], 'name' => '礼物', 'user' => $user], $refund_price, '礼物退款');
                        }
                    }
                }
            }

            if (!CommonGift::giftOver($gift_log->id)) {
                \Yii::error('礼物订单结束失败');
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '退款成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}