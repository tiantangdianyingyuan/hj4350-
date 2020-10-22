<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/21
 * Time: 11:28
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\Share;
use app\models\ShareOrder;
use app\models\ShareSetting;
use app\models\UserInfo;

class InitDataForm extends Model
{
    public function search()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->goods()->share()->plugin();
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return $this
     * @throws \yii\db\Exception
     * 商品统计字段冗余
     */
    public function goods()
    {
        $query = Order::find()->with(['detail.goods'])->where([
            'is_pay' => 1, 'cancel_status' => 0, 'mall_id' => \Yii::$app->mall->id
        ]);

        $count = $query->count();
        $goodsList = [];
        $limit = 10000;
        for ($i = 0; $i <= $count; $i += $limit) {
            /* @var Order[] $list */
            $list = $query->limit($limit)->offset($i)->all();
            foreach ($list as $order) {
                foreach ($order->detail as $detail) {
                    if (!isset($goodsList[$detail['goods_id']])) {
                        $goodsList[$detail['goods_id']] = [
                            'payment_num' => 0,
                            'payment_amount' => 0,
                            'user_list' => [],
                            'order_list' => [],
                            'class' => $detail->goods
                        ];
                    }
                    $goodsList[$detail['goods_id']]['payment_num'] += $detail->num;
                    $goodsList[$detail['goods_id']]['payment_amount'] += floatval($detail->total_price);
                    if (!in_array($order->user_id, $goodsList[$detail['goods_id']]['user_list'])) {
                        array_push($goodsList[$detail['goods_id']]['user_list'], $order->user_id);
                    }
                    if (!in_array($order->id, $goodsList[$detail['goods_id']]['order_list'])) {
                        array_push($goodsList[$detail['goods_id']]['order_list'], $order->id);
                    }
                }
            }
            $ids = implode(',', array_keys($goodsList));
            $table = Goods::tableName();
            $sql = "UPDATE {$table} SET `payment_num` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $goods['payment_num']);
            }
            $sql .= "END, `payment_people` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, count($goods['user_list']));
            }
            $sql .= "END, `payment_order` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, count($goods['order_list']));
            }
            $sql .= "END, `payment_amount` = CASE `id` ";
            foreach ($goodsList as $id => $goods) {
                $sql .= sprintf("WHEN %d THEN %.2f ", $id, $goods['payment_amount']);
            }
            $sql .= "END WHERE `id` IN ($ids)";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     * 插件统计字段冗余
     */
    public function plugin()
    {
        $corePluginList = \Yii::$app->plugin->getList();
        foreach ($corePluginList as $corePlugin) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($corePlugin->name);
            } catch (\Exception $exception) {
                continue;
            }
            $plugin->initData();
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     * 分销统计字段冗余
     */
    public function share()
    {
        $level = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
        if ($level <= 0) {
            Share::updateAll([
                'first_children' => 0,
                'all_children' => 0,
                'all_order' => 0,
                'all_money' => 0,
            ], ['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            return $this;
        }
        $limit = 10000;
        $query = Share::find()->with(['thirdChildren'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1]);
        $offset = 0;
        while (true) {
            $shareList = $query->limit($limit)->offset($offset)->all();
            if (!$shareList || empty($shareList)) {
                break;
            }
            $offset += $limit;
            $shareUserId = array_column($shareList, 'user_id');
            $shareOrderList = ShareOrder::find()->where([
                'mall_id' => \Yii::$app->mall->id, 'is_refund' => 0, 'is_delete' => 0
            ])->andWhere([
                'or',
                ['first_parent_id' => $shareUserId],
                ['second_parent_id' => $shareUserId],
                ['third_parent_id' => $shareUserId],
            ])->select([
                'SUM(first_price) as first_price_total', 'SUM(second_price) as second_price_total',
                'SUM(third_price) as third_price_total', 'first_parent_id', 'second_parent_id', 'third_parent_id'
            ])->groupBy(['order_id'])->asArray()->all();
            $newList = [];
            /* @var Share[] $shareList */
            foreach ($shareList as $share) {
                if (!isset($newList[$share->id])) {
                    $newItem = [
                        'first_children' => 0,
                        'all_children' => 0,
                        'all_order' => 0,
                        'all_money' => 0,
                    ];
                }
                $newItem['first_children'] = count($share->firstChildren);
                $newItem['all_children'] += count($share->firstChildren);
                if ($level > 1) {
                    $newItem['all_children'] += count($share->secondChildren);
                    if ($limit > 2) {
                        $newItem['all_children'] += count($share->thirdChildren);
                    }
                }
                foreach ($shareOrderList as $item) {
                    if ($item['first_parent_id'] == $share->user_id) {
                        $newItem['all_money'] += $item['first_price_total'];
                        $newItem['all_order'] += 1;
                    }
                    if ($item['second_parent_id'] == $share->user_id) {
                        $newItem['all_money'] += $item['second_price_total'];
                        $newItem['all_order'] += 1;
                    }
                    if ($item['third_parent_id'] == $share->user_id) {
                        $newItem['all_money'] += $item['third_price_total'];
                        $newItem['all_order'] += 1;
                    }
                }
                $newList[$share->id] = $newItem;
            }
            $ids = implode(',', array_keys($newList));
            $table = Share::tableName();
            $sql = "UPDATE {$table} SET `first_children` = CASE `id` ";
            foreach ($newList as $id => $item) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $item['first_children']);
            }
            $sql .= "END, `all_children` = CASE `id` ";
            foreach ($newList as $id => $item) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $item['all_children']);
            }
            $sql .= "END, `all_order` = CASE `id` ";
            foreach ($newList as $id => $item) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $item['all_order']);
            }
            $sql .= "END, `all_money` = CASE `id` ";
            foreach ($newList as $id => $item) {
                $sql .= sprintf("WHEN %d THEN %.2f ", $id, $item['all_money']);
            }
            $sql .= "END WHERE `id` IN ($ids)";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        return $this;
    }
}
