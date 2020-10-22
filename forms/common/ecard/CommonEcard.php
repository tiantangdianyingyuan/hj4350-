<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/20
 * Time: 11:21
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\ecard;

use app\events\OrderEvent;
use app\models\EcardOrder;
use app\models\Goods;
use app\models\Mall;
use app\models\Ecard;
use app\models\EcardOptions;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use yii\helpers\Json;

/**
 * Class Ecard
 * @package app\plugins\ecard\forms\common
 * @property Mall $mall
 */
class CommonEcard extends Model
{
    public $mall;
    public $ecardList = [];
    public static $ignore = ['gift', 'pick', 'composition', 'booking', 'advance']; // 电子卡密不支持的插件列表

    public static function getCommon($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $common = new static();
        $common->mall = $mall;
        return $common;
    }

    /**
     * @param $id
     * @return Ecard
     * @throws \Exception
     * 获取指定id的卡密
     */
    public function getEcard($id)
    {
        if (isset($this->ecardList[$id])) {
            return $this->ecardList[$id];
        }
        $model = Ecard::findOne(['id' => $id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$model) {
            throw new \Exception('该电子卡密已被删除');
        }
        $this->ecardList[$id] = $model;
        return $model;
    }

    /**
     * @param Ecard $ecard
     * @return array
     * @throws \Exception
     * 获取指定id的卡密
     */
    public function getEcardArray($ecard)
    {
        $list = Json::decode($ecard->list, true);
        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'key' => $item
            ];
        }
        return [
            'id' => $ecard->id,
            'name' => $ecard->name,
            'content' => $ecard->content,
            'is_unique' => $ecard->is_unique,
            'list' => $newList,
            'key' => $list[0] ?? ''
        ];
    }

    /**
     * @param $token
     * @param $ecard_id
     * @return array|\yii\db\ActiveRecord|null|EcardOptions
     * 获取指定token的卡密数据
     */
    public function getEcardData($token, $ecard_id)
    {
        $list = EcardOptions::find()->with('data')
            ->where(['token' => $token, 'mall_id' => $this->mall->id, 'is_delete' => 0, 'ecard_id' => $ecard_id])
            ->one();
        return $list;
    }

    /**
     * @param $token
     * @param $ecard_id
     * @return array|\yii\db\ActiveRecord[]|EcardOptions[]
     * 获取指定token的卡密数据
     */
    public function getEcardDataAll($token, $ecard_id)
    {
        $list = EcardOptions::find()->with('data')
            ->where(['token' => $token, 'mall_id' => $this->mall->id, 'is_delete' => 0, 'ecard_id' => $ecard_id])
            ->all();
        return $list;
    }

    /**
     * @param Ecard $ecard
     * @return Ecard
     * @throws \Exception
     * 更新卡密库存
     */
    public function updateStock($ecard)
    {
        $stock = EcardOptions::find()
            ->where([
                'ecard_id' => $ecard->id, 'mall_id' => $this->mall->id, 'is_delete' => 0, 'is_sales' => 0
            ])->count(1);
        $sales = EcardOptions::find()
            ->where([
                'ecard_id' => $ecard->id, 'mall_id' => $this->mall->id, 'is_sales' => 1
            ])->count(1);
        $preStock = EcardOptions::find()
            ->where([
                'ecard_id' => $ecard->id, 'mall_id' => $this->mall->id, 'is_sales' => 0, 'is_delete' => 0,
                'is_occupy' => 1
            ])->count(1);
        $ecard->pre_stock = $preStock;
        $ecard->sales = $sales;
        $ecard->stock = $stock;
        $ecard->total_stock = $sales + $stock;
        if (!$ecard->save()) {
            throw new \Exception($this->getErrorMsg($ecard));
        }
        return $ecard;
    }

    public function repeatData($list)
    {
        $string = '';
        if (is_array($list)) {
            foreach ($list as $item) {
                if (is_array($item)) {
                    foreach ($item as $key => $value) {
                        $string .= $key . ':' . $value . ' ';
                    }
                }
                $string .= '<br>';
            }
        }
        return $string;
    }

    public function getGoodsConfig()
    {
        return [
            'is_ecard' => 1,
            'ecard_url' => '/plugin/ecard/mall/index/index',
            'ecard_api_url' => '/plugin/ecard/api/index/list',
        ];
    }

    public function getEcardList()
    {
        /* @var Ecard[] $list */
        $list = Ecard::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->select('id,name,created_at,sales,stock')
            ->orderBy(['id' => SORT_DESC])
            ->all();
        return $list;
    }

    /**
     * @param CheckGoods $checkGoods
     * @return string|null
     */
    public function log($checkGoods)
    {
        return $checkGoods->save();
    }

    /**
     * @param $stock
     * @param Goods $goods
     * @return mixed
     * @throws \Exception
     * 电子卡密类商品，库存需要取'商品库存'和'卡密数量'的最小值
     */
    public function getEcardStock($stock, $goods)
    {
        if (!$goods instanceof Goods) {
            throw new \Exception('商品参数错误');
        }
        if ($goods->goodsWarehouse->type == 'ecard') {
            $ecard = $goods->goodsWarehouse->ecard;
            $stock = min($stock, max($ecard->stock - $ecard->pre_stock, 0));
        }
        return $stock;
    }

    public function getEcardStockByArray($stock, $goods)
    {
        if (isset($goods['goodsWarehouse']['type']) && $goods['goodsWarehouse']['type'] == 'ecard') {
            $ecard = $this->getEcard($goods['goodsWarehouse']['ecard_id']);
            $stock = min($stock, max($ecard->stock - $ecard->pre_stock, 0));
        }
        return $stock;
    }

    /**
     * @param array $params
     * @throws \Exception
     * 返还卡密信息
     * [
     * 'type' // 卡密返还类型 order--从订单中返还  order_token--从订单token中返还 occupy--从占用中返还
     * 'sign' // 插件类型
     * 'num' // 返还数量
     * 'goods_id' // 商品id
     * 'order' // 订单对象
     * 'order_token' // 订单表token
     * ]
     */
    public function refundEcard($params)
    {
        if (!isset($params['type'])) {
            throw new \Exception('所传参数不正确');
        }
        $log = [];
        switch ($params['type']) {
            case 'order_token':
                $condition = ['order_token' => $params['order_token'], 'is_delete' => 0];
                $log[] = [
                    'sign' => $params['sign'],
                    'num' => $params['num'],
                    'goods_id' => $params['goods_id']
                ];
                $ecardOrder = EcardOrder::findAll($condition);
                if (!$ecardOrder) {
                    return;
                }
                EcardOrder::updateAll(['is_delete' => 1], $condition);
                $token = array_column($ecardOrder, 'token');
                // 目前一个订单只可能是一种卡密
                $ecardIdList = array_column($ecardOrder, 'ecard_id');
                $ecardId = array_shift($ecardIdList);
                break;
            case 'order':
                /* @var Order $order */
                $order = $params['order'];
                $condition = ['order_id' => $order->id, 'is_delete' => 0];
                foreach ($order->detail as $detail) {
                    $log[] = [
                        'sign' => $detail->sign,
                        'num' => $detail->num,
                        'goods_id' => $detail->goods_id
                    ];
                }
                $ecardOrder = EcardOrder::findAll($condition);
                if (!$ecardOrder) {
                    return;
                }
                EcardOrder::updateAll(['is_delete' => 1], $condition);
                $token = array_column($ecardOrder, 'token');
                // 目前一个订单只可能是一种卡密
                $ecardIdList = array_column($ecardOrder, 'ecard_id');
                $ecardId = array_shift($ecardIdList);
                break;
            case 'occupy':
                $goods = Goods::findOne(['id' => $params['goods_id']]);
                if ($goods->goodsWarehouse->type != 'ecard') {
                    return ;
                }
                $ecardId = $goods->goodsWarehouse->ecard_id;
                $ecardOptions = EcardOptions::find()
                    ->where(['is_occupy' => 1, 'is_delete' => 0, 'is_sales' => 0, 'ecard_id' => $ecardId])
                    ->limit($params['num'])->all();
                if (!$ecardOptions || count($ecardOptions) != $params['num']) {
                    return;
                }
                $log[] = [
                    'sign' => $params['sign'],
                    'num' => $params['num'],
                    'goods_id' => $params['goods_id']
                ];
                $token = array_column($ecardOptions, 'token');
                break;
            default:
                throw new \Exception('所传参数不正确');
        }
        EcardOptions::updateAll(['is_sales' => 0, 'is_occupy' => 0], ['token' => $token]);
        $ecard = $this->getEcard($ecardId);
        $this->updateStock($ecard);
        foreach ($log as $item) {
            $this->log(new CheckGoods([
                'ecard' => $ecard,
                'status' => CheckGoods::STATUS_REFUND,
                'sign' => $item['sign'],
                'number' => $item['num'],
                'goods_id' => $item['goods_id'],
            ]));
        }
    }

    /**
     * @param $order
     * @return EcardOrder|array|mixed
     * 获取订单中电子卡密信息
     */
    public function getTypeData($order)
    {
        if ($order['status'] == 0 || $order['is_pay'] != 1) {
            return [];
        }
        /* @var EcardOrder[] $list */
        $list = EcardOrder::find()->where(['order_id' => $order['id'], 'is_delete' => 0])->all();
        $newList = array_reduce($list, function ($v1, $v2) {
            $v1[] = Json::decode($v2['value'], true);
            return $v1;
        }, []);
        return $newList;
    }

    /**
     * @param Order $order
     * 设置订单中的电子卡密信息
     */
    public function setTypeData($order)
    {
        \Yii::warning('设置电子卡密');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $orderDetailList = $order->detail;
            $count = 0;
            foreach ($orderDetailList as $orderDetail) {
                try {
                    $this->setEcard($orderDetail, $order);
                    $count++;
                } catch (\Exception $exception) {
                    \Yii::warning('不是虚拟商品');
                    \Yii::warning($exception);
                    continue;
                }
            }
            if ($count != count($orderDetailList)) {
                throw new \Exception('不全是虚拟商品');
            }
            if ($order->status == 1) {
                $this->autoSend($order);
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            \Yii::warning('卡密数据添加出错');
            \Yii::warning($exception);
            $transaction->rollBack();
        }
    }

    /**
     * @param Order $order
     * 自动发货
     */
    public function autoSend($order)
    {
        try {
            foreach ($order->detail as $detail) {
                $goodsInfo = Json::decode($detail->goods_info, true);
                if (!isset($goodsInfo['goods_attr']['ecard_id']) || $goodsInfo['goods_attr']['ecard_id'] == 0) {
                    throw new \Exception('不是卡密商品');
                }
            }
            $order->is_send = 1;
            $order->send_time = mysql_timestamp();
            $order->is_confirm = 1;
            $order->confirm_time = mysql_timestamp();
            $order->is_sale = 1;
            if ($order->save()) {
                \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent([
                    'order' => $order,
                ]));
                \Yii::$app->trigger(Order::EVENT_SALES, new OrderEvent([
                    'order' => $order,
                ]));
            }
        } catch (\Exception $exception) {
            \Yii::warning($exception);
        }
    }

    /**
     * @param OrderDetail $detail
     * @param Order $order
     * @throws \Exception
     * 订单支付时，设置卡密数据
     */
    protected function setEcard(OrderDetail $detail, $order)
    {
        $ecardOrder = EcardOrder::findOne(['order_token' => $order->token]);
        if ($ecardOrder) {
            $ecardOrder->order_id = $order->id;
            $ecardOrder->order_detail_id = $detail->id;
            $ecardOrder->save();
            $ecard = $this->getEcard($ecardOrder->ecard_id);
            $ecardOptionsIds = $ecardOrder->ecard_options_id;
        } else {
            $goodsInfo = Json::decode($detail->goods_info, true);
            if (!isset($goodsInfo['goods_attr']['ecard_id']) || $goodsInfo['goods_attr']['ecard_id'] == 0) {
                throw new \Exception('不是卡密商品');
            }
            $ecard = $this->getEcard($goodsInfo['goods_attr']['ecard_id']);
            /* @var EcardOptions[] $list */
            $list = $this->getEcardOptions($ecard, $detail->num);
            if (count($list) != $detail->num) {
                throw new \Exception('卡密数据库存不足');
            }
            $data = [];
            $model = new EcardOrder();
            foreach ($list as $item) {
                $data[] = [
                    'id' => null,
                    'mall_id' => \Yii::$app->mall->id,
                    'ecard_id' => $item->ecard_id,
                    'value' => $item->value,
                    'order_id' => $detail->order_id,
                    'order_detail_id' => $detail->id,
                    'is_delete' => 0,
                    'token' => $item->token,
                    'ecard_options_id' => $item->id,
                    'user_id' => $order->user_id,
                    'order_token' => $order->token,
                ];
            }
            \Yii::$app->db->createCommand()->batchInsert(
                EcardOrder::tableName(),
                array_keys($model->attributes),
                $data
            )->execute();
            $ecardOptionsIds = array_column($list, 'id');
        }
        EcardOptions::updateAll(['is_sales' => 1], ['id' => $ecardOptionsIds]);
        $this->updateStock($ecard);
        $this->log(new CheckGoods([
            'ecard' => $ecard,
            'status' => CheckGoods::STATUS_SALES,
            'sign' => $detail->sign,
            'number' => $detail->num,
            'goods_id' => $detail->goods_id,
        ]));
    }

    /**
     * @param $ecard
     * @param $num
     * @return EcardOptions[]
     * 获取指定卡密指定数量的可用数据
     */
    public function getEcardOptions($ecard, $num)
    {
        /* @var EcardOptions[] $list */
        $list = EcardOptions::find()
            ->where(['ecard_id' => $ecard->id, 'is_sales' => 0, 'is_delete' => 0, 'is_occupy' => 0])
            ->limit($num)
            ->all();
        return $list;
    }

    /**
     * @param $ecard
     * @param $num
     * @return EcardOptions[]
     * 从已占用的卡密数据中获取指定卡密指定数量
     */
    public function getEcardOptionsByOccupy($ecard, $num)
    {
        /* @var EcardOptions[] $list */
        $list = EcardOptions::find()
            ->where(['ecard_id' => $ecard->id, 'is_sales' => 0, 'is_delete' => 0, 'is_occupy' => 1])
            ->limit($num)
            ->all();
        return $list;
    }

    /**
     * @param $order
     * 通过占用卡密设置订单的卡密数据
     */
    public function setTypeDataOccupy($order)
    {
        \Yii::warning('通过占用卡密设置订单的卡密数据');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $orderDetailList = $order->detail;
            $count = 0;
            foreach ($orderDetailList as $orderDetail) {
                try {
                    $this->setEcardOccupy($orderDetail, $order);
                    $count++;
                } catch (\Exception $exception) {
                    \Yii::warning('不是虚拟商品');
                    \Yii::warning($exception);
                    continue;
                }
            }
            if ($count != count($orderDetailList)) {
                throw new \Exception('不全是虚拟商品');
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            \Yii::warning('卡密数据添加出错');
            \Yii::warning($exception);
            $transaction->rollBack();
        }
    }

    /**
     * @param OrderDetail $detail
     * @param Order $order
     * @throws \Exception
     * 订单下单时，占用卡密数据
     */
    public function setEcardOccupy($detail, $order)
    {
        $goodsInfo = Json::decode($detail->goods_info, true);
        if (!isset($goodsInfo['goods_attr']['ecard_id'])) {
            throw new \Exception('没有有效的商品库商品id');
        }
        $ecard = $this->getEcard($goodsInfo['goods_attr']['ecard_id']);
        /* @var EcardOptions[] $list */
        $list = $this->getEcardOptionsByOccupy($ecard, $detail->num);
        if (count($list) != $detail->num) {
            throw new \Exception('卡密数据库存不足');
        }
        $data = [];
        $model = new EcardOrder();
        foreach ($list as $item) {
            $data[] = [
                'id' => null,
                'mall_id' => \Yii::$app->mall->id,
                'ecard_id' => $item->ecard_id,
                'value' => $item->value,
                'order_id' => $detail->order_id,
                'order_detail_id' => $detail->id,
                'is_delete' => 0,
                'token' => $item->token,
                'ecard_options_id' => $item->id,
                'user_id' => $order->user_id,
                'order_token' => $order->token,
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert(
            EcardOrder::tableName(),
            array_keys($model->attributes),
            $data
        )->execute();
    }
    /**
     * @param Goods $goods
     * @param $num
     * @return bool
     * @throws \Exception
     * 占用卡密库存
     */
    public function occupy($goods, $num)
    {
        if ($goods->goodsWarehouse->type !== 'ecard') {
            \Yii::warning('不是卡密商品');
            return false;
        }
        $ecard = $this->getEcard($goods->goodsWarehouse->ecard_id);
        /* @var EcardOptions[] $list */
        $list = $this->getEcardOptions($ecard, $num);
        if (count($list) != $num) {
            throw new \Exception('卡密数据库存不足');
        }
        if ($num + $ecard->pre_stock > $ecard->stock) {
            throw new \Exception('卡密数据库存不足');
        }
        EcardOptions::updateAll(['is_occupy' => 1], ['id' => array_column($list, 'id')]);
        $this->updateStock($ecard);
        $this->log(new CheckGoods([
            'ecard' => $ecard,
            'status' => CheckGoods::STATUS_OCCUPY,
            'sign' => $goods->sign,
            'number' => $num,
            'goods_id' => $goods->id,
        ]));
    }

    public function getSupportEcard()
    {
        $corePlugin = \Yii::$app->plugin->list;
        $res = [];
        foreach ($corePlugin as $item) {
            $name = $item->name;
            $Class = 'app\\plugins\\' . $name . '\\Plugin';
            if (!class_exists($Class)) {
                continue;
            }
            /* @var \app\plugins\Plugin $object */
            $object = new $Class();
            if ($object->supportEcard()) {
                $res[] = $name;
            }
        }
        return $res;
    }
}
