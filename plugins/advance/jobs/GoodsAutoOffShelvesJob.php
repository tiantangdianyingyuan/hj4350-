<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 11:12
 */

namespace app\plugins\advance\jobs;

use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\plugins\advance\models\AdvanceGoods;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class GoodsAutoOffShelvesJob extends BaseObject implements JobInterface
{
    /**@var AdvanceGoods $advanceGoods**/
    public $advanceGoods;

    public function execute($queue)
    {
        if (!$this->advanceGoods) {
            return;
        }

        $mall = Mall::findOne(['id' => $this->advanceGoods->mall_id]);
        \Yii::$app->setMall($mall);

        $goods = Goods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->advanceGoods->goods_id,
            'is_delete' => 0
        ]);

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        if ($this->advanceGoods->is_delete == 1 || $this->advanceGoods->pay_limit == -1 || $goods->status == 0) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods->status = 0;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($goods));
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error("商品自动下架:" . $e->getMessage() . $e->getFile() . $e->getLine());
            throw new \Exception($e->getMessage());
        };
    }
}