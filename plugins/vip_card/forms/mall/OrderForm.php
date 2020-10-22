<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/23
 * Time: 16:45
 */

namespace app\plugins\vip_card\forms\mall;


use app\forms\mall\order\BaseOrderForm;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\User;
use app\models\UserInfo;
use app\plugins\vip_card\models\Order;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardOrder;

class OrderForm extends BaseOrderForm
{
    protected function getFieldsList()
    {
        return (new OrderExport())->fieldsList();
    }

    /**
     * @param BaseActiveQuery $query
     * @return BaseActiveQuery mixed
     */
    protected function getExtraWhere($query)
    {
        return $query->joinWith(['order.detail od' => function ($query) {
            if ($this->keyword && $this->keyword_1 == 4) {
                $query->where(['like', 'od.name', $this->keyword]);
            }
        }])->andWhere([
            'o.sign' => 'vip_card',
            'o.is_pay' => 1,
            'o.is_sale' => 1,
            'o.is_confirm' => 1
        ]);
    }

    protected function where()
    {
        $query = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.mch_id' => 0,
            'o.is_delete' => 0,
        ])
            ->leftJoin(['u' => User::tableName()], 'u.id = o.user_id')
            ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = o.user_id');

        $query->keyword($this->platform, ['ui.platform' => $this->platform]);

        $query->keyword($this->status == -1, ['AND', ['o.is_recycle' => 0], ['not', ['o.cancel_status' => 1]]])
            ->keyword($this->status == 0, [
                'AND',
                ['o.is_pay' => 0, 'o.is_recycle' => 0],
                ['not', ['o.cancel_status' => 1]]
            ])
            ->keyword($this->status == 1, [
                'AND',
                ['o.is_recycle' => 0, 'o.is_send' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['not', ['o.cancel_status' => 1]]
            ])
            ->keyword($this->status == 2, [
                'AND',
                ['o.is_send' => 1, 'o.is_confirm' => 0, 'o.is_recycle' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['not', ['o.cancel_status' => 1]]
            ])
            ->keyword($this->status == 3, [
                'AND',
                ['o.is_send' => 1, 'o.is_confirm' => 1, 'o.is_recycle' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['not', ['o.cancel_status' => 1]]
            ])
            ->keyword($this->status == 4, [
                'AND',
                ['o.cancel_status' => 2, 'o.is_recycle' => 0], ['not', ['o.cancel_status' => 1]]
            ])
            ->keyword($this->status == 5, ['o.is_recycle' => 0, 'o.cancel_status' => 1])
            ->keyword($this->status == 7, ['o.is_recycle' => 1])->keyword($this->sign, ['o.sign' => $this->sign]);

        if ($this->user_id) {
            $query->andWhere(['o.user_id' => $this->user_id]);
        }

        if ($this->date_start) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end]);
        }

        if ($this->keyword) {
            if ($this->keyword_1 == 1) {
                $query->andWhere(['like', 'o.order_no', $this->keyword]);
            }
            if ($this->keyword_1 == 2) {
                $query->andWhere(['like', 'u.nickname', $this->keyword]);
            }
            if ($this->keyword_1 == 3) {
                $query->andWhere(['u.id' => $this->keyword]);
            }
        }
        return $query;
    }

    protected function getExtra($order)
    {
        $cardOrder = VipCardOrder::findOne(['order_id' => $order['id']]);
        if (empty($cardOrder)) {
            return [
                'card_detail_name' => ''
            ];
        }
        return [
            'card_detail_name' => $cardOrder->detail_name ?? ''
        ];
    }
}