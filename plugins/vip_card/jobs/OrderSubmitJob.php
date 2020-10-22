<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/12/2
 * Time: 13:36
 */

namespace app\plugins\vip_card\jobs;

use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderDetailVipCardInfo;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\vip_card\forms\api\IndexForm;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\forms\common\CommonVipCard;
use app\plugins\vip_card\models\VipCardOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;
use app\models\Order;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\events\OrderEvent;
use app\jobs\OrderCancelJob;

class OrderSubmitJob extends BaseObject implements JobInterface
{

    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    public $token;
    public $id;

    /**
     * 临时商品数据，用于下单同时开通超级会员卡时订单优惠金额调整
     * @var
     */
    public $orderDetailVipCardInfoData;
    public $order_form;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CommonVip::getCommon()->generateOrders($this->id, $this->token, $this->orderDetailVipCardInfoData, false, '', 1, $this->order_form);
            $transaction->commit();
        } catch (\Exception $e) {
            \Yii::error($e);
            $transaction->rollBack();
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
        }
    }
}
