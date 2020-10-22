<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/21
 * Time: 15:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\handle;


use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\plugins\fxhb\events\JoinActivityEvent;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\models\FxhbActivity;
use yii\base\BaseObject;

class JoinActivityHandle extends BaseObject
{
    public function on($event)
    {
        /* @var JoinActivityEvent $event */
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var FxhbActivity $activity */
            $activity = \Yii::$app->serializer->decode($event->parentActivity->data);
            $common = CommonFxhbDb::getCommon($event->mall);
            if ($event->parentActivity) {
                $userActivityAll = $common->getUserActivityAllById($event->parentActivity->id);
            } else {
                $userActivityAll = [$event->userActivity];
            }
            \Yii::warning('用户参与拆红包');
            \Yii::warning($event->parentActivity->number);
            \Yii::warning(count($userActivityAll));
            if ($event->parentActivity->number != count($userActivityAll)) {
                if ($event->parentActivity->activity->end <= time() || $common->getResetTime($event->parentActivity) == 0) {
                    foreach ($userActivityAll as $userActivity) {
                        $common->setUserActivityStatus($userActivity, 2);
                    }
                    if ($activity->sponsor_count_type == 1) {
                        // 拆红包失败时加上拆红包数量
                        $common->updateSponsorCount($event->parentActivity->activity, 'add');
                    }
                }
            } else {
                $priceList = $this->getCouponPrice($event->parentActivity->number, $event->parentActivity->count_price, $activity->type);
                foreach ($userActivityAll as $key => $userActivity) {
                    if ($userActivity->status != 0 && $userActivity->user_coupon_id != 0) {
                        continue;
                    }
                    $userCoupon = $common->addUserCoupon([
                        'mall_id' => $event->parentActivity->mall_id,
                        'user_id' => $userActivity->user_id,
                        'name' => '红包活动',
                        'type' => 2,
                        'min_price' => $activity->least_price,
                        'sub_price' => $priceList[$key],
                        'total_count' => 0,
                        'sort' => 0,
                        'expire_type' => 1,
                        'expire_day' => $activity->effective_time,
                        'appoint_type' => $activity->coupon_type,
                        'rule' => '红包活动',
                        'is_delete' => 1,
                        'deleted_at' => date('Y-m-d H:i:s', time()),
                        'cats' => $event->parentActivity->activity->cats,
                        'goods' => $event->parentActivity->activity->goods
                    ]);
                    $userActivity->get_price = $priceList[$key];
                    $res = $common->setUserActivityStatus($userActivity, 1, $userCoupon->id);
                }
                if ($activity->sponsor_count_type == 0) {
                    // 拆红包成功时减掉拆红包数量
                    $common->updateSponsorCount($event->parentActivity->activity, 'sub');
                }

                // TODO 发送订阅消息
                /*
                foreach ($userActivityAll as $userActivity) {
                    $tplMsg = new ActivitySuccessTemplate();
                    $tplMsg->activityName = '拆红包';
                    $tplMsg->name = date('Y-m-d H:i:s', $_SERVER["REQUEST_TIME"]);
                    $tplMsg->remark = '拆红包成功，奖励已发放！';
                    $tplMsg->user = $userActivity->user;
                    $tplMsg->page = 'plugins/fxhb/detail/detail';
                    $tplMsg->send();
                }
                */
            }
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::warning($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param $num
     * @param $totalPrice
     * @param $type
     * @return array
     * 获取每个用户红包金额数组
     */
    private function getCouponPrice($num, $totalPrice, $type)
    {
        switch ($type) {
            case 1:
                $res = $this->getRandom($num, $totalPrice);
                break;
            case 2:
                $res = $this->getAverage($num, $totalPrice);
                break;
            default:
                $res = [];
        }
        return $res;
    }

    /**
     * @param $num
     * @param $totalPrice
     * @return array
     * 平均分红包
     */
    private function getAverage($num, $totalPrice)
    {
        $res = [];
        for ($i = 0; $i < $num; $i++) {
            $res[] = price_format($totalPrice / $num, 'float', 2);
        }
        return $res;
    }

    /**
     * @param $num
     * @param $totalPrice
     * @return array
     * 随机拆红包
     */
    private function getRandom($num, $totalPrice)
    {
        $res = [];
        $sum = [];
        for ($i = 0; $i < $num; $i++) {
            $sum[] = mt_rand() % 100 + 1;
        }
        $total = array_sum($sum);
        for ($i = 0; $i < $num; $i++) {
            if ($i + 1 == $num) {
                $res[] = price_format($totalPrice - array_sum($res), 'float', 2);
            } else {
                $res[] = price_format($totalPrice * $sum[$i] / $total, 'float', 2);
            }
        }
        return $res;
    }

}
