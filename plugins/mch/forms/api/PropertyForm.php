<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\order\CommonOrderList;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCash;
use app\plugins\mch\models\MchOrder;
use yii\helpers\ArrayHelper;

class PropertyForm extends Model
{
    public $mch_id;
    public $is_transfer;
    public $date;

    public function rules()
    {
        return [
            [['mch_id'], 'required'],
            [['mch_id', 'is_transfer'], 'integer'],
            [['is_transfer'], 'default', 'value' => 0],
            [['date'], 'string']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $mch = Mch::findOne($this->mch_id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            $form = new CommonOrderList();
            $form->mch_id = $this->mch_id;
            $form->is_pagination = 0;
            $form->is_mch_order = 1;
            $form->is_refund = 1;
            $list = $form->search();

            $closeMoney = 0;
            $notCloseMoney = 0;
            /** @var Order $item */
            foreach ($list as $item) {
                if ($item->mchOrder->is_transfer) {
                    $closeMoney += $item->total_pay_price;
                    /** @var OrderRefund $rItem */
                    foreach ($item->refund as $rItem) {
                        if ($rItem->is_refund > 0) {
                            if ($rItem->reality_refund_price > 0) {
                                $closeMoney = $closeMoney - $rItem->reality_refund_price;
                            } else {
                                $closeMoney = $closeMoney - $rItem->refund_price;
                            }
                        }
                    }
                } else {
                    $notCloseMoney += $item->total_pay_price;
                }
            }

            $desc = '商户手续费为' . $mch->transfer_rate . '/1000,即每笔成交订单可收入的金额' .
                ' = 订单支付金额 * (1-' . $mch->transfer_rate . '/1000)。';

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'account_money' => $mch->account_money,
                    'transfer_rate' => $mch->transfer_rate,
                    'close_money' => price_format($closeMoney),
                    'not_close_money' => price_format($notCloseMoney),
                    'desc' => $desc
                ]
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

    public function getAccountLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = MchAccountLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
        ]);

//        if (!$this->date) {
//            return [
//                'code' => ApiCode::CODE_ERROR,
//                'msg' => '请传入date参数'
//            ];
//        }
        if ($this->date) {
            $dateArr = $this->getTheMonth($this->date);
            $query->andWhere(['>=', 'created_at', $dateArr[0] . ' 00:00:00']);
            $query->andWhere(['<=', 'created_at', $dateArr[1] . ' 23:59:59']);
        }

        $list = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getCashLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = MchCash::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
            'is_delete' => 0
        ]);

//        if (!$this->date) {
//            return [
//                'code' => ApiCode::CODE_ERROR,
//                'msg' => '请传入date参数'
//            ];
//        }
        if ($this->date) {
            $dateArr = $this->getTheMonth($this->date);
            $query->andWhere(['>=', 'created_at', $dateArr[0] . ' 00:00:00']);
            $query->andWhere(['<=', 'created_at', $dateArr[1] . ' 23:59:59']);
        }

        $list = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getOrderCloseLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $orderIds = MchOrder::find()->where([
            'is_transfer' => $this->is_transfer
        ])->select('order_id');

        $list = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
            'id' => $orderIds,
            'is_delete' => 0
        ])
            ->with('mchOrder', 'detail.goods.goodsWarehouse', 'refund')
            ->page($pagination)
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /** @var Order $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $goodsName = '';
            foreach ($item->detail as $dItem) {
                $goodsName .= $dItem->goods->goodsWarehouse->name . '|';
            }
            $newItem['goods_name'] = substr($goodsName, 0, strlen($goodsName) - 1);
            $newItem['order_status_text'] = (new Order())->orderStatusText($item);
            $price = $item->total_pay_price;
            /** @var OrderRefund $rItem */
            foreach ($item->refund as $rItem) {
                if ($rItem->is_refund > 0) {
                    if ($rItem->reality_refund_price > 0) {
                        $price = $price - $rItem->reality_refund_price;
                    } else {
                        $price = $price - $rItem->refund_price;
                    }
                }
            }
            $newItem['total_pay_price'] = price_format($price);
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 获取月份的第一天和最后一天
     * @param $date
     * @return array
     */
    public function getTheMonth($date)
    {
        $firstDay = date('Y-m-01', strtotime($date));
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
        return array($firstDay, $lastDay);
    }
}
