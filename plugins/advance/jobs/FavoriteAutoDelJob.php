<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/9/6
 * Email: <657268722@qq.com>
 */

namespace app\plugins\advance\jobs;

use app\models\Favorite;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FavoriteAutoDelJob extends BaseObject implements JobInterface
{
    public $goods_id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::error('预售商品自动删除收藏开始，ID:' . $this->goods_id);
        if (!$this->goods_id) {
            return;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $count = Favorite::updateAll(['is_delete' => 1], ['goods_id' => $this->goods_id]);
            \Yii::error('预售商品收藏删除' . $count, '条');
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error("预售商品到期自动删除收藏夹:" . $exception->getMessage() . $exception->getFile() . $exception->getLine());
        }
    }
}