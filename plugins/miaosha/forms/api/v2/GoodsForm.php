<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api\v2;

use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\miaosha\forms\common\v2\SettingForm;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\Plugins\miaosha\Plugin;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $id;
    public $page;
    public $open_time;
    public $open_date;
    public $type;
    public $is_activity;

    public function rules()
    {
        return [
            [['id', 'open_time', 'type', 'is_activity'], 'integer'],
            [['page', 'type'], 'default', 'value' => 1],
            [['open_date'], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->type == 1) {
            $openDate = $this->open_date ?: date('Y-m-d');
            $openTime = $this->open_time ?: date('H');
            $activityIds = MiaoshaActivitys::find()->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
            // 秒杀整点商品列表
            $list = MiaoshaGoods::find()->alias('mg')->where([
                'mg.is_delete' => 0,
                'mg.open_date' => $openDate,
                'mg.open_time' => $openTime,
                'mg.mall_id' => \Yii::$app->mall->id,
                'mg.activity_id' => $activityIds,
            ])
                ->leftJoin(['g' => Goods::tableName()], 'g.id=mg.goods_id')
                ->andWhere(['g.status' => 1, 'g.is_delete' => 0])
                ->with(['attr', 'goods.goodsWarehouse'])
                ->orderBy(['g.sort' => SORT_ASC, 'mg.id' => SORT_DESC])
                ->page($pagination)
                ->all();
        } else {
            // 秒杀预告列表
            $res = $this->estimate();
            $list = $res['list'];
            $pagination = $res['pagination'];
        }

        $setting = (new SettingForm())->search();
        $newList = [];
        $commonEcard = CommonEcard::getCommon();
        /** @var MiaoshaGoods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['sort'] = $item->goods->sort;
            $miaoshaNum = 0; // 库存
            $minPrice = 0;
            foreach ($item->attr as $aItem) {
                $miaoshaNum += $commonEcard->getEcardStock($aItem['stock'], $item->goods);
                $minPrice = $minPrice == 0 ? $aItem['price'] : min($minPrice, $aItem['price']);
            }
            $newItem['min_price'] = $minPrice; // 最小规格价

            // 统计已秒杀数量  | 只统计当前时间段的秒杀数
            $count = $item->open_time <= date('H') && $this->type == 1 ? $this->getMiaoshaCount($item) : 0;
            if ($count <= 0) {
                $miaoshaPercentage = '0%';
            } else {
                if ($miaoshaNum == 0) {
                    $miaoshaPercentage = '100%';
                } else {
                    $miaoshaPercentage = round(((int) $count / ((int) $miaoshaNum + (int) $count)) * 100, 2) . '%';
                }
            }

            $newItem['miaosha_count'] = (int) $count; // 已秒杀数量
            $newItem['miaosha_num'] = $miaoshaNum; // 当前秒杀商品库存
            $newItem['miaosha_percentage'] = $miaoshaPercentage; // 已秒杀百分比
            $newItem['is_level'] = $setting['is_member_price'] ? $item->goods->is_level : 0; // 是否显示会员价
            $newItem['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice($item->goods);
            $newItem['goods'] = array_merge($newItem, ArrayHelper::toArray($item->goods->goodsWarehouse));
            $newItem['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($item->goods)->getAppoint();
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->is_activity) {
                $this->id = $this->getActivityGoodsId();
            }

            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }

            $form->goods = $goods;
            $res = $form->getAll();

            /** @var MiaoshaGoods $miaoshaGoods */
            $miaoshaGoods = MiaoshaGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $res['id'],
            ])->one();

            if (!$miaoshaGoods) {
                throw new \Exception('秒杀商品不存在');
            }

            $activity = MiaoshaActivitys::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $miaoshaGoods->activity_id])->one();

            $H = strlen($miaoshaGoods->open_time) > 1 ? $miaoshaGoods->open_time : '0' . $miaoshaGoods->open_time;
            $startTime = strtotime($miaoshaGoods->open_date . ' ' . $H . ':00:00');

            if (strtotime($miaoshaGoods->open_date) < strtotime(date('Y-m-d')) || !$activity) {
                $miaoshaStatus = 0; // 已结束
                $miaoshaTime = 0;
            } elseif ($miaoshaGoods->open_date == date('Y-m-d')) {
                if ($miaoshaGoods->open_time > date('H')) {
                    $miaoshaStatus = 2; // 未开始
                    $miaoshaTime = $startTime - time();
                } elseif ($miaoshaGoods->open_time == date('H')) {
                    $miaoshaStatus = 1; // 正在进行中
                    $time = strtotime(date('Y-m-d H') . ':00:00') + 60 * 60;
                    $miaoshaTime = $time - time();
                } else {
                    $miaoshaStatus = 0; // 已结束
                    $miaoshaTime = 0;
                }
            } else {
                $miaoshaStatus = 2;
                $miaoshaTime = $startTime - time();
            }

            if ($miaoshaGoods->open_date <= date('Y-m-d') && $miaoshaGoods->open_time <= date('H')) {
                $count = $this->getMiaoshaCount($miaoshaGoods);
            } else {
                $count = 0;
            }

            $res['miaoshaGoods'] = $miaoshaGoods;
            $setting = (new SettingForm())->search();
            $res['goods_marketing']['limit'] = $setting['is_territorial_limitation'] ? $res['goods_marketing']['limit'] : '';
            $res['level_show'] = $setting['is_member_price'] ? $res['level_show'] : 0;

            if (!$setting['is_share']) {
                $res['share'] = 0;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $res,
                    'miaosha_status' => $miaoshaStatus,
                    'miaosha_time' => $miaoshaTime,
                    'miaosha_buy_count' => $count,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    /**
     * 获取某个秒杀活动最近的一个场次商品ID
     */
    private function getActivityGoodsId()
    {
        $goodsId = 0;

        if ($this->is_activity) {
            $activity = MiaoshaActivitys::find()->andWhere(['id' => $this->id])->one();
            // 数据表结构需优化 秒杀日期 时间应该存一块 例如：2020-02-02 02:00:00
            if ($activity) {
                $goodsIds = Goods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1, 'sign' => (new Plugin())->getName()])->select('id');
                $list = MiaoshaGoods::find()->andWhere(['activity_id' => $activity->id, 'goods_id' => $goodsIds])->orderBy("CASE
                    WHEN `open_date` = '" . date('Y-m-d') . "' THEN
                        1
                    WHEN `open_date` > '" . date('Y-m-d') . "' THEN
                        2
                    WHEN `open_date` < '" . date('Y-m-d') . "' THEN
                        3
                    END,
                    `open_date`,
                    CASE
                    WHEN `open_time` = '" . date('H') . "' THEN
                        4
                    WHEN `open_time` > '" . date('H') . "' THEN
                        5
                    WHEN `open_time` < '" . date('H') . "' THEN
                        6
                    END,
                    `open_time`,
                    "

                )->select('goods_id,open_date,open_time')->asArray()->all();

                if (count($list) > 0) {
                    $goodsId = $list[0]['goods_id'];
                }
            }
        }

        return $goodsId;
    }

    public function estimate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // 即将开始的秒杀
        /** @var MiaoshaGoods $miaoshaGoods */
        $miaoshaGoods = $this->getNextMiaoshaGoods();
        $activityIds = MiaoshaActivitys::find()->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');

        $list = MiaoshaGoods::find()->alias('mg')->where([
            'mg.is_delete' => 0,
            'mg.open_date' => $miaoshaGoods ? $miaoshaGoods->open_date : '',
            'mg.open_time' => $miaoshaGoods ? $miaoshaGoods->open_time : '',
            'mg.mall_id' => \Yii::$app->mall->id,
            'mg.activity_id' => $activityIds,
        ])
            ->groupBy('mg.goods_warehouse_id')
            ->with(['goods.goodsWarehouse', 'attr'])
            ->leftJoin(['g' => Goods::tableName()], 'g.id=mg.goods_id')
            ->andWhere(['g.status' => 1, 'g.is_delete' => 0])
            ->orderBy(['g.sort' => SORT_ASC, 'mg.id' => SORT_DESC])
            ->page($pagination)
            ->all();

        return [
            'list' => $list,
            'pagination' => $pagination,
        ];
    }

    /**
     * 秒杀首页时间列表
     * @return array
     */
    public function getTimeList()
    {
        $goodsIds = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ])->select('id');

        $activityIds = MiaoshaActivitys::find()->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');

        $list = MiaoshaGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'goods_id' => $goodsIds,
            'activity_id' => $activityIds,
        ])->orderBy(['open_time' => SORT_ASC])->all();

        // 筛选秒杀时间
        $openTime = [];
        /** @var MiaoshaGoods $item */
        foreach ($list as $item) {
            $openTime[] = $item->open_time;
        }
        $openTime = array_unique($openTime);
        $openTime = array_values($openTime);

        $newList = [];
        foreach ($openTime as $item) {
            if ($item > date('H')) {
                $H = strlen($item) > 1 ? $item : 0 . $item;
                $newTime = strtotime(date('Y-m-d') . ' ' . $H . ':00:00');
                $newList[] = [
                    'open_time' => $item,
                    'new_open_time' => strlen($item) > 1 ? $item . ':00' : '0' . $item . ':00',
                    'label' => '即将开抢',
                    'status' => 0,
                    'time' => $newTime - time(),
                    'date_time' => date('Y-m-d H:i:s', $newTime),
                ];
            } elseif (date('H') == $item) {
                $newTime = strtotime(date('Y-m-d H') . ':00:00') + 60 * 60;
                $newList[] = [
                    'open_time' => $item,
                    'new_open_time' => strlen($item) > 1 ? $item . ':00' : '0' . $item . ':00',
                    'label' => '抢购进行中',
                    'status' => 1,
                    'time' => $newTime - time(),
                    'date_time' => date('Y-m-d H:i:s', $newTime),
                ];
            }
        }

        // 秒杀下一场预告是否存在
        /** @var MiaoshaGoods $miaoshaGoods */
        $miaoshaGoods = $this->getNextMiaoshaGoods();
        $nextMiaoshaDateTime = '';
        if ($miaoshaGoods) {
            $openTime = $miaoshaGoods->open_time;
            $newOpenTime = strlen($openTime) > 1 ? $openTime : '0' . $openTime . ':00';
            $nextMiaoshaDateTime = $miaoshaGoods->open_date . ' ' . $newOpenTime . ':00';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'is_estimate' => $miaoshaGoods ? 1 : 0,
                'next_miaosha_date_time' => $nextMiaoshaDateTime,
            ],
        ];
    }

    /**
     * 获取下一场秒杀活动商品
     * @return array|null|\yii\db\ActiveRecord
     */
    private function getNextMiaoshaGoods()
    {
        $goodsIds = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ])->select('id');

        $activityIds = MiaoshaActivitys::find()->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
        $miaoshaGoods = MiaoshaGoods::find()->andWhere([
            'and',
            ['>', 'open_date', date('Y-m-d')],
            ['mall_id' => \Yii::$app->mall->id],
            ['is_delete' => 0],
            ['goods_id' => $goodsIds],
            ['activity_id' => $activityIds],
        ])
            ->orderBy(['open_date' => SORT_ASC, 'open_time' => SORT_ASC])
            ->one();

        return $miaoshaGoods;
    }

    /**
     * 统计已秒杀量
     * @param MiaoshaGoods $item
     * @return int|mixed
     */
    private function getMiaoshaCount($item)
    {
        $orderIds = Order::find()->where([
            'is_pay' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'cancel_status' => 0,
        ])->select('id');

        $count = OrderDetail::find()->where([
            'goods_id' => $item->goods_id,
            'is_refund' => 0,
            'order_id' => $orderIds,
        ])->sum('num');
        $count += (int) $item->goods->virtual_sales;

        return $count;
    }
}
