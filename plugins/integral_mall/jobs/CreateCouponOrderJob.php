<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\integral_mall\jobs;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Order;
use app\models\UserInfo;
use app\plugins\integral_mall\models\IntegralMallCouponOrderSubmitResult;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallOrders;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class CreateCouponOrderJob
 * @package app\plugins\bargain\jobs
 */
class CreateCouponOrderJob extends BaseObject implements JobInterface
{
    public $coupon;
    public $user;
    public $mall;
    public $token;

    public function execute($queue)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var UserInfo $userInfo */
            $userInfo = UserInfo::find()->where(['user_id' => $this->user->id])->select('integral')->one();
            if ($userInfo->integral < $this->coupon->integral_num) {
                throw new \Exception('积分余额不足');
            }

            $buyCount = IntegralMallCouponsOrders::find()->where([
                'mall_id' => $this->coupon->mall_id,
                'integral_mall_coupon_id' => $this->coupon->id,
                'is_pay' => 1,
            ])->count();
            if ($buyCount >= $this->coupon->send_count) {
                throw new \Exception('优惠券库存不足');
            }

            if ($this->coupon->exchange_num != -1) {
                $buyCount = IntegralMallCouponsOrders::find()->where([
                    'mall_id' => $this->coupon->mall_id,
                    'integral_mall_coupon_id' => $this->coupon->id,
                    'is_pay' => 1,
                    'user_id' => $this->user->id,
                ])->count();

                if ($buyCount >= $this->coupon->exchange_num) {
                    throw new \Exception('兑换次数已用完,每人限兑换' . $this->coupon->exchange_num . '次');
                }
            }

            $integralOrder = new IntegralMallCouponsOrders();
            $integralOrder->user_id = $this->user->id;
            $integralOrder->order_no = Order::getOrderNo('JF');
            $integralOrder->mall_id = $this->mall->id;
            $integralOrder->integral_mall_coupon_id = $this->coupon->id;
            $integralOrder->integral_mall_coupon_info = \Yii::$app->serializer->encode($this->coupon);
            $integralOrder->user_coupon_id = 0;
            $integralOrder->price = $this->coupon->price;
            $integralOrder->integral_num = $this->coupon->integral_num;
            $integralOrder->token = $this->token;
            $res = $integralOrder->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($integralOrder));
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage());
            $this->saveOrderResult($e->getMessage());
        }
    }

    private function saveOrderResult($message)
    {
        $model = new IntegralMallCouponOrderSubmitResult();
        $model->token = $this->token;
        $model->data = $message;
        $res = $model->save();
    }
}
