<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/14
 * Time: 10:01
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\models\Mall;
use app\models\Share;
use app\models\ShareOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property ShareOrder $shareOrder
 * 分销订单总数：
 * 按订单来算即一个订单最多算一个分销订单；
 * 取消的订单不算分销订单；
 * 当订单中有商品发生退款时，
 * 若订单中其他商品还有分销商品则仍算一个分销订单，
 * 若没有则不算分销订单；
 * ---------------------
 * 分销总佣金：
 * 订单取消或退款时，需要将相应商品的佣金减去；
 */
class ChangeShareOrderJob extends BaseObject implements JobInterface
{
    public $mall;
    public $shareOrder;
    public $before;
    public $order;
    public $beforeList;
    public $type = 'add';
    private $allOrder; // 是否计算总订单

    public function execute($queue)
    {
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->search('first_parent_id', 'first_price');
            $this->search('second_parent_id', 'second_price');
            $this->search('third_parent_id', 'third_price');
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('分销记录：');
            \Yii::error($exception);
        }
    }

    /**
     * @param $idKey
     * @param $priceKey
     * @throws \Exception
     */
    public function search($idKey, $priceKey)
    {
        if ($this->order) {
            $shareOrderList = ShareOrder::find()->where([
                'mall_id' => $this->order->mall_id,
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'is_delete' => 0,
            ])->all();
            $beforeList = $this->beforeList;
            $this->allOrder = true;
        } else {
            $shareOrderList = [$this->shareOrder];
            $beforeList = [$this->before];
            $other = ShareOrder::find()->where([
                'mall_id' => $this->order->mall_id,
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'is_delete' => 0,
            ])->andWhere(['!=', 'id', $this->shareOrder->id])->all();
            if (empty($other)) {
                // 没有其他的分销订单，需要计算总订单
                $this->allOrder = true;
            } else {
                $this->allOrder = false;
            }
        }
        foreach ($shareOrderList as $shareOrder) {
            if ($shareOrder[$idKey]) {
                if ($this->type === 'add') {
                    $beforeUserId = 0;
                    $beforeMoney = 0;
                    if (!empty($beforeList)) {
                        $before = null;
                        foreach ($beforeList as $before) {
                            if ($before['id'] == $shareOrder['id']) {
                                $beforeUserId = $before[$idKey];
                                $beforeMoney = $before[$priceKey];
                                break;
                            }
                        }
                    }
                    $this->add($shareOrder[$idKey], $shareOrder[$priceKey], $beforeUserId, $beforeMoney);
                } elseif ($this->type === 'sub') {
                    $this->sub($shareOrder[$idKey], $shareOrder[$priceKey]);
                } else {
                    throw new \Exception('错误的状态');
                }
            }
        }
    }

    /**
     * @param $afterUserId
     * @param $afterMoney
     * @param int $beforeUserId
     * @param int $beforeMoney
     * @throws \Exception
     * 增加分销商分销订单总数及总佣金
     */
    public function add($afterUserId, $afterMoney, $beforeUserId = 0, $beforeMoney = 0)
    {
        $after = Share::findOne(['user_id' => $afterUserId]);
        if ($beforeUserId) {
            if ($afterUserId == $beforeUserId) {
                $afterMoney = floatval($after->all_money) + $afterMoney - $beforeMoney;
            } else {
                $before = Share::findOne(['user_id' => $beforeUserId]);
                $beforeMoney = floatval($before->all_money) - min(floatval($before->all_money), $beforeMoney);
                $before->all_money = price_format($beforeMoney);
                if ($this->allOrder) {
                    $before->all_order = intval($before->all_order - min($before->all_order, 1));
                    $after->all_order += 1;
                }
                if (!$before->save()) {
                    throw new \Exception('分销保存出错');
                }
                $afterMoney = floatval($after->all_money) + $afterMoney;
            }
        } else {
            if ($this->allOrder) {
                $after->all_order += 1;
            }
            $afterMoney = floatval($after->all_money) + $afterMoney;
        }
        $after->all_money = price_format($afterMoney);
        if (!$after->save()) {
            throw new \Exception('分销保存出错');
        }
        $this->allOrder = false;
    }

    /**
     * @param $userId
     * @param $money
     * @throws \Exception
     * 减少分销商分销订单总数及总佣金
     */
    public function sub($userId, $money)
    {
        $before = Share::findOne(['user_id' => $userId]);
        $beforeMoney = floatval($before->all_money) - min(floatval($before->all_money), $money);
        $before->all_money = price_format($beforeMoney);
        if ($this->allOrder) {
            $before->all_order = intval($before->all_order - min($before->all_order, 1));
        }
        if (!$before->save()) {
            throw new \Exception('分销保存出错');
        }
        $this->allOrder = false;
    }
}
