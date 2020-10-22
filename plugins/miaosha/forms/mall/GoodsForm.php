<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaGoods;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $goods_id;
    public $page;
    public $search;
    public $status;
    public $goods_warehouse_id;
    public $choose_list;
    public $batch_ids;
    public $is_all;
    public $plugin_sign;
    public $continue_goods_count;
    public $continue_order_count;
    public $is_goods_confine;
    public $is_order_confine;
    public $freight_id;
    public $activity_id;

    public function rules()
    {
        return [
            [['id', 'page', 'goods_id', 'status', 'goods_warehouse_id', 'is_all',
                'is_goods_confine', 'is_order_confine', 'continue_goods_count', 'continue_order_count',
                'freight_id', 'activity_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['search', 'choose_list', 'batch_ids'], 'safe'],
            [['plugin_sign'], 'string'],
        ];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\miaosha\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->keyword = $search['keyword'];
        $form->relations = ['goodsWarehouse.cats', 'miaoshaGoods'];
        $form->getQuery();
        $list = $form->query->groupBy('goods_warehouse_id')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = $item->goodsWarehouse ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['miaoshaGoods'] = $item->miaoshaGoods ? ArrayHelper::toArray($item->miaoshaGoods) : [];
            try {
                $newItem['goodsWarehouse']['cats'] = $item->goodsWarehouse->cats ? ArrayHelper::toArray($item->goodsWarehouse->cats) : [];
            } catch (\Exception $exception) {
                $newItem['goodsWarehouse']['cats'] = [];
            }
            $count = MiaoshaGoods::find()->where([
                'goods_warehouse_id' => $item->goods_warehouse_id,
                'mall_id' => $item->mall_id,
                'is_delete' => 0,
            ])->count();
            $newItem['miaosha_count'] = $count;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function getMiaoshaList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $search = \Yii::$app->serializer->decode($this->search);

        $query = MiaoshaGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'goods_warehouse_id' => $this->id
        ]);

        if ($search['date_start'] && $search['date_end']) {
            $query->andWhere([
                'and',
                ['>=', 'open_date', $search['date_start']],
                ['<=', 'open_date', $search['date_end']],
            ]);
        }

        $list = $query->with('goods')->orderBy(['open_date' => SORT_ASC, 'open_time' => SORT_ASC])
            ->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            $isShowStatus = 1;
            if ($item['open_date'] < date('Y-m-d')) {
                $isShowStatus = 0;
            }
            if ($item['open_date'] == date('Y-m-d') && $item['open_time'] < date('H')) {
                $isShowStatus = 0;
            }
            $item['is_show_status'] = $isShowStatus;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        // 秒杀商品 必须从秒杀表开始查询
        /** @var MiaoshaGoods $miaoshaGoods */
        $miaoshaGoods = MiaoshaGoods::find()->where(['id' => $this->id])->with('activity')->one();
        if (!$miaoshaGoods) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '秒杀商品不存在'
            ];
        }

        $form = new CommonGoods();
        $res = $form->getGoodsDetail($miaoshaGoods->goods_id);
        $res['miaoshaGoods'] = ArrayHelper::toArray($miaoshaGoods);
        $res['activity'] = $miaoshaGoods->activity;
        $res['open_time'] = [$miaoshaGoods->open_time];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $res,
            ]
        ];
    }

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }


        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'goods_warehouse_id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['is_delete' => 1], $where);

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_warehouse_id' => $this->batch_ids,
                ];
            }
            $res = MiaoshaGoods::updateAll(['is_delete' => 1], $where);
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchMiaoshaDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
            $res = Goods::updateAll(['is_delete' => 1], $where);

            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $this->batch_ids,
            ];

            $res = MiaoshaGoods::updateAll(['is_delete' => 1], $where);
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['status' => $this->status], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateFreight()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['freight_id' => $this->freight_id], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateConfineCount()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            if ($this->continue_goods_count < 0 && !$this->is_goods_confine) {
                throw new \Exception('限购商品数量不能小于0');
            }

            if ($this->continue_order_count < 0 && !$this->is_order_confine) {
                throw new \Exception('限购订单数量不能小于0');
            }

            $goodsCount = (int)$this->continue_goods_count;
            if ($this->is_goods_confine || $goodsCount < 0) {
                $goodsCount = -1;
            }

            $orderCount = (int)$this->continue_order_count;
            if ($this->is_order_confine || $orderCount < 0) {
                $orderCount = -1;
            }

            $res = Goods::updateAll(['confine_count' => $goodsCount, 'confine_order_count' => $orderCount], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
