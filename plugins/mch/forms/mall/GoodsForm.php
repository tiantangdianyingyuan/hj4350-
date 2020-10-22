<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsCats;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\mch\models\Goods;
use app\plugins\mch\models\MchGoods;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $search;
    public $page;
    public $mch_id;
    public $type;
    public $remark;
    public $sort;
    public $batch_ids;
    public $status;

    public function rules()
    {
        return [
            [['id', 'page', 'mch_id', 'type', 'sort', 'status'], 'integer'],
            [['search', 'batch_ids'], 'safe'],
            [['remark'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $search = json_decode($this->search, true);
        $form = new CommonGoodsList();
        $form->keyword = $search['keyword'];
        $form->relations = ['goodsWarehouse.cats', 'goodsWarehouse.mchCats', 'attr', 'mallGoods', 'mch.store'];
        $form->mch_id = -1;
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->page = $this->page;

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $form->sort = 6;
            $form->sort_prop = $search['sort_prop'];
            $form->sort_type = $search['sort_type'];
        } else {
            $form->sort = 2;
        }

        if (array_key_exists('status', $search) && $search['status'] != -1) {
            if ($search['status'] == 0 || $search['status'] == 1) {
                $form->status = $search['status'];
            } else if ($search['status'] == 2) {
                $form->is_sold_out = 1;
            }
        }

        if (array_key_exists('cats', $search) && $search['cats']) {
            $form->cat_id = $search['cats'];
        }

        $list = $form->search();

        $newList = [];
        foreach ($list as &$item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = ArrayHelper::toArray($item->goodsWarehouse);
            $newItem['mchGoods'] = ArrayHelper::toArray($item->mchGoods);
            $newItem['cats'] = ArrayHelper::toArray($item->goodsWarehouse->cats);
            $newItem['mallGoods'] = ArrayHelper::toArray($item->mallGoods);
            $newItem['store'] = ArrayHelper::toArray($item->mch->store);
            $newItem['mch'] = ArrayHelper::toArray($item->mch);
            $goodsStock = 0;
            foreach ($item->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            $newItem['goods_stock'] = $goodsStock;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            //todo
            $r = (\Yii::$app->request->get()['r']);
            parse_str($r, $res);
            \Yii::$app->setMchId($res['mch_id']);
            $form = new CommonGoods();
            $detail = $form->getGoodsDetail($this->id);

            if ($detail) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'detail' => $detail
                    ]
                ];
            }

            throw new \Exception('请求失败');
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    public function switchStatus()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /**
             * @var Goods $goods
             */
            $goods = Goods::find()->where([
                'id' => $this->id,
                'mch_id' => $this->mch_id,
                'is_delete' => 0,
            ])->with('mchGoods')->one();

            if (!$goods || !$goods->mchGoods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            if ($goods->status) {
                // 更改申请状态
                $goods->mchGoods->status = 2;
                $goods->mchGoods->remark = '该商品由管理员直接上架';
            } else {
                // 如果是下架,需要还原相关信息
                $goods->mchGoods->status = 0;
                $goods->mchGoods->remark = '';
            }
            $res = $goods->mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods->mchGoods));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchSwitchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /**
             * @var Goods $goods
             */

            $res = Goods::updateAll([
                'status' => $this->status
            ],[
                'id' => $this->batch_ids,
                'is_delete' => 0
            ]);

            if ($this->status) {
                $res = MchGoods::updateAll([
                    'status' => 2,
                    'remark' => '该商品由管理员直接上架'
                ], [
                    'goods_id' => $this->batch_ids,
                    'is_delete' => 0
                ]);
            } else {
                $res = MchGoods::updateAll([
                    'status' => 0,
                    'remark' => ''
                ], [
                    'goods_id' => $this->batch_ids,
                    'is_delete' => 0
                ]);
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'res' => $res
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

    public function auditSubmit()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods = Goods::find()->where([
                'id' => $this->id,
                'mch_id' => $this->mch_id,
                'is_delete' => 0,
            ])->one();

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $mchGoods = MchGoods::findOne(['goods_id' => $this->id, 'mch_id' => $this->mch_id]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }
            // 同意上架
            if ($this->type == 1) {
                $goods->status = 1;
                $res = $goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goods));
                }

                $mchGoods->status = 2;
            } else {
                // 拒绝上架
                $mchGoods->status = 3;
            }
            $mchGoods->remark = $this->remark;
            $res = $mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($mchGoods));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getCatList()
    {
        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id,
            'is_delete' => 0,
        ])->orderBy('sort DESC')->asArray()->all();

        $newList = [];
        // 一级分类
        foreach ($list as $key => $item) {
            if ($item['parent_id'] == 0) {
                $newList[] = [
                    'value' => $item['id'],
                    'label' => $item['name'],
                ];
                unset($list[$key]);
            }
        }
        $list = array_values($list);

        // 二级分类
        foreach ($newList as &$item) {
            foreach ($list as $lKey => $lItem) {
                if ($item['value'] == $lItem['parent_id']) {
                    $item['children'][] = [
                        'value' => $lItem['id'],
                        'label' => $lItem['name'],
                    ];
                    unset($list[$lKey]);
                }
            }
        }
        $list = array_values($list);

        // 三级分类
        foreach ($newList as &$item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as &$cItem) {
                    foreach ($list as $lItem) {
                        if ($cItem['value'] == $lItem['parent_id']) {
                            $cItem['children'][] = [
                                'value' => $lItem['id'],
                                'label' => $lItem['name'],
                            ];
                        }
                    }
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList
            ]
        ];
    }

    public function editSort()
    {
        try {
            $mchGoods = MchGoods::findOne(['goods_id' => $this->id]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }

            $mchGoods->sort = $this->sort;
            $res = $mchGoods->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($mchGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "更新成功"
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
