<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;

use app\forms\common\order\CommonOrder;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\forms\common\share\AddShareOrder;
use app\forms\common\share\CommonShare;
use app\jobs\OrderCancelJob;
use app\models\Cart;
use app\models\User;

/**
 * @property User $user
 */
abstract class BaseOrderCreatedHandler extends BaseOrderHandler
{
    public $user;

    protected function setAutoCancel()
    {
        $orderAutoCancelMinute = \Yii::$app->mall->getMallSettingOne('over_time');
        if (is_numeric($orderAutoCancelMinute) && $orderAutoCancelMinute > 0) {
            // 订单自动取消任务
            \Yii::$app->queue->delay($orderAutoCancelMinute * 60)->push(new OrderCancelJob([
                'orderId' => $this->event->order->id,
            ]));
            $autoCancelTime = strtotime($this->event->order->created_at) + $orderAutoCancelMinute * 60;
            $this->event->order->auto_cancel_time = mysql_timestamp($autoCancelTime);
            $this->event->order->save();
        }
        return $this;
    }

    protected function setShareUser()
    {
        try {
            $commonShare = new CommonShare();
            $commonShare->mall = \Yii::$app->mall;
            $commonShare->user = \Yii::$app->user->identity;
            $commonShare->bindParent($commonShare->user->userInfo->temp_parent_id, 2);
        } catch (\Exception $exception) {
            \Yii::error('首次下单成为下级：' . $exception->getMessage());
        }
        return $this;
    }

    protected function setShareMoney()
    {
        try {
            $this->saveShareMoney();
        } catch (\Exception $exception) {
            \Yii::error('分销佣金记录失败：' . $exception->getMessage());
        }
        return $this;
    }

    protected function saveShareMoney()
    {
        (new AddShareOrder())->save($this->event->order);
    }

    protected function setPrint()
    {
        $orderConfig = $this->orderConfig;

        try {
            if ($orderConfig->is_print != 1) {
                throw new PrintException($this->event->order->sign . '未开启小票打印');
            }
            (new PrintOrder())->print($this->event->order, $this->event->order->id, 'order');
        } catch (PrintException $exception) {
            \Yii::error("小票打印打印出错：" . $exception->getMessage());
        }
        return $this;
    }

    /**
     * 购物车商品购买后删除
     */
    protected function deleteCartGoods()
    {
        $res = Cart::updateAll(['is_delete' => 1], ['id' => $this->event->cartIds]);
    }
}
