<?php

namespace app\plugins\pintuan\jobs\v2;

use app\models\Model;
use app\plugins\pintuan\models\Goods;
use yii\base\Component;
use yii\queue\JobInterface;

class PintuanGoodsJob extends Component implements JobInterface
{
    public $goodsId;

    public function execute($queue)
    {
        \Yii::warning('拼团商品自动下架开始');
        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $this->goodsId])->with('pintuanGoods')->one();
        if (!$goods) {
            \Yii::warning('拼团商品不存在');
            return false;
        }

        if ($goods->pintuanGoods && $goods->pintuanGoods->end_time != '0000-00-00 00:00:00') {
            $endTime = strtotime($goods->pintuanGoods->end_time) - time();
            if ($endTime <= 0) {
                $goods->status = 0;
                $res = $goods->save();
                if (!$res) {
                    \Yii::warning((new Model())->getErrorMsg($goods));
                    return false;
                }
                \Yii::warning('拼团商品自动下架执行完成');
            }
        }
    }
}
