<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/17 10:56
 */


namespace app\plugins\advance\jobs;


use app\forms\common\CommonOption;
use app\models\Address;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsMemberPrice;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Option;
use app\models\Order;
use app\models\User;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\AdvanceOrderSubmitResult;
use app\plugins\mch\models\Mch;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AdvanceOrderSubmitJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    public $goods_id;
    public $goods_attr_id;
    public $goods_num;
    public $token;
    public $appVersion;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $advance_order = AdvanceOrder::findOne(['token' => $this->token]);
            if (!empty($advance_order)) {
                throw new \Exception('重复下单。');
            }
            $advance_goods = AdvanceGoods::findOne(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $this->goods_id, 'is_delete' => 0]);
            if (empty($advance_goods)) {
                throw new \Exception('该商品已下架。');
            }
            if (strtotime($advance_goods->start_prepayment_at) > time()) {
                throw new \Exception('该商品预售活动未开始。');
            }
            if (strtotime($advance_goods->end_prepayment_at) < time()) {
                throw new \Exception('该商品预售活动已结束。');
            }
            try {
                (new GoodsAttr())->updateStock($this->goods_num, 'sub', $this->goods_attr_id);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
            $attr = AdvanceGoodsAttr::findOne(['goods_id' => $this->goods_id, 'goods_attr_id' => $this->goods_attr_id, 'is_delete' => 0]);
            if (empty($attr)) {
                throw new \Exception('该规格已下架。');
            }
            $set = (new SettingForm())->search();;
            if (empty($set) || $set['is_advance'] == 0) {
                throw new \Exception('商城预售已关闭。');
            }
            /* @var \app\models\Goods $goods_info */
            $goods_info = Goods::find()->where(['id' => $this->goods_id, 'is_delete' => 0])->with('goodsWarehouse')->one();
            if (empty($goods_info)) {
                throw new \Exception('该商品已下架。');
            }
            //区域销售判断
            if ($set['is_territorial_limitation'] == 1 && $goods_info->is_area_limit == 1) {
                $address = Address::findOne([
                    'user_id' => \Yii::$app->user->id,
                    'is_delete' => 0,
                    'is_default' => 1,
                ]);

                $mchItem = [
                    'mch' => $this->getMchInfo($goods_info->mch_id),
                ];
                $listData[] = $mchItem;
                // 检查区域允许购买
                $addressEnable = $this->getAddressEnable($address, $mchItem);
                if ($addressEnable == false) {
                    throw new \Exception('您所在区域无法购买。');
                }
            }
            //限购
            if ($goods_info->confine_count != -1) {
                $oldOrderGoodsNum = AdvanceOrder::find()
                    ->where([
                        'goods_id' => $this->goods_id,
                        'is_delete' => 0,
                        'is_cancel' => 0,
                        'is_refund' => 0,
                        'is_recycle' => 0,
                        'user_id' => \Yii::$app->user->id,
                    ])
                    ->sum('goods_num');
                $oldOrderGoodsNum = $oldOrderGoodsNum ? intval($oldOrderGoodsNum) : 0;
                $totalNum = $oldOrderGoodsNum + $this->goods_num;
                if ($totalNum > $goods_info->confine_count) {
                    throw new \Exception('商品购买数量超出限制: ' . $goods_info->name);
                }
            }

            //记录商品信息------------------------------------------------------------------------------------
            $goods_attr = GoodsAttr::findOne(['goods_id' => $this->goods_id, 'id' => $this->goods_attr_id, 'is_delete' => 0]);
            $attr_list = [];
            $attr_group = explode(':', $goods_attr->sign_id);
            foreach ($attr_group as $k => $value) {
                $arr_attr = json_decode($goods_info->attr_groups, true);
                $attr_list[$k]['attr_group_name'] = $arr_attr[$k]['attr_group_name'];
                $attr_list[$k]['attr_group_id'] = $arr_attr[$k]['attr_group_id'];
                foreach ($arr_attr[$k]['attr_list'] as $item) {
                    if ($item['attr_id'] == $value) {
                        $attr_list[$k]['attr_name'] = $item['attr_name'];
                        $attr_list[$k]['attr_id'] = $item['attr_id'];
                    }
                }
            }
            //计算会员价
            $member_price = $goods_attr->price;
            if ($goods_info->is_level == 1 && $goods_info->is_level_alone != 1) {
                $level_info = MallMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'level' => \Yii::$app->user->identity['identity']['member_level'], 'is_delete' => 0]);
                if (!empty($level_info)) {
                    $member_price = bcdiv(bcmul($member_price, $level_info->discount), 10);
                }
            } elseif ($goods_info->is_level == 1 && $goods_info->is_level_alone == 1) {
                /* @var \app\models\GoodsMemberPrice $goods_member_price */
                if (\Yii::$app->user->identity['identity']['member_level'] > 0) {
                    $goods_member_price = GoodsMemberPrice::find()
                        ->where(['level' => \Yii::$app->user->identity['identity']['member_level'], 'is_delete' => 0, 'goods_attr_id' => $this->goods_attr_id, 'goods_id' => $this->goods_id])->one();
                    if (!empty($goods_member_price)) {
                        $member_price = $goods_member_price->price;
                    }
                }
            }

            $goodsInfo = [
                'attr_list' => $attr_list,
                'goods_attr' => [
                    'id' => $goods_attr->id,
                    'goods_id' => $this->goods_id,
                    'sign_id' => $goods_attr->sign_id,
                    'stock' => $goods_attr->stock,
                    'price' => $goods_attr->price,
                    'original_price' => $goods_info->getOriginalPrice(),
                    'no' => $goods_attr->no,
                    'weight' => $goods_attr->weight,
                    'pic_url' => $goods_attr->pic_url,
                    'share_commission_first' => 0,
                    'share_commission_second' => 0,
                    'share_commission_third' => 0,
                    'member_price' => $member_price,
                    'integral_price' => 0,
                    'use_integral' => 0,
                    'discount' => [],// TODO 折扣为什么是数组
                    'extra' => [],
                    'goods_warehouse_id' => $goods_info->goods_warehouse_id,
                    'name' => $goods_info->getName(),
                    'cover_pic' => $goods_info->getCoverPic(),
                ],
            ];
            //---------------------------------------------------------------------------------

            $model = new AdvanceOrder();
            $model->mall_id = \Yii::$app->mall->id;
            $model->user_id = \Yii::$app->user->id;
            $model->goods_id = $advance_goods->goods_id;
            $model->goods_attr_id = $this->goods_attr_id;
            $model->goods_num = $this->goods_num;
            $model->advance_no = Order::getOrderNo('AD');
            $model->deposit = $attr->deposit;
            $model->swell_deposit = $attr->swell_deposit;
            $model->auto_cancel_time = date('Y-m-d H:i:s', bcadd(bcmul(!empty($set['over_time']) ? $set['over_time'] : 0, 60), time()));
            $model->goods_info = \Yii::$app->serializer->encode($goodsInfo);
            $model->token = $this->token;

            if (!$model->save()) {
                throw new \Exception($model->errors[0]);
            }
            if ($set['over_time'] > 0) {
                \Yii::$app->queue->delay($set['over_time'] * 60)->push(new AdvanceAutoCancelJob(['id' => $model->id]));
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            \Yii::error($e);
            $orderSubmitResult = new AdvanceOrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
            throw $e;
        } catch (\Error $error) {
            \Yii::error($error->getMessage());
            \Yii::error($error);
        }
    }

    protected function getMchInfo($id)
    {
        if ($id == 0) {
            return [
                'id' => 0,
                'name' => \Yii::$app->mall->name,
            ];
        } else {
            $mch = Mch::findOne($id);
            \Yii::$app->setMchId($mch->id);
            return [
                'id' => $id,
                'name' => $mch ? $mch->store->name : '未知商户',
            ];
        }
    }

    protected function getAddressEnable($address, $mchItem)
    {
        $mchId = $mchItem['mch']['id'];
        if (!$address) {
            return true;
        }

        $model = CommonOption::get(
            Option::NAME_TERRITORIAL_LIMITATION,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0],
            $mchId
        );
        if (!$model || !isset($model['is_enable'])) {
            return true;
        }
        if ($model['is_enable'] != 1) {
            return true;
        }
        if (!isset($model['detail']) || !is_array($model['detail'])) {
            return false;
        }
        foreach ($model['detail'] as $group) {
            if (isset($group['list']) && is_array($group['list'])) {
                foreach ($group['list'] as $item) {
                    if (isset($item['id'])) {
                        if ($item['id'] == $address->province_id
                            || $item['id'] == $address->city_id
                            || $item['id'] == $address->district_id) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

}
