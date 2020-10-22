<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 13:50
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\jobs;


use app\forms\common\ecard\CommonEcard;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\Code;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property User $user
 * @property BargainGoods $bargainGoods
 */
class BargainOrderSubmitJob extends BaseObject implements JobInterface
{
    public $mall;
    public $user;
    public $bargainGoods;
    public $token;

    /**
     * @param \yii\queue\Queue $queue
     * @throws \Exception
     */
    public function execute($queue)
    {
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->bargainGoods = CommonBargainGoods::getCommonGoods()->getGoods($this->bargainGoods->goods_id);
            if ($this->bargainGoods->stock <= 0) {
                throw new Exception('砍价活动商品库存不足');
            }
            if ($this->bargainGoods->goods->status == 0) {
                throw new Exception('砍价活动已关闭');
            }
            $beginTime = strtotime($this->bargainGoods->begin_time);
            $endTime = strtotime($this->bargainGoods->end_time);
            $nowDate = time();
            if ($beginTime >= $nowDate) {
                throw new Exception('砍价活动尚未开始');
            }
            if ($endTime <= $nowDate) {
                throw new Exception('砍价活动已结束');
            }
            $common = CommonBargainOrder::getCommonBargainOrder($this->mall);
            /* @var BargainOrder $bargainOrder */
            $bargainOrder = $common->getUserOrder($this->bargainGoods->id, $this->user->id);
            if ($bargainOrder && $bargainOrder->resetTime > 0) {
                throw new \Exception('已存在进行中的砍价');
            }

            /* @var Goods $goods */
            $goods = $this->bargainGoods->goods;

            /* @var BargainOrder[] $bargainOrderSuccess */
            $bargainOrderSuccess = $common->getBargainOrderSuccess($this->bargainGoods->id, $this->user->id);
            if ($goods->confine_count > 0
                && $bargainOrderSuccess
                && count($bargainOrderSuccess) >= $goods->confine_count) {
                throw new Exception('已达砍价活动限制数量');
            }
            $statusData = (array)\Yii::$app->serializer->decode($this->bargainGoods->status_data);
            $bargainOrder = new BargainOrder();
            $bargainOrder->mall_id = $this->mall->id;
            $bargainOrder->user_id = $this->user->id;
            $bargainOrder->bargain_goods_id = $this->bargainGoods->id;
            $bargainOrder->token = $this->token;
            $bargainOrder->price = $this->bargainGoods->goods->price;
            $bargainOrder->min_price = $this->bargainGoods->min_price;
            $bargainOrder->status = Code::BARGAIN_PROGRESS;
            $bargainOrder->time = $this->bargainGoods->time;
            $bargainOrder->is_delete = 0;
            $bargainOrder->created_at = mysql_timestamp();
            $bargainOrder->bargain_goods_data = \Yii::$app->serializer->encode(array_merge([
                'min_price' => $this->bargainGoods->min_price,
                'begin_time' => $this->bargainGoods->begin_time,
                'end_time' => $this->bargainGoods->end_time,
                'time' => $this->bargainGoods->time,
                'type' => $this->bargainGoods->type,
                'stock' => $this->bargainGoods->stock,
                'id' => $this->bargainGoods->id,
                'price' => $this->bargainGoods->goods->price,
                'stock_type' => $this->bargainGoods->stock_type,
            ], $statusData));
            if (!$bargainOrder->save()) {
                throw new Exception((new Model())->getErrorMsg($bargainOrder));
            }
            if ($this->bargainGoods->stock_type == 1) {
                $this->bargainGoods->stock -= 1;
                // 砍价参与减库存时，占用卡密数据
                CommonEcard::getCommon()->occupy($this->bargainGoods->goods, 1);
            }
            $this->bargainGoods->initiator += 1;
            $this->bargainGoods->underway += 1;
            if (!$this->bargainGoods->save()) {
                throw new Exception((new Model())->getErrorMsg($this->bargainGoods));
            }
            \Yii::$app->queue->delay($bargainOrder->resetTime)->push(new BargainOrderTimeJob([
                'bargainOrder' => $bargainOrder
            ]));
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error($exception->getMessage());
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $exception->getMessage();
            $orderSubmitResult->save();
            throw $exception;
        }
    }
}
