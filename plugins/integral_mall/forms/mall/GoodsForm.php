<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\integral_mall\models\Goods;
use app\plugins\integral_mall\models\IntegralMallGoods;
use app\plugins\integral_mall\models\IntegralMallGoodsAttr;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $search;
    public $sort;
    public $batch_ids;
    public $status;
    public $page;
    public $goods_integral;

    public function rules()
    {
        return [
            [['id', 'sort', 'status', 'page', 'goods_integral'], 'integer'],
            [['search', 'batch_ids'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
            'id' => '商品ID'
        ];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);
        $form = new CommonGoodsList();
        $form->keyword = $search['keyword'];
        $form->model = 'app\plugins\integral_mall\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['integralMallGoods', 'goodsWarehouse.cats', 'attr'];
        $form->is_array = 1;

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $form->sort = 6;
            $form->sort_prop = $search['sort_prop'];
            $form->sort_type = $search['sort_type'];
        } else {
            $form->sort = 2;
        }

        $form->page = $this->page;
        if (array_key_exists('status', $search) && $search['status'] != -1) {
            if ($search['status'] == 0 || $search['status'] == 1) {
                $form->status = $search['status'];
            } else if ($search['status'] == 2) {
                $form->is_sold_out = 1;
            }
        }

        $list = $form->search();

        foreach ($list as &$item) {
            $item['status'] = (int)$item['status'];
            $item['integralMallGoods']['is_home'] = (int)$item['integralMallGoods']['is_home'];
            $item['cats'] = $item['goodsWarehouse']['cats'];

            $goodsStock = 0;
            foreach ($item['attr'] as $aItem) {
                $goodsStock += $aItem['stock'];
            }
            $item['goods_stock'] = $goodsStock;
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function getDetail()
    {
        $form = new CommonGoods();
        $res = $form->getGoodsDetail($this->id);
        /** @var IntegralMallGoods $integralMallGoods */
        $integralMallGoods = IntegralMallGoods::find()->where(['goods_id' => $this->id])->one();
        $res['integralMallGoods'] = $integralMallGoods;
        $res['integral_num'] = $integralMallGoods->integral_num;

        // TODO 待优化 循环查询不太好
        foreach ($res['attr'] as &$item) {
            $integralMallGoodsAttr = IntegralMallGoodsAttr::findOne(['goods_attr_id' => $item['id'], 'is_delete' => 0]);
            if ($integralMallGoodsAttr) {
                $item['integral_num'] = $integralMallGoodsAttr->integral_num;
            } else {
                $item['integral_num'] = 0;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $res,
            ]
        ];
    }

    public function switchSellWell()
    {
        /** @var IntegralMallGoods $goods */
        $goods = IntegralMallGoods::find()->where([
            'goods_id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->one();

        $goods->is_home = $goods->is_home ? 0 : 1;
        $res = $goods->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($goods)
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }

    public function editSort()
    {
        /** @var Goods $goods */
        $goods = Goods::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        $goods->sort = $this->sort;
        $res = $goods->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($goods)
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }

    public function batchUpdateIndex()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $res = IntegralMallGoods::updateAll([
            'is_home' => $this->status
        ], [
            'goods_id' => $this->batch_ids,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateIntegral()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $t = \Yii::$app->db->beginTransaction();
        try {
            $goods_integral = $this->goods_integral;

            //售价
            IntegralMallGoods::updateAll([
                'integral_num' => $goods_integral,
            ], [
                'goods_id' => $this->batch_ids,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            //规格价
            IntegralMallGoodsAttr::updateAll([
                'integral_num' => $goods_integral
            ], [
                'goods_id' => $this->batch_ids,
                'is_delete' => 0
            ]);
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => []
            ];
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $t->rollBack();
        }
    }
}
