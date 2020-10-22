<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/27
 * Time: 11:12
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\models\Express;
use app\models\Model;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\models\UserInfo;
use app\plugins\advance\events\DepositEvent;
use app\plugins\advance\models\AdvanceOrder;

class DepositOrderForm extends Model
{
    public $id;
    public $status;
    public $date_start;
    public $date_end;
    public $keyword;
    public $keyword_1;
    public $platform;
    public $page;
    public $flag;
    public $fields;
    public $remark;

    public function rules()
    {
        return [
            [['id', 'status', 'page', 'keyword_1',], 'integer'],
            [['platform', 'flag', 'remark',], 'string'],
            [['status',], 'default', 'value' => -1],
            [['page',], 'default', 'value' => 1],
            [['fields'], 'safe'],
            [['date_start', 'date_end', 'keyword',], 'trim'],
            [['id',], 'required', 'on' => ['detail', 'cancel', 'remark']],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new DepositOrderExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination)
            ->orderBy('o.created_at DESC')
            ->select(['o.*', 'u.nickname', 'ui.platform', 'po.refund'])
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
                'express_list' => Express::getExpressList(),
                'export_list' => $this->getFieldsList(),
            ]
        ];
    }

    protected function where()
    {
        $query = AdvanceOrder::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0
        ])
            ->leftJoin(['u' => User::tableName()], 'u.id = o.user_id')
            ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = o.user_id')
            ->leftJoin(['po' => PaymentOrder::tableName()], 'po.order_no = o.advance_no')
            ->with('user')
            ->with('goods.advanceGoods')
            ->with('goods.goodsWarehouse')
            ->with('goods.attr');

        $query->keyword($this->platform, ['ui.platform' => $this->platform]);

        $query->keyword($this->status == -1, ['AND', ['o.is_recycle' => 0, 'o.is_delete' => 0], ['not', ['o.is_cancel' => 1]]])
            ->keyword($this->status == 0, [
                'AND',
                ['o.is_pay' => 0, 'o.is_recycle' => 0, 'o.is_delete' => 0],
                ['not', ['o.is_cancel' => 1]],
            ])
            ->keyword($this->status == 1, [
                'AND',
                ['o.is_pay' => 1, 'o.is_recycle' => 0, 'o.is_delete' => 0],
                ['not', ['o.is_cancel' => 1]],
            ])
            ->keyword($this->status == 2,
                ['o.is_refund' => 1, 'o.is_recycle' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1]
            )
            ->keyword($this->status == 3, [
                    'AND',
                    ['o.is_recycle' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_refund' => 0, 'o.is_cancel' => 0],
                    ['not', ['o.order_id' => '0']],
                ]
            );

        if ($this->date_start) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end]);
        }

        if ($this->keyword) {
            switch ($this->keyword_1) {
                case 1:
                    $query->andWhere(['like', 'o.advance_no', $this->keyword]);
                    break;
                case 2:
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                    break;
                case 4:
                    $query->andWhere(['u.id' => $this->keyword]);
                    break;
                case 5:
                    $query->andWhere(['like', 'u.mobile', $this->keyword]);
                    break;
                case 6:
                    // 商户支付订单号
                    /** @var PaymentOrderUnion $paymentOrderUnion */
                    $paymentOrderUnion = PaymentOrderUnion::find()->where(['order_no' => $this->keyword])->with('paymentOrder')->one();
                    $orderNos = [];
                    if ($paymentOrderUnion) {
                        /** @var PaymentOrder $item */
                        foreach ($paymentOrderUnion->paymentOrder as $item) {
                            $orderNos[] = $item->order_no;
                        }
                    }
                    $query->andWhere(['order_no' => $orderNos]);
                    break;
                default:
            }

        }

        return $query;
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = AdvanceOrder::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ])
            ->with('user')
            ->with('goods.advanceGoods')
            ->with('goods.goodsWarehouse')
            ->with('goods.attr')
            ->asArray()
            ->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在',
            ];
        }

        //倒计时秒
        $order['auto_cancel'] = strtotime($order['auto_cancel_time']) - time();
        $order['ladder_rules'] = json_decode($order['goods']['advanceGoods']['ladder_rules'], true);
        $order['deposit_num'] = $order['deposit'] * $order['goods_num'];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order' => $order,
            ]
        ];
    }

    /**
     * @return array
     * 定金订单强制取消
     */
    public function cancel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order = AdvanceOrder::find()
                ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->one();

            if (!$order) {
                throw new \Exception('该订单不存在。');
            }

            if ($order->order_id != 0) {
                throw new \Exception('已付尾款订单无法退定金。');
            }

            $order->is_cancel = 1;

            if ($order->save()) {
                \Yii::$app->trigger(AdvanceOrder::EVENT_REFUND, new DepositEvent([
                    'advanceOrder' => $order
                ]));
                $t->commit();

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            } else {
                $t->rollBack();

                return $this->getErrorResponse($order);
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 未付款定金订单删除
     */
    public function del()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var AdvanceOrder $order */
            $order = AdvanceOrder::find()
                ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->one();

            if (!$order) {
                throw new \Exception('该订单不存在。');
            }

            if ($order->order_id != 0) {
                throw new \Exception('已付尾款订单无法删除。');
            }

            if ($order->is_pay != 0) {
                throw new \Exception('已付款订单无法删除。');
            }

            $order->is_delete = 1;

            if (!$order->save()) {
                throw new \Exception($this->getErrorResponse($order));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function remark()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = AdvanceOrder::find()
            ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }
        $order->remark = $this->remark;
        if ($order->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($order);
        }
    }

    protected function getFieldsList()
    {
        return (new DepositOrderExport())->fieldsList();
    }
}
