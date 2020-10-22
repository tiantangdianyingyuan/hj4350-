<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;
use yii\db\ActiveQuery;

class ActivityForm extends Model
{
    public $is_all;
    public $batch_ids;
    public $activity_status;
    public $goods_status;
    public $activity_id;
    public $id; // 活动ID 与 activity_id 相同
    public $search;

    public function rules()
    {
        return [
            [['is_all', 'activity_status', 'activity_id', 'goods_status', 'id'], 'integer'],
            [['batch_ids', 'search'], 'safe'],
        ];
    }

    // 批量更新活动状态
    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->batch_ids,
            ];
        }

        $res = MiaoshaActivitys::updateAll(['status' => $this->activity_status], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    // 批量删除
    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $sign = (new Plugin())->getName();
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ];
                $goodsWhere = array_merge($where, ['sign' => $sign]);
                $msGoodsWhere = array_merge($where, []);
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->batch_ids,
                ];
                $list = MiaoshaGoods::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'activity_id' => $this->batch_ids
                ])->select('goods_id')->all();
                $goodsIds = [];
                /** @var MiaoshaGoods $item */
                foreach ($list as $item) {
                    $goodsIds[] = $item->goods_id;
                }
                $goodsWhere = array_merge($where, ['sign' => $sign, 'id' => $goodsIds]);
                $msGoodsWhere = array_merge($where, ['activity_id' => $this->batch_ids]);
            }

            $res = MiaoshaGoods::updateAll(['is_delete' => 1], $msGoodsWhere);
            $res = Goods::updateAll(['is_delete' => 1], $goodsWhere);
            $res = MiaoshaActivitys::updateAll(['is_delete' => 1], $where);

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    // 活动详情场次列表
    public function activityGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            try {
                $this->search = \Yii::$app->serializer->decode($this->search);
            } catch (\Exception $exception) {
                $this->search = [];
            }

            /** @var BaseActiveQuery $query */
            $query = MiaoshaGoods::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->andWhere(['>', 'activity_id', 0]);

            $activity = [];
            if ($this->activity_id) {
                $query->andWhere(['activity_id' => $this->activity_id]);
                $activity = $this->getActivityInfo();
            } else {
                $activityIds = MiaoshaActivitys::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ])->select('id');
                $query->andWhere(['activity_id' => $activityIds]);
            }


            $query = $this->setDate($query);
            $query = $this->setKeyword($query);
            $query = $this->setStatus($query);
            $query->orderBy("CASE
                    WHEN `open_date` = '" . date('Y-m-d') . "' THEN
	                    1
                    WHEN `open_date` > '" . date('Y-m-d') . "' THEN
	                    2
                    WHEN `open_date` < '" . date('Y-m-d') . "' THEN
	                    3
                    END,
                    `activity_id`,
                    `open_date`,
                    `open_time`"
            );
            $list = $query->page($pagination)->all();

            $newList = [];
            /** @var MiaoshaGoods $item */
            foreach ($list as $item) {
                $newItem = [];
                $newItem['id'] = $item->goods_id;
                $newItem['miaosha_goods_id'] = $item->id;
                $newItem['activity_id'] = $item->activity_id;
                $newItem['price'] = $item->goods->price;
                $H = strlen($item->open_time) > 1 ? $item->open_time : '0' . $item->open_time;
                $newItem['date_time'] = $item->open_date . ' ' . $H . ':00';
                $newItem['payment_num'] = $item->goods->payment_num;
                $newItem['payment_amount'] = $item->goods->payment_amount;
                $newItem['activity_status'] = $this->getGoodsStatus($item);

                // 总活动数据列表需要商品信息
                $newItem['goods_name'] = $item->goods->name;
                $newItem['goods_cover_pic'] = $item->goods->coverPic;
                $minPrice = 0;
                foreach ($item->goods->attr as $attrItem) {
                    $minPrice = $minPrice == 0 ? $attrItem->price : min($minPrice, $attrItem->price);
                }
                $newItem['goods_miaosha_price'] = $minPrice;
                $newItem['goods_id'] = $item->goods_id;

                $isShowEdit = 1;
                $todayDate = date('Y-m-d');
                if ($item->open_date < $todayDate) {
                    $isShowEdit = 0;
                } elseif ($item->open_date == $todayDate) {
                    $H = date('H');
                    if ($item->open_time < $H) {
                        $isShowEdit = 0;
                    }
                }
                $newItem['is_show_edit'] = $isShowEdit;
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'activity' => $activity,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    /**
     * @param ActiveQuery $query
     * @return mixed
     */
    private function setKeyword($query)
    {
        $search = $this->search;
        if (isset($search['keyword']) && $search['keyword']) {
            $goodsWarehouseIds = GoodsWarehouse::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->andWhere(['like', 'name', $search['keyword']])->select('id');
            $goodsIds = Goods::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'goods_warehouse_id' => $goodsWarehouseIds
            ])->select('id');
            $query->andWhere(['goods_id' => $goodsIds]);
        }

        return $query;
    }

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setStatus($query)
    {
        if (isset($this->search['status']) && $this->search['status'] != 1) {
            $todayDate = date('Y-m-d');
            $H = date('H');
            switch ($this->search['status']) {
                // 未开始
                case 2:
                    $query->andWhere([
                        'or',
                        ['>', 'open_date', $todayDate],
                        [
                            'and',
                            ['open_date' => $todayDate],
                            ['>', 'open_time', $H]
                        ]
                    ]);
                    break;
                // 进行中
                case 3:
                    $query->andWhere([
                        'and',
                        ['open_date' => $todayDate],
                        ['open_time' => $H]
                    ]);
                    break;
                // 已结束
                case 4:
                    $query->andWhere([
                        'or',
                        ['<', 'open_date', $todayDate],
                        [
                            'and',
                            ['open_date' => $todayDate],
                            ['<', 'open_time', $H]
                        ]
                    ]);
                    break;
                // 下架中
                case 5:
                    $sign = \Yii::$app->plugin->currentPlugin->getName();
                    $goodsIds = Goods::find()
                        ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => $sign, 'status' => 0])
                        ->select('id');
                    $query->andWhere(['goods_id' => $goodsIds]);
                    break;
            }
        }

        return $query;
    }


    // 批量更新秒杀场次上架状态
    public function batchUpdateGoodsStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->batch_ids,
            ];
        }

        $res = MiaoshaGoods::updateAll(['status' => $this->goods_status], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    // 批量删除秒杀场次
    public function batchDestroyGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->batch_ids,
            ];
        }

        $res = MiaoshaGoods::updateAll(['is_delete' => 1], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    /**
     * @param MiaoshaGoods $goods
     * @return string $status
     */
    private function getGoodsStatus($goods)
    {
        $todayDate = date('Y-m-d');
        if ($goods->open_date < $todayDate) {
            $status = '已结束';
        } elseif ($goods->open_date == $todayDate) {
            $H = date('H');
            if ($goods->open_time < $H) {
                $status = '已结束';
            } elseif ($goods->open_time == $H) {
                $status = '进行中';
            } else {
                $status = '未开始';
            }
        } else {
            $status = '未开始';
        }

        if ($goods->goods->status == 0) {
            $status = '下架中';
        }

        return $status;
    }

    /**
     * 获取活动信息
     * @return array
     * @throws \Exception
     */
    private function getActivityInfo()
    {
        /** @var MiaoshaActivitys $activity */
        $activity = MiaoshaActivitys::find()->where(['id' => $this->activity_id, 'is_delete' => 0])->with('oneMiaoshaGoods')->one();
        if (!$activity) {
            throw new \Exception('活动不存在');
        }
        $query = MiaoshaGoods::find()->where(['activity_id' => $activity->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        $query = $this->setDate($query);
        $count = $query->count();
        $newActivity = [];
        $newActivity['id'] = $activity->id;
        $newActivity['goods_name'] = $activity->oneMiaoshaGoods->goods->name;
        $newActivity['goods_cover_pic'] = $activity->oneMiaoshaGoods->goods->coverPic;
        $newActivity['goods_miaosha_price'] = $activity->oneMiaoshaGoods->goods->price;
        $newActivity['miaosha_count'] = $count;

        return $newActivity;
    }

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setDate($query) {
        if (isset($this->search['date_start']) && $this->search['date_start'] && isset($this->search['date_end']) && $this->search['date_end']) {
            $startH = date('H', strtotime($this->search['date_start']));
            $endH = date('H', strtotime($this->search['date_end']));
            $query->andWhere([
                'and',
                [
                    'or',
                    ['>', 'open_date', date('Y-m-d', strtotime($this->search['date_start']))],
                    [
                        'and',
                        ['=', 'open_date', date('Y-m-d', strtotime($this->search['date_start']))],
                        ['>=', 'open_time', $startH],
                    ]
                ],
                [
                    'or',
                    ['<', 'open_date', date('Y-m-d', strtotime($this->search['date_end']))],
                    [
                        'and',
                        ['=', 'open_date', date('Y-m-d', strtotime($this->search['date_end']))],
                        ['<=', 'open_time', $endH],
                    ]
                ]
            ]);
        }

        return $query;
    }
}