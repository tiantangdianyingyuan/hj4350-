<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 13:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\jobs;


use app\models\Mall;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\bargain\events\BargainUserOrderEvent;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\handlers\HandlerRegister;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\BargainUserOrder;
use app\plugins\bargain\models\Code;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property BargainOrder $bargainOrder
 * @property Mall $mall
 * @property User $user
 */
class UserJoinBargainJob extends BaseObject implements JobInterface
{
    public $bargainOrder;
    public $mall;
    public $token;
    public $user;

    public function execute($queue)
    {
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        $this->user = User::findOne($this->user->id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var CommonBargainOrder $commonBargainOrder */
            $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder();
            $this->bargainOrder = $commonBargainOrder->getBargainOrder($this->bargainOrder->id);
            if ($this->bargainOrder->bargainGoods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($this->bargainOrder->goods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($this->bargainOrder->goods->status == 0) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($this->bargainOrder->goods->goodsWarehouse->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            /* @var BargainUserOrder $userBargainOrder */
            $userBargainOrder = $commonBargainOrder->getUserJoinOrder($this->user->id, $this->bargainOrder->id);
            if ($userBargainOrder) {
                throw new \Exception('用户已参与本次砍价');
            }
            if ($this->bargainOrder->status != Code::BARGAIN_PROGRESS) {
                throw new \Exception('砍价已完成');
            }

            if ($this->bargainOrder->resetTime <= 0) {
                throw new \Exception('砍价已结束');
            }
            /* @var BargainUserOrder[] $userJoinOrderAll */
            $userJoinOrderAll = $commonBargainOrder->getUserJoinOrderAll($this->bargainOrder->id);
            // 已砍价金额
            $totalPrice = 0;
            // 参与砍价人数
            $totalPeople = 0;
            foreach ($userJoinOrderAll as $userJoinOrder) {
                $totalPrice += floatval($userJoinOrder->price);
                $totalPeople++;
            }
            // 剩余可砍价金额
            $resetPrice = floatval($this->bargainOrder->price) - floatval($this->bargainOrder->min_price) - $totalPrice;
            $resetPrice = $resetPrice <= 0 ? 0 : $resetPrice;
            if ($resetPrice <= 0) {
                throw new \Exception('已砍至最低价');
            }

            $price = $this->intToFloat($this->getPrice($resetPrice, $totalPeople));
            $bargainUserOrder = $commonBargainOrder->addBargainUserOrder(
                $this->bargainOrder->id,
                $this->user->id,
                $price,
                $this->token
            );
            $bargainGoods = $this->bargainOrder->bargainGoods;
            $bargainGoods->participant += 1;
            if ($price == $resetPrice) {
                $bargainGoods->min_price_goods += 1;
            }
            $bargainGoods->save();
            $t->commit();
            array_push($userJoinOrderAll, $bargainUserOrder);
            // 触发用户参与砍价事件
            \Yii::$app->trigger(HandlerRegister::BARGAIN_USER_JOIN, new BargainUserOrderEvent([
                'bargainUserOrderAll' => $userJoinOrderAll,
                'bargainOrder' => $this->bargainOrder
            ]));
        } catch (\Exception $exception) {
            $t->rollBack();
            $form = new OrderSubmitResult();
            $form->token = $this->token;
            $form->data = $exception->getMessage();
            $form->save();
        }
    }

    /**
     * @param float $resetPrice 剩余金额
     * @param int $people 已砍价人数
     * @return float|int
     *  砍价算法
     */
    private function getPrice($resetPrice, $people)
    {
        $data = \Yii::$app->serializer->decode($this->bargainOrder->bargain_goods_data);
        if (isset($data->people)) {
            $dataPeople = intval($data->people);
            if ($dataPeople != 0) {
                if ($people == $dataPeople - 1) {
                    $money = $resetPrice * 100;
                    return $money;
                }
                if ($people >= $dataPeople) {
                    return 0;
                }
            }
        }
        if ($people < intval($data->human)) {
            $min = $data->first_min_price > $data->first_max_price ? $data->first_max_price : $data->first_min_price;
            $max = $data->first_min_price > $data->first_max_price ? $data->first_min_price : $data->first_max_price;
        } else {
            $min = min($data->second_min_price, $data->second_max_price);
            $max = max($data->second_min_price, $data->second_max_price);
        }
        $money = $this->getRand($min * 100, $max * 100);
        if ($money > $resetPrice * 100) {
            $money = (round($resetPrice, 2)) * 100;
        }
        return intval($money);
    }

    // 随机数
    private function getRand($min, $max)
    {
        return mt_rand($min, $max);
    }

    // int转float
    private function intToFloat($int)
    {
        if (strlen($int) < 3) {
            $int = str_pad($int, 3, 0, STR_PAD_LEFT);
        }
        return round(floatval($this->insertToStr($int, strlen($int) - 2, '.')), 2);
    }

    /**
     * 指定位置插入字符串
     * @param $str string  原字符串
     * @param $i  integer   插入位置
     * @param $substr string 插入字符串
     * @return string 处理后的字符串
     */
    private function insertToStr($str, $i, $substr)
    {
        //指定插入位置前的字符串
        $startStr = substr($str, 0, $i);
        //指定插入位置后的字符串
        $lastStr = substr($str, -2);
        //将插入位置前，要插入的，插入位置后三个字符串拼接起来
        $str = $startStr . $substr . $lastStr;
        //返回结果
        return $str;
    }
}
