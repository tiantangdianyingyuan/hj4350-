<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\Plugin;

class ActivityBatchForm extends Model
{
    public $batch_ids;
    public $status;
    public $is_all;
    public $plugin_sign;
    public $activity_status;

    public function rules()
    {
        return [
            [['status', 'is_all', 'activity_status'], 'integer'],
            [['batch_ids'], 'safe'],
            [['plugin_sign'], 'string']
        ];
    }

    // 批量更新热销
    public function updateHotSell()
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
                'goods_id' => $this->batch_ids,
            ];
        }

        $res = PintuanGoods::updateAll([
            'is_sell_well' => $this->status ? 1 : 0
        ], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    // 批量更新活动状态
    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $pintuanGoodsIds = PintuanGoods::find()->andWhere(['goods_id' => $this->batch_ids, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
        $list = PintuanGoods::find()->andWhere([
            'or',
            ['id' => $pintuanGoodsIds],
            ['pintuan_goods_id' => $pintuanGoodsIds]
        ])->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->all();
        $batchIds = [];
        foreach ($list as $item) {
            $batchIds[] = $item->goods_id;
        }

        $sign = (new Plugin())->getName();
        $where = [
                'mall_id' => \Yii::$app->mall->id,
                'sign' => $sign,
                'id' => $batchIds,
            ];

        $res = Goods::updateAll([
            'status' => $this->activity_status
        ], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    // 批量删除活动
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
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
                $ptWhere = [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ];
            } else {
                $ptGoodsIds = PintuanGoods::find()->where([
                    'goods_id' => $this->batch_ids,
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0
                ])->select('id');
                $list = PintuanGoods::find()->where([
                    'or',
                    ['pintuan_goods_id' => $ptGoodsIds],
                    ['id' => $ptGoodsIds]
                ])->all();
                $goodsIds = [];
                /** @var PintuanGoods $item */
                foreach ($list as $item) {
                    $goodsIds[] = $item->goods_id;
                }
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'sign' => $this->plugin_sign,
                    'id' => $goodsIds,
                ];
                $ptWhere = [
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_id' => $goodsIds,
                ];
            }

            $res = PintuanGoods::updateAll(['is_delete' => 1], $ptWhere);
            $res = Goods::updateAll(['is_delete' => 1], $where);
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
}