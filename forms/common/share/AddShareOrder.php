<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/28
 * Time: 13:54
 */

namespace app\forms\common\share;


use app\forms\common\order\CommonOrder;
use app\forms\common\template\tplmsg\AccountChange;
use app\jobs\ChangeShareOrderJob;
use app\models\Model;
use app\models\OrderDetail;
use app\models\Share;
use app\models\ShareOrder;
use app\models\ShareSetting;
use app\models\User;

class AddShareOrder extends Model
{

    public function save($order)
    {
        // 判断账户是否有分销权限
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('share', $permission)) {
            \Yii::error('未开启分销，没有分销权限');
            return;
        }
        /** @var OrderDetail[] $orderDetails */
        $orderDetails = $order->getDetail()->with(['goods' => function ($query) {
            $query->with(['share']);
        }])->andWhere(['is_delete' => 0])->all();
        // 判断是否有商品需要设置分销订单
        $flag = false;
        foreach ($orderDetails as $orderDetail) {
            if ($this->check($orderDetail)) {
                $flag = true;
            }
        }
        if (!$flag) {
            \Yii::error('未开启分销');
            return;
        }
        $baseModel = new Model();
        // 判断商家是否开启分销
        $shareSetting = ShareSetting::getList($order->mall_id);
        if (!$shareSetting[ShareSetting::LEVEL] || $shareSetting[ShareSetting::LEVEL] < 1) {
            \Yii::error('商家未开启分销');
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
            \Yii::error('不存在分销关系');
            return;
        }
        $secondParentUser = $this->getShare($firstParentUser, $shareSetting[ShareSetting::LEVEL], 1);
        $thirdParentUser = $this->getShare($secondParentUser, $shareSetting[ShareSetting::LEVEL], 2);
        $shareOrderList = ShareOrder::find()->andWhere([
            'mall_id' => $order->mall_id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'is_delete' => 0,
        ])->all();
        $firstPrice = 0;
        $secondPrice = 0;
        $thirdPrice = 0;
        foreach ($orderDetails as $orderDetail) {
            if (!$this->check($orderDetail)) {
                continue;
            }
            $goodsShareSetting = $orderDetail->goods;
            $first = [
                'shareType' => 0,
                'sharePrice' => 0,
                'price' => 0,
            ];
            $second = [
                'shareType' => 0,
                'sharePrice' => 0,
                'price' => 0,
            ];
            $third = [
                'shareType' => 0,
                'sharePrice' => 0,
                'price' => 0,
            ];
            $shareType = 0;
            $goodsInfo = $orderDetail->decodeGoodsInfo($orderDetail->goods_info);
            if ((isset($goodsInfo['goods_attr']['individual_share'])
                    && $goodsInfo['goods_attr']['individual_share'] == 1) || $goodsShareSetting->individual_share == 1) {
                // 单独设置
                $shareType = isset($goodsInfo['goods_attr']['share_type'])
                    ? $goodsInfo['goods_attr']['share_type']
                    : $goodsShareSetting->share_type;
                if ($firstParentUser) {
                    if (isset($goodsInfo['goods_attr']['share_commission_first'])) {
                        $first = $goodsInfo['goods_attr']['share_commission_first'];
                        $first = $this->getSharePrice($first, $shareType, $orderDetail);
                    } else {
                        $first = $this->getShareLevel($firstParentUser, $orderDetail, 'share_commission_first');
                    }
                }
                if ($secondParentUser) {
                    if (isset($goodsInfo['goods_attr']['share_commission_second'])) {
                        $second = $goodsInfo['goods_attr']['share_commission_second'];
                        $second = $this->getSharePrice($second, $shareType, $orderDetail);
                    } else {
                        $second = $this->getShareLevel($secondParentUser, $orderDetail, 'share_commission_second');
                    }
                }
                if ($thirdParentUser) {
                    if (isset($goodsInfo['goods_attr']['share_commission_third'])) {
                        $third = $goodsInfo['goods_attr']['share_commission_third'];
                        $third = $this->getSharePrice($third, $shareType, $orderDetail);
                    } else {
                        $third = $this->getShareLevel($thirdParentUser, $orderDetail, 'share_commission_third');
                    }
                }
            } else {
                if ($order->mch_id > 0) {
                    continue;
                }
                // 全局设置
                $shareType = $shareSetting[ShareSetting::PRICE_TYPE] == 2 ? 0 : 1;
                if ($firstParentUser) {
                    if ($firstParentUser->level > 0) {
                        $first = $this->getShareLevelGlobal($firstParentUser, $orderDetail, ShareSetting::FIRST);
                    } else {
                        if (!empty($shareSetting[ShareSetting::FIRST])
                            && is_numeric($shareSetting[ShareSetting::FIRST])) {
                            $first = $shareSetting[ShareSetting::FIRST];
                            $first = $this->getSharePrice($first, $shareType, $orderDetail);
                        }
                    }
                }

                if ($secondParentUser) {
                    if ($secondParentUser->level > 0) {
                        $second = $this->getShareLevelGlobal($secondParentUser, $orderDetail, ShareSetting::SECOND);
                    } else {
                        if (!empty($shareSetting[ShareSetting::SECOND])
                            && is_numeric($shareSetting[ShareSetting::SECOND])) {
                            $second = $shareSetting[ShareSetting::SECOND];
                            $second = $this->getSharePrice($second, $shareType, $orderDetail);
                        }
                    }
                }

                if ($thirdParentUser) {
                    if ($thirdParentUser->level > 0) {
                        $third = $this->getShareLevelGlobal($thirdParentUser, $orderDetail, ShareSetting::THIRD);
                    } else {
                        if (!empty($shareSetting[ShareSetting::THIRD])
                            && is_numeric($shareSetting[ShareSetting::THIRD])) {
                            $third = $shareSetting[ShareSetting::THIRD];
                            $third = $this->getSharePrice($third, $shareType, $orderDetail);
                        }
                    }
                }
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
                $model->flag = 1;
            }

            if ($firstParentUser) {
                $firstParentId = $firstParentUser->user_id;
            } else {
                $firstParentId = 0;
            }
            $model->first_parent_id = $firstParentId;
            $model->first_price = price_format($first['price']);
            $model->first_share_type = $first['shareType'];
            $model->first_share_price = $first['sharePrice'];

            if ($secondParentUser) {
                $secondParentId = $secondParentUser->user_id;
            } else {
                $secondParentId = 0;
            }
            $model->second_parent_id = $secondParentId;
            $model->second_price = price_format($second['price']);
            $model->second_share_type = $second['shareType'];
            $model->second_share_price = $second['sharePrice'];

            if ($thirdParentUser) {
                $thirdParentId = $thirdParentUser->user_id;
            } else {
                $thirdParentId = 0;
            }
            $model->third_parent_id = $thirdParentId;
            $model->third_price = price_format($third['price']);
            $model->third_share_type = $third['shareType'];
            $model->third_share_price = $third['sharePrice'];

            $model->price = $orderDetail->total_price;

            $before = $model->oldAttributes;
            if (!$model->save()) {
                throw new \Exception($baseModel->getErrorMsg($model));
            }
            $firstPrice += $first['price'];
            $secondPrice += $second['price'];
            $thirdPrice += $third['price'];
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

    /**
     * @param OrderDetail $orderDetail
     * @return bool
     */
    protected function check($orderDetail)
    {
        $common = CommonOrder::getCommonOrder($orderDetail->sign);
        $orderConfig = $common->getOrderConfig();
        return $orderConfig->is_share == 1 ? true : false;
    }

    /**
     * @param $default
     * @param $shareType
     * @param OrderDetail $orderDetail
     * @return array
     */
    protected function getSharePrice($default, $shareType, $orderDetail)
    {
        $price = 0;
        if (!empty($default) && is_numeric($default) && $default > 0) {
            $price = $default;
        }

        if ($shareType == 1) {
            $sharePrice = $price;
            $price = $price * $orderDetail->total_price / 100;
        } else {
            $price = $price * $orderDetail->num;
            $sharePrice = $price * $orderDetail->num;
        }
        return [
            'shareType' => $shareType,
            'sharePrice' => $sharePrice,
            'price' => $price,
        ];
    }

    /**
     * @param Share $share
     * @param OrderDetail $orderDetail
     * @param string $key
     * @return array
     * @throws \Exception
     * 获取详细设置分销等级佣金
     */
    protected function getShareLevel($share, $orderDetail, $key)
    {
        $price = 0;
        $goodsInfo = $orderDetail->decodeGoodsInfo($orderDetail->goods_info);
        if (isset($goodsInfo['goods_attr']['goods_share_level'])) {
            $hasLevel = false;
            $first = 0;
            foreach ($goodsInfo['goods_attr']['goods_share_level'] as $item) {
                if ($item['level'] == $share->level) {
                    $hasLevel = true;
                    $price = $item[$key];
                    break;
                }
                if ($item['level'] == 0) {
                    $first = $item[$key];
                }
            }
            // 判断是否有设置指定分销等级的佣金，若没有则使用默认佣金进行计算
            if (!$hasLevel) {
                $price = $first;
            }
        }
        $shareType = $goodsInfo['goods_attr']['share_type'];
        return $this->getSharePrice($price, $shareType, $orderDetail);
    }

    /**
     * @param $share
     * @param $orderDetail
     * @param $key
     * @return array
     * 获取全局设置分销佣金
     */
    protected function getShareLevelGlobal($share, $orderDetail, $key)
    {
        $shareLevel = CommonShareLevel::getInstance()->getShareLevelByLevel($share->level);
        if ($shareLevel) {
            $price = $shareLevel->$key;
            $shareType = $shareLevel->price_type == 2 ? 0 : 1;
            return $this->getSharePrice($price, $shareType, $orderDetail);
        } else {
            return [
                'sharePrice' => 0,
                'shareType' => 0,
                'price' => 0,
            ];
        }
    }

    /**
     * @param Share $children
     * @param $settingLevel
     * @param $level
     * @return Share|null
     * 获取父级分销商
     */
    public function getShare($children, $settingLevel, $level)
    {
        $share = null;
        if ($children && $children->userInfo
            && $children->userInfo->parent_id && $settingLevel > $level) {
            $share = Share::findOne([
                'user_id' => $children->userInfo->parent_id, 'is_delete' => 0, 'status' => 1
            ]);
        }
        return $share;
    }

    /**
     * @param OrderDetail $orderDetail
     * @param float $price 用于分销的金额
     * @return bool
     * @throws \Exception
     * 售后修改分销订单
     */
    public function refund($orderDetail, $price)
    {
        $model = ShareOrder::findOne([
            'mall_id' => $orderDetail->order->mall_id,
            'order_id' => $orderDetail->order_id,
            'order_detail_id' => $orderDetail->id,
            'user_id' => $orderDetail->order->user_id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            \Yii::warning('不存在分销订单');
            return false;
        }
        if ($price <= 0) {
            $price = 0;
        }
        $before = clone $model;
        $model->first_price = $this->sharePrice($orderDetail->total_price, $model->first_price, $price);
        $model->second_price = $this->sharePrice($orderDetail->total_price, $model->second_price, $price);
        $model->third_price = $this->sharePrice($orderDetail->total_price, $model->third_price, $price);
        $model->price = $price;
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        \Yii::$app->queue->delay(0)->push(new ChangeShareOrderJob([
            'mall' => \Yii::$app->mall,
            'shareOrder' => $model,
            'before' => $before,
            'type' => 'add'
        ]));
        return true;
    }

    public function sharePrice($totalPrice, $sharePrice, $price)
    {
        if ($totalPrice == 0) {
            return 0;
        }
        return price_format($sharePrice * $price / $totalPrice);
    }
}
