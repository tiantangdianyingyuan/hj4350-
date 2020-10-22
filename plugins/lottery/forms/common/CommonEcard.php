<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/6
 * Time: 10:21
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\lottery\forms\common;


use app\forms\common\ecard\CheckGoods;
use app\models\EcardOptions;
use app\models\EcardOrder;
use app\plugins\lottery\models\Goods;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\models\LotteryLog;

class CommonEcard extends \app\forms\common\ecard\CommonEcard
{
    /**
     * @param Lottery $lottery
     * @param LotteryLog[] $lotteryLog
     * @throws \Exception
     */
    public function setEcardLottery($lottery, $lotteryLog)
    {
        $num = count($lotteryLog);
        $ecard = $this->getEcard($lottery->goods->goodsWarehouse->ecard_id);
        /* @var EcardOptions[] $list */
        $list = $this->getEcardOptionsByOccupy($ecard, $num);
        if (count($list) != $num) {
            throw new \Exception('卡密数据库存不足');
        }
        $data = [];
        $table = LotteryLog::tableName();
        $logSql = "UPDATE {$table} SET `token` = CASE `id` ";
        $lotteryLogId = implode(',', array_column($lotteryLog, 'id'));
        $model = new EcardOrder();
        foreach ($list as $index => $item) {
            $token = \Yii::$app->security->generateRandomString();
            $logSql .= sprintf("WHEN %d THEN '%s' ", $lotteryLog[$index]['id'], $token);
            $data[] = [
                'id' => null,
                'mall_id' => \Yii::$app->mall->id,
                'ecard_id' => $item->ecard_id,
                'value' => $item->value,
                'order_id' => 0,
                'order_detail_id' => 0,
                'is_delete' => 0,
                'token' => $item->token,
                'ecard_options_id' => $item->id,
                'user_id' => $lotteryLog[$index]['user_id'],
                'order_token' => $token,
            ];
        }
        $logSql .= "END WHERE `id` IN ($lotteryLogId)";
        \Yii::$app->db->createCommand($logSql)->execute();
        \Yii::$app->db->createCommand()->batchInsert(
            EcardOrder::tableName(),
            array_keys($model->attributes),
            $data
        )->execute();
        $ecardOPtionsIds = array_column($list, 'id');
        EcardOptions::updateAll(['is_sales' => 1], ['id' => $ecardOPtionsIds]);
        $this->updateStock($ecard);
        $this->log(new CheckGoods([
            'ecard' => $ecard,
            'status' => CheckGoods::STATUS_SALES,
            'sign' => 'lottery',
            'number' => $num,
            'goods_id' => $lottery->goods_id,
        ]));
    }
}
