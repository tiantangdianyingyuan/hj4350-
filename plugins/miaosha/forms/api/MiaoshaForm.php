<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\mch\forms\common\CommonCat;
use app\plugins\miaosha\models\MiaoshaGoods;

class MiaoshaForm extends Model
{
    public $page;
    public $type;
    public $cat_id;

    public function rules()
    {
        return [
            [['page', 'type', 'cat_id'], 'integer'],
            [['page', 'type'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = MiaoshaGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if ($this->type == 1) {
                // 正在进行的秒杀
                $query->andWhere([
                    'open_date' => date('Y-m-d'),
                    'open_time' => date('H'),
                ]);
            } elseif ($this->type == 2) {
                // 即将开始的秒杀
                $miaoshaGoods = MiaoshaGoods::find()->andWhere([
                    'and',
                    ['>=', 'open_date', date('Y-m-d')],
                    ['>', 'open_time', date('H')]
                ])
                    ->orderBy(['open_date' => SORT_ASC])
                    ->orderBy(['open_time' => SORT_ASC])
                    ->one();
                if ($miaoshaGoods) {
                    // 有即将开始场次
                    $query->andWhere([
                        'open_date' => $miaoshaGoods->open_date,
                        'open_time' => $miaoshaGoods->open_time
                    ]);
                } else {
                    // 无秒杀场次啦
                    $query->andWhere([
                        'and',
                        ['>=', 'open_date', date('Y-m-d')],
                        ['>', 'open_time', date('H')]
                    ]);
                }
            } else {
                throw new \Exception('type参数错误');
            }

            if ($this->cat_id) {
                $goodsIds = $this->getCatGoods($this->cat_id);
                $query->andWhere(['goods_id' => $goodsIds]);
            }


            $list = $query->groupBy('goods_id')
                ->with('goods', 'attr')
                ->orderBy(['created_at' => SORT_DESC])
                ->page($pagination, 10)
                ->asArray()
                ->all();

            $orderIds = Order::find()->where([
                'is_pay' => 1,
                'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            ])
                ->andWhere(['>=', 'created_at', date('Y-m-d H') . " 00:00"])
                ->select('id');

            foreach ($list as &$item) {
                $minPrice = $item['goods']['price'];
                $miaoshaNum = 0;
                foreach ($item['attr'] as $aItem) {
                    $miaoshaNum += $aItem['miaosha_stock'];
                    $minPrice = min($minPrice, $aItem['miaosha_price']);
                }
                $item['min_price'] = $minPrice;

                // 统计已秒杀数量
                $count = OrderDetail::find()->where([
                    'goods_id' => $item['goods']['id'],
                    'order_id' => $orderIds
                ])->count();
                if ($count <= 0) {
                    $miaoshaPercentage = '0%';
                } else {
                    $miaoshaPercentage = round(((int)$count / (int)$miaoshaNum) * 100, 2) . '%';
                }

                $item['miaosha_count'] = (int)$count;
                $item['miaosha_num'] = $miaoshaNum;
                $item['miaosha_percentage'] = $miaoshaPercentage;
                $startDate = date('m-d', strtotime($item['open_date']));
                $startTime = strlen($item['open_time']) > 1 ?
                    $item['open_time'] . ':00' : '0' . $item['open_time'] . ':00';
                $item['start_time'] = $startDate . ' ' . $startTime;
            }


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getTodayMiaosha()
    {
        $query = MiaoshaGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'open_time' => date('H')
        ]);


        if ($this->cat_id) {
            $goodsIds = $this->getCatGoods($this->cat_id);
            $query->andWhere(['goods_id' => $goodsIds]);
        }

        $list = $query->groupBy('goods_id')
            ->with('goods', 'attr')
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->asArray()
            ->all();

        $type = 1;// 区分是今日秒杀还是下一场次的秒杀
        $startTime = 0;
        if (!$list) {
            $type = 2;
            // 无今日秒杀 即显示最近一个场次的秒杀
            $miaoshaGoods = MiaoshaGoods::find()->andWhere([
                'and',
                ['>', 'open_date', date('Y-m-d')],
            ])
                ->orderBy(['open_date' => SORT_ASC])
                ->orderBy(['open_time' => SORT_ASC])
                ->one();

            $query = MiaoshaGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            if ($query) {
                // 有即将开始场次
                $query->andWhere([
                    'open_date' => $miaoshaGoods->open_date,
                    'open_time' => $miaoshaGoods->open_time
                ]);
            } else {
                // 无秒杀场次啦
                $query->andWhere([
                    'and',
                    ['>', 'open_date', date('Y-m-d')],
                ]);
            }

            $list = $query->groupBy('goods_id')
                ->with('goods', 'attr')
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(20)
                ->asArray()
                ->all();

            $H = strlen($miaoshaGoods->open_time) > 1 ? $miaoshaGoods->open_time : '0' . $miaoshaGoods->open_time;
            $startTime = strtotime($miaoshaGoods->open_date . ' ' . $H . ':00:00');
        }

        $newList = [];
        $arr = [];
        foreach ($list as $item) {
            $arr[] = $item;
            if (count($arr) == 4) {
                $newList[] = $arr;
                $arr = [];
            }
        }
        if (count($arr) > 0) {
            $newList[] = $arr;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'surplus_h_time' => strtotime(date('Y-m-d H') . ':59:59') - time(),
                'type' => $type,
                'start_time' => $startTime > 0 ? $startTime - time() : 0,
            ]
        ];
    }

    /**
     * 根据分类查找商品
     * @param $id
     * @return $this
     */
    public function getCatGoods($id)
    {
        if ($id) {
            $catIds = [$id];
            $goodsCats_2 = GoodsCats::find()->where([
                'parent_id' => $id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->asArray()->select('id')->all();

            if ($goodsCats_2) {
                foreach ($goodsCats_2 as $item) {
                    $catIds[] = $item['id'];
                    $goodsCats_3 = GoodsCats::find()->where([
                        'parent_id' => $item['id'],
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ])->asArray()->all();

                    foreach ($goodsCats_3 as $item2) {
                        $catIds[] = $item2['id'];
                    }
                }
            }
        } else {
            $goodsCats = GoodsCats::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])->select('id')->asArray()->all();

            $catIds = [];
            foreach ($goodsCats as $goodsCat) {
                $catIds[] = $goodsCat['id'];
            }
        }
        $catGoodsIds = GoodsCatRelation::find()->alias('gc')->where([
            'gc.cat_id' => $catIds,
            'gc.is_delete' => 0
        ])->select('goods_id');

        return $catGoodsIds;
    }
}
