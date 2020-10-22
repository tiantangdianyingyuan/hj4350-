<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\miaosha\forms\common\SettingForm;
use app\plugins\miaosha\models\MiaoshaGoods;
use yii\db\Query;
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

    public function rules()
    {
        return [
            [['id', 'open_time', 'type'], 'integer'],
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
            // 秒杀整点商品列表
            $list = MiaoshaGoods::find()->alias('mg')->where([
                'mg.is_delete' => 0,
                'mg.open_date' => $this->open_date ?: date('Y-m-d'),
                'mg.open_time' => $this->open_time ?: date('H'),
                'mg.mall_id' => \Yii::$app->mall->id,
                'mg.activity_id' => 0
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

        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $minPrice = $item['goods']['price'];
            $miaoshaNum = 0;
            foreach ($item['attr'] as $aItem) {
                $miaoshaNum += $aItem['stock'];
                $minPrice = min($minPrice, $aItem['price']);
            }
            $newItem['min_price'] = $minPrice;

            $count = 0;
            // 只统计当前时间段的秒杀数
            if ($item['open_time'] <= date('H')) {
                // 统计已秒杀数量
                $count = OrderDetail::find()->where([
                    'goods_id' => $item['goods_id'],
                    'is_refund' => 0,
                    'cancel_status' => 0,
                ])->joinWith(['order' => function ($query) {
                    return $query->andWhere(['is_pay' => 1]);
                }])->sum('num');

                $count += (int)$item['virtual_miaosha_num'];
            }

            if ($count <= 0) {
                $miaoshaPercentage = '0%';
            } else {
                if ($miaoshaNum == 0) {
                    $miaoshaPercentage = '100%';
                } else {
                    $miaoshaPercentage = round(((int)$count / ((int)$miaoshaNum + (int)$count)) * 100, 2) . '%';
                }
            }

            $newItem['miaosha_count'] = (int)$count;// 已秒杀数量
            $newItem['miaosha_num'] = $miaoshaNum;// 当前秒杀商品库存
            $newItem['miaosha_percentage'] = $miaoshaPercentage;// 已秒杀百分比
            $newItem['is_level'] = $item['goods']['is_level'];// 是否显示会员价
            $newItem['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice($item['goods']);
            $newItem['goods'] = array_merge($newItem, ArrayHelper::toArray($item['goods']['goodsWarehouse']));
            $newItem['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($item['goods'])->getAppoint();
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
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

            $miaoshaGoods = MiaoshaGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $res['id'],
            ])->asArray()->one();

            $H = strlen($miaoshaGoods['open_time']) > 1 ? $miaoshaGoods['open_time'] : '0' . $miaoshaGoods['open_time'];
            $startTime = strtotime($miaoshaGoods['open_date'] . ' ' . $H . ':00:00');

            if (strtotime($miaoshaGoods['open_date']) < strtotime(date('Y-m-d'))) {
                $miaoshaStatus = 0;// 已结束
                $miaoshaTime = 0;
            } elseif ($miaoshaGoods['open_date'] == date('Y-m-d')) {
                if ($miaoshaGoods['open_time'] > date('H')) {
                    $miaoshaStatus = 2;// 未开始
                    $miaoshaTime = $startTime - time();
                } elseif ($miaoshaGoods['open_time'] == date('H')) {
                    $miaoshaStatus = 1;// 正在进行中
                    $time = strtotime(date('Y-m-d H') . ':00:00') + 60 * 60;
                    $miaoshaTime = $time - time();
                } else {
                    $miaoshaStatus = 0;// 已结束
                    $miaoshaTime = 0;
                }
            } else {
                $miaoshaStatus = 2;
                $miaoshaTime = $startTime - time();
            }

            $orderIds = Order::find()->where([
                'is_pay' => 1,
                'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            ])->select('id');

            // 统计已秒杀数量
            $count = OrderDetail::find()->where([
                'goods_id' => $miaoshaGoods['goods_id'],
                'order_id' => $orderIds
            ])->sum('num');
            $count += $miaoshaGoods['virtual_miaosha_num'];

            $res['miaoshaGoods'] = $miaoshaGoods;
            $setting = (new SettingForm())->search();
            $res['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $res['goods_marketing']['limit'] : '';

            if (!$setting['is_share']) {
                $res['share'] = 0;
            }
//            dd($miaoshaTime);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $res,
                    'miaosha_status' => $miaoshaStatus,
                    'miaosha_time' => $miaoshaTime,
                    'miaosha_buy_count' => $count
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function estimate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // 即将开始的秒杀
        /** @var MiaoshaGoods $miaoshaGoods */
        $miaoshaGoods = $this->getNextMiaoshaGoods();

        $list = MiaoshaGoods::find()->alias('mg')->where([
            'mg.is_delete' => 0,
            'mg.open_date' => $miaoshaGoods ? $miaoshaGoods->open_date : '',
            'mg.open_time' => $miaoshaGoods ? $miaoshaGoods->open_time : '',
            'mg.mall_id' => \Yii::$app->mall->id,
            'mg.activity_id' => 0
        ])
            ->groupBy('mg.goods_warehouse_id')
            ->with(['goods.goodsWarehouse', 'attr'])
            ->joinWith(['goods AS g' => function ($query) {
                $query->andWhere(['g.status' => 1, 'g.is_delete' => 0]);
            }])
            ->orderBy(['g.sort' => SORT_ASC])
            ->page($pagination)->all();

        return [
            'list' => $list,
            'pagination' => $pagination,
        ];
    }

    public function getTimeList()
    {
        $list = MiaoshaGoods::find()->alias('mg')->where([
            'mg.mall_id' => \Yii::$app->mall->id,
            'mg.is_delete' => 0,
            'mg.open_date' => date('Y-m-d'),
            'mg.activity_id' => 0,
        ])->joinWith(['goods AS g' => function ($query) {
            $query->andWhere(['g.status' => 1, 'g.is_delete' => 0]);
        }])->asArray()->all();

        $openTime = [];
        foreach ($list as $item) {
            $openTime[] = $item['open_time'];
        }
        $openTime = array_unique($openTime);
        asort($openTime);

        $newOpenTime = [];
        foreach ($openTime as $key => $item) {
            if ($item > date('H')) {
                $H = strlen($item) > 1 ? $item : 0 . $item;
                $newTime = strtotime(date('Y-m-d') . ' ' . $H . ':00:00');
                $newOpenTime[] = [
                    'open_time' => $item,
                    'new_open_time' => strlen($item) > 1 ? $item . ':00' : '0' . $item . ':00',
                    'label' => '即将开抢',
                    'status' => 0,
                    'time' =>  $newTime - time(),
                    'date_time' =>  date('Y-m-d H:i:s', $newTime),
                ];
            } elseif (date('H') == $item) {
                $newTime = strtotime(date('Y-m-d H') . ':00:00') + 60 * 60;
                $newOpenTime[] = [
                    'open_time' => $item,
                    'new_open_time' => strlen($item) > 1 ? $item . ':00' : '0' . $item . ':00',
                    'label' => '抢购进行中',
                    'status' => 1,
                    'time' =>  $newTime - time(),
                    'date_time' => date('Y-m-d H:i:s', $newTime)
                ];
            } else {
                $newOpenTime[] = [
                    'open_time' => $item,
                    'new_open_time' => strlen($item) > 1 ? $item . ':00' : '0' . $item . ':00',
                    'label' => '已结束',
                    'status' => 2,
                    'time' => 0,
                    'date_time' => '',
                ];
            }
        }

        // 秒杀下一场预告是否存在
        $miaoshaGoods = $this->getNextMiaoshaGoods();
        $nextMiaoshaDateTime = '';
        if ($miaoshaGoods) {
            $time = strlen($miaoshaGoods->open_time) > 1 ?
                $miaoshaGoods->open_time : '0' . $miaoshaGoods->open_time . ':00';
            $nextMiaoshaDateTime = $miaoshaGoods->open_date . ' ' . $time . ':00';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newOpenTime,
                'is_estimate' => $miaoshaGoods ? 1 : 0,
                'next_miaosha_date_time' => $nextMiaoshaDateTime
            ]
        ];
    }

    /**
     * 获取下一场秒杀活动商品
     * @return array|null|\yii\db\ActiveRecord
     */
    private function getNextMiaoshaGoods()
    {
        $miaoshaGoods = MiaoshaGoods::find()->alias('mg')->andWhere([
            'and',
            ['>', 'mg.open_date', date('Y-m-d')],
            ['mg.mall_id' => \Yii::$app->mall->id],
            ['mg.is_delete' => 0],
            ['mg.activity_id' => 0]
        ])
            ->joinWith(['goods AS g' => function ($query) {
                /* @var Query $query */
                $query->andWhere(['g.status' => 1, 'g.is_delete' => 0]);
            }])
            ->orderBy(['mg.open_date' => SORT_ASC])
            ->one();

        return $miaoshaGoods;
    }
}
