<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\advance\forms\common\CommonForm;
use app\plugins\advance\models\Goods;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;

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

    public function rules()
    {
        return [
            [['id', 'sort', 'status', 'page'], 'integer'],
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
        $form->date_start = $search['date_start'] ?? null;
        $form->date_end = $search['date_end'] ?? null;
        $form->model = 'app\plugins\advance\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['advanceGoods', 'goodsWarehouse.cats', 'attr'];
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
            } else if ($search['status'] == 3) {
                $form->sign = 'advance';
                $form->isSignCondition = 1;
                $form->signWhere = [
                    'AND',
                    ['>=', 'end_prepayment_at', date('Y-m-d H:i:s', time())],
                    ['<=', 'start_prepayment_at', date('Y-m-d H:i:s', time())],
                ];
            } else if ($search['status'] == 4) {
                $form->sign = 'advance';
                $form->isSignCondition = 1;
                $form->signWhere = [
                    'OR',
                    [
                        'AND',
                        ['<', 'end_prepayment_at', mysql_timestamp()],
                        ['>', 'DATE_ADD(`end_prepayment_at`, interval `pay_limit` day)', mysql_timestamp()]],
                    [
                        'AND',
                        ['pay_limit' => -1],
                        ['<', 'end_prepayment_at', mysql_timestamp()],
                    ]
                ];
            } else if ($search['status'] == 5) {
                $form->sign = 'advance';
                $form->isSignCondition = 1;
                $form->signWhere = [
                    'AND',
                    ['<', 'DATE_ADD(`end_prepayment_at`, interval `pay_limit` day)', mysql_timestamp()],
                    ['!=', 'pay_limit', -1]
                ];
            }
        }

        $list = $form->search();

        foreach ($list as &$item) {
            if ($item['use_attr'] == 0) {
                $item['display_deposit'] = $item['advanceGoods']['deposit'];
            } else {
                $displayDeposit = [];
                foreach ($item['attr'] as $value) {
                    $advanceGoodsAttr = AdvanceGoodsAttr::findOne(['goods_attr_id' => $value['id'], 'is_delete' => 0]);
                    if ($advanceGoodsAttr) {
                        $displayDeposit[] = $advanceGoodsAttr->deposit;
                    } else {
                        $displayDeposit[] = 0;
                    }
                }
                $item['display_deposit'] = min($displayDeposit)."~".max($displayDeposit);
            }

            $item['status'] = (int)$item['status'];
            $item['cats'] = $item['goodsWarehouse']['cats'];
            $goodsStatus = CommonForm::timeSlot($item['advanceGoods']);
            switch ($goodsStatus) {
                case 1:
                    $goodsStatusText = '预售前';
                    break;
                case 2:
                    $goodsStatusText = '预售中';
                    break;
                case 3:
                    $goodsStatusText = '支付尾款';
                    break;
                case 4:
                    $goodsStatusText = '已结束';
                    break;
                default:
                    $goodsStatusText = '未知状态';
                    break;
            }
            $item['goods_status'] = $goodsStatusText;

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
                'hide_function' => []
            ]
        ];
    }

    public function getDetail()
    {
        $form = new CommonGoods();
        $res = $form->getGoodsDetail($this->id);
        /** @var AdvanceGoods $advanceGoods */
        $advanceGoods = AdvanceGoods::find()->where(['goods_id' => $this->id])->one();
        $res['advanceGoods'] = $advanceGoods;
        $res['deposit'] = $advanceGoods->deposit;
        $res['swell_deposit'] = $advanceGoods->swell_deposit;
        $res['ladder_rules'] = json_decode($advanceGoods->ladder_rules,true);

        // TODO 待优化 循环查询不太好
        foreach ($res['attr'] as &$item) {
            $advanceGoodsAttr = AdvanceGoodsAttr::findOne(['goods_attr_id' => $item['id'], 'is_delete' => 0]);
            if ($advanceGoodsAttr) {
                $item['deposit'] = $advanceGoodsAttr->deposit;
                $item['swell_deposit'] = $advanceGoodsAttr->swell_deposit;
            } else {
                $item['deposit'] = 0;
                $item['swell_deposit'] = 0;
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
        /** @var AdvanceGoods $goods */
        $goods = AdvanceGoods::find()->where([
            'goods_id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->one();

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

    public function switchStatus()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;

            $advanceGoods = AdvanceGoods::findOne(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $this->id]);

            $oldStatus = CommonForm::timeSlot($advanceGoods);

            if ($oldStatus == 4 && $goods->status == 1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '商品已过付尾款时间,无法上架'
                ];
            }

            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
