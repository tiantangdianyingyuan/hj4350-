<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/2
 * Time: 16:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common;


use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;

class CommonGoods extends Model
{
    private static $instance;

    public static function getCommon()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDiyGoods()
    {
        $goodsWarehouseId = null;

        $query = Composition::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (isset($array['keyword']) && $array['keyword']) {
            $query->andWhere(['like', 'name', $array['keyword']]);
        }

        $list = $query->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods.goods.attr'])
            ->page($pagination)
            ->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        $newList = [];

        foreach ($list as $composition) {
            $coverPicList = [];
            /** @var $composition $item */
            foreach ($composition->compositionGoods as $item) {
                $coverPic = $item->goods->goodsWarehouse->cover_pic;
                $item['is_host'] ? array_unshift($coverPicList, $coverPic) : array_push($coverPicList, $coverPic);
            }

            $newItem = [
                'name' => $composition->name,
                'id' => $composition->id,
                'price' => $composition->price,
                'original_price' => '',
                'cover_pic_list' => $coverPicList,
                'created_at' => $composition->created_at,
                'type' => $composition->type,
            ];
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * @param Order $order
     * @param string $type 枚举值sub|add
     * @return boolean
     * @throws \Exception
     */
    public function setGoodsPayment($order, $type = 'sub')
    {
        if ($order->is_pay != 1) {
            \Yii::warning('未支付订单不需要修改信息');
            return true;
        }
        $goodsIdList = array_column($order->detail, 'goods_id');
        /* @var OrderDetail[] $list */
        $list = OrderDetail::find()->alias('od')->where(['od.goods_id' => $goodsIdList, 'od.is_delete' => 0])
            ->leftJoin(['o' => Order::tableName()], 'o.id = od.order_id')
            ->andWhere(['o.user_id' => $order->user_id, 'o.is_pay' => 1, 'o.sign' => 'composition'])
            ->andWhere(['!=', 'od.order_id', $order->id])
            ->groupBy('od.goods_id')
            ->all();
        $compositionGoodsIds = [];
        $goodsList = [];
        foreach ($order->detail as $detail) {
            $goodsInfo = json_decode($detail->goods_info, true);
            if (!isset($goodsInfo['goods_attr']['composition_goods_id'])) {
                continue;
            }
            $compositionGoodsId = $goodsInfo['goods_attr']['composition_goods_id'];
            $compositionGoodsIds[] = $compositionGoodsId;
            if (!isset($goodsList[$compositionGoodsId])) {
                $goodsList[$compositionGoodsId] = [
                    'num' => 0,
                    'total_price' => 0,
                    'goods_id' => $detail->goods_id,
                    'composition_goods_id' => $compositionGoodsId,
                ];
            }
            $goodsList[$compositionGoodsId]['num'] += $detail->num;
            $goodsList[$compositionGoodsId]['total_price'] += floatval($detail->total_price);
        }
        /* @var CompositionGoods[] $compositionGoodsList */
        $compositionGoodsList = CompositionGoods::find()
            ->where(['mall_id' => $order->mall_id, 'id' => $compositionGoodsIds])
            ->all();
        foreach ($compositionGoodsList as $compositionGoods) {
            $flag = true;
            foreach ($list as $item) {
                $goodsInfo = json_decode($item->goods_info, true);
                if (!isset($goodsInfo['goods_attr']['composition_goods_id'])) {
                    continue;
                }
                if ($goodsInfo['goods_attr']['composition_goods_id'] == $compositionGoods->id) {
                    $flag = false;
                    break;
                }
            }
            switch ($type) {
                case 'sub':
                    if ($flag) {
                        $compositionGoods->payment_people -= min($compositionGoods->payment_people, 1);
                    }
                    $compositionGoods->payment_num -= min($goodsList[$compositionGoods->id]['num'], $compositionGoods->payment_num);
                    $compositionGoods->payment_amount -= min($goodsList[$compositionGoods->id]['total_price'], floatval($compositionGoods->payment_amount));
                    break;
                case 'add':
                    if ($flag) {
                        $compositionGoods->payment_people += 1;
                    }
                    $compositionGoods->payment_num += $goodsList[$compositionGoods->id]['num'];
                    $compositionGoods->payment_amount += $goodsList[$compositionGoods->id]['total_price'];
                    break;
                default:
                    throw new \Exception('错误的type值');
            }
            if (!$compositionGoods->save()) {
                throw new \Exception($this->getErrorMsg($compositionGoods));
            }
        }
        return true;
    }
}
