<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api;

use app\forms\api\order\OrderException;
use app\models\OrderDetail;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $this->setSign((new Plugin())->getName());
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        $this->setSupportPayTypes($mallPaymentTypes);

        return $this;
    }

    public function checkGoods($goods, $item)
    {
        if ($goods->sign != (new Plugin())->getName()) {
            return parent::checkGoods($goods, $item);
        }

        $miaoshaGoods = MiaoshaGoods::findOne(['goods_id' => $goods->id]);
        if (!$miaoshaGoods) {
            throw new OrderException('秒杀商品不存在');
        }
        if ($miaoshaGoods->open_date != date('Y-m-d') || $miaoshaGoods->open_time != date('H')) {
            throw new OrderException('秒杀活动未开始或已结束');
        }

        $buyCount = OrderDetail::find()->where([
            'goods_id' => $miaoshaGoods->goods_id,
        ])->joinWith(['order' => function ($query) {
            $query->andWhere([
                'user_id' => \Yii::$app->user->id,
                'is_pay' => 1
            ]);
        }])->groupBy('order_id')->count();

        if ($goods->confine_order_count != -1 && $buyCount >= $goods->confine_order_count) {
            throw new OrderException('超出购买限单(' . $goods->confine_order_count . ')次数');
        }
    }
}
