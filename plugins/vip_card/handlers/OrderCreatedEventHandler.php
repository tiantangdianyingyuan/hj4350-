<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\vip_card\handlers;

use app\forms\common\template\tplmsg\AccountChange;
use app\handlers\orderHandler\BaseOrderCreatedHandler;
use app\jobs\ChangeShareOrderJob;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Share;
use app\models\ShareOrder;
use app\models\ShareSetting;
use app\models\User;
use app\plugins\vip_card\forms\common\AddShareOrder;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;

class OrderCreatedEventHandler extends BaseOrderCreatedHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->setShareUser()->setShareMoney();
    }

    protected function saveShareMoney()
    {
        try {
            (new AddShareOrder())->save($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('超级会员卡分销佣金记录失败：' . $exception->getMessage());
            \Yii::error($exception);
        }
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function saveShare($order)
    {
        $baseModel = new Model();
        $shareSetting = ShareSetting::getList($order->mall_id);
        if (!$shareSetting[ShareSetting::LEVEL] || $shareSetting[ShareSetting::LEVEL] < 1) {
            return;
        }

        $vipCardSetting = (new CommonVipCardSetting())->getSetting();
        if (!$vipCardSetting['is_share']) {
            return;
        }

        $user = User::findOne(['id' => $order->user_id]);
        if (!$user) {
            return;
        }
        $userInfo = $user->userInfo;
        if (!$userInfo) {
            return;
        }

        // 查询出3个级别的用户
        if ($shareSetting[ShareSetting::IS_REBATE] == 1 && $user->share && $user->share->status == 1
            && $user->identity->is_distributor == 1
            && $user->share->is_delete == 0) {
            // 自购返利 下单用户必须是分销商
            $firstParentUser = $user->share;
        } else {
            $firstParentUser = Share::findOne(['user_id' => $userInfo->parent_id, 'status' => 1, 'is_delete' => 0]);
        }
        if (!$firstParentUser) {
            return;
        }

        if ($firstParentUser && $firstParentUser->userInfo
            && $firstParentUser->userInfo->parent_id && $shareSetting[ShareSetting::LEVEL] > 1) {
            $secondParentUser = Share::findOne([
                'user_id' => $firstParentUser->userInfo->parent_id, 'is_delete' => 0, 'status' => 1
            ]);
        } else {
            $secondParentUser = null;
        }

        if ($secondParentUser && $secondParentUser->userInfo
            && $secondParentUser->userInfo->parent_id && $shareSetting[ShareSetting::LEVEL] > 2) {
            $thirdParentUser = Share::findOne([
                'user_id' => $secondParentUser->userInfo->parent_id, 'is_delete' => 0, 'status' => 1
            ]);
        } else {
            $thirdParentUser = null;
        }

        /** @var OrderDetail[] $orderDetails */
        $orderDetails = $order->getDetail()->with(['goods' => function ($query) {
            $query->with(['share']);
        }])->andWhere(['is_delete' => 0])->all();
        $shareOrderList = ShareOrder::find()->andWhere([
            'mall_id' => $order->mall_id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'is_delete' => 0,
        ])->all();
        $firstPrice = 0;
        $secondPrice = 0;
        $thirdPrice = 0;
        $shareLevelList = $vipCardSetting['shareLevelList'];
        foreach ($orderDetails as $orderDetail) {
            $first = 0;
            $second = 0;
            $third = 0;

            if ($firstParentUser) {
                if ($firstParentUser->level > 0) {
                    $first = $this->getShareLevel($firstParentUser, $shareLevelList, "share_commission_first");
                } else {
                    $firstValue = $vipCardSetting['share_commission_first'];
                    if (!empty($firstValue) && is_numeric($firstValue)) {
                        $first = $firstValue;
                    }
                }
            }

            if ($secondParentUser) {
                if ($secondParentUser->level > 0) {
                    $second = $this->getShareLevel($secondParentUser, $shareLevelList, "share_commission_second");
                } else {
                    $secondValue = $vipCardSetting['share_commission_second'];
                    if (!empty($secondValue) && is_numeric($secondValue)) {
                        $second = $secondValue;
                    }
                }
            }

            if ($thirdParentUser) {
                if ($thirdParentUser->level > 0) {
                    $third = $this->getShareLevel($thirdParentUser, $shareLevelList, "share_commission_third");
                } else {
                    $thirdValue = $vipCardSetting['share_commission_third'];
                    if (!empty($thirdValue) && is_numeric($thirdValue)) {
                        $third = $thirdValue;
                    }
                }
            }


            if ($vipCardSetting['share_type'] == 1) {
                $first = $first * $orderDetail->total_price / 100;
                $second = $second * $orderDetail->total_price / 100;
                $third = $third * $orderDetail->total_price / 100;
            } else {
                $first = $first * $orderDetail->num;
                $second = $second * $orderDetail->num;
                $third = $third * $orderDetail->num;
            }

            $model = ShareOrder::findOne([
                'mall_id' => $order->mall_id,
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'user_id' => $order->user_id,
                'is_delete' => 0,
            ]);
            if (!$model) {
                $model = new ShareOrder();
                $model->mall_id = $order->mall_id;
                $model->order_id = $order->id;
                $model->user_id = $order->user_id;
                $model->order_detail_id = $orderDetail->id;
            }

            if ($firstParentUser) {
                $firstParentId = $firstParentUser->user_id;
            } else {
                $firstParentId = 0;
                $first = 0;
            }
            $model->first_parent_id = $firstParentId;
            $model->first_price = price_format($first);

            if ($secondParentUser) {
                $secondParentId = $secondParentUser->user_id;
            } else {
                $secondParentId = 0;
                $second = 0;
            }
            $model->second_parent_id = $secondParentId;
            $model->second_price = price_format($second);

            if ($thirdParentUser) {
                $thirdParentId = $thirdParentUser->user_id;
            } else {
                $thirdParentId = 0;
                $third = 0;
            }
            $model->third_parent_id = $thirdParentId;
            $model->third_price = price_format($third);

            $before = $model->oldAttributes;
            if (!$model->save()) {
                throw new \Exception($baseModel->getErrorMsg($model));
            }
            $firstPrice += $first;
            $secondPrice += $second;
            $thirdPrice += $third;
        }
        $firstPrice = price_format($firstPrice);
        $secondPrice = price_format($secondPrice);
        $thirdPrice = price_format($thirdPrice);
        if (count($shareOrderList) > 0) {
            try {
                $templateSend = new AccountChange([
                    'remark' => '分销佣金',
                    'page' => 'pages/user-center/user-center',
                ]);
                if ($firstPrice > 0) {
                    $templateSend->desc = '有用户下单，预计可得佣金' . $firstPrice;
                    $templateSend->user = $firstParentUser->user;
                    $templateSend->send();
                }
                if ($secondPrice > 0) {
                    $templateSend->desc = '有用户下单，预计可得佣金' . $secondPrice;
                    $templateSend->user = $secondParentUser->user;
                    $templateSend->send();
                }
                if ($thirdPrice > 0) {
                    $templateSend->desc = '有用户下单，预计可得佣金' . $thirdPrice;
                    $templateSend->user = $thirdParentUser->user;
                    $templateSend->send();
                }
            } catch (\Exception $exception) {
                \Yii::error('预计可得佣金发放');
                \Yii::error($exception);
            }
        }
        \Yii::$app->queue->delay(0)->push(new ChangeShareOrderJob([
            'mall' => \Yii::$app->mall,
            'order' => $order,
            'beforeList' => $shareOrderList,
            'type' => 'add'
        ]));
    }

    protected function getShareLevel($share, $shareList, $level)
    {
        if (!isset($share->level)) {
            return 0;
        }

        if (!empty($shareList)) {
            foreach ($shareList as $list) {
                if (isset($list->level) && $list->level == $share->level && isset($list->$level)) {
                    return $list->$level;
                }
            }
        }

        return 0;
    }
}
