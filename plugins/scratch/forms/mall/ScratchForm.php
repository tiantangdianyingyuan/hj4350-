<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\scratch\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\scratch\forms\common\CommonEcard;
use app\plugins\scratch\models\Scratch;
use yii\helpers\ArrayHelper;

class ScratchForm extends Model
{
    public $id;
    public $mall_id;
    public $type;
    public $status;
    public $attr_id;
    public $num;
    public $price;
    public $coupon_id;
    public $stock;
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            [['id', 'mall_id', 'type', 'status', 'num', 'coupon_id', 'stock', 'page'], 'integer'],
            [['price'], 'number', 'min' => 0, 'max' => 999999999],
            [['num', 'stock'], 'integer', 'min' => 0, 'max' => 999999999],
            [['keyword'], 'default', 'value' => ''],
            [['stock', 'coupon_id', 'price', 'num', 'attr_id'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'type' => '类型',
            'status' => '状态 0 关闭 1开启',
            'attr_id' => '商品',
            'num' => '积分数量',
            'price' => '红包价格',
            'coupon_id' => '优惠券',
            'stock' => '库存',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = Scratch::find()->alias('s')->where([
            's.mall_id' => \Yii::$app->mall->id,
            's.is_delete' => 0
        ])
            ->page($pagination)
            ->keyword($this->type, ['s.type' => $this->type])
            ->orderBy("s.status desc,s.id DESC")
            ->with('goods.attr')
            ->with('goods.goodsWarehouse')
            ->with('coupon');
        if ($this->keyword) {
            $goods_id_list = Goods::find()->alias('g')
                ->joinWith(['goodsWarehouse gw'])
                ->where(['like', 'gw.name', $this->keyword])
                ->select('g.id');

            $coupon_id_list = Coupon::find()->where(['like', 'name', $this->keyword])->select('id');
            $query->andWhere([
                'OR',
                ['goods_id' => $goods_id_list],
                ['coupon_id' => $coupon_id_list],
            ]);

            //$query->innerJoin(['ct' => $goods_id_list], 'ct.id = s.`goods_id`');
            //$query1 = clone $query;
            //$query1->innerJoin(['co' => $coupon_id_list], 'co.id = s.`coupon_id`');
            //$query->union($query1);
        }
        $list = $query->asArray()->all();
        array_walk($list, function (&$item) {
            if ($item && $item['type'] == 4 && $item['goods']) {
                $attr = $item['goods']['attr'][0];
                $attr_list = (new Goods())->signToAttr($attr['sign_id'], $item['goods']['attr_groups']);
                $attr_str = '';
                foreach ($attr_list as $item1) {
                    $attr_str .= $item1['attr_group_name'] . ':' . $item1['attr_name'] . ';';
                }
                $item['attr_str'] = $attr_str;
                $item['goods_name'] = $item['goods']['goodsWarehouse']['name'];
                unset($item['attr']);
            };
        });
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Scratch::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }

        if ($model->type == 2) {
            try {
                $coupon = new Coupon();
                $coupon->updateCount($model->stock, 'add', $model->coupon_id);
            } catch (\Exception $e) {
                //排除优惠券删除情况
                \Yii::error($e->getMessage());
            }
        }
        if ($model->type == 4) {
            //$goodsAttr = new GoodsAttr();
            //$goodsAttr->updateStock($model->stock, 'add', $model->attr_id);
            // 返还占用的卡密数据
            CommonEcard::getCommon()->refundEcard([
                'type' => 'occupy',
                'sign' => 'scratch',
                'num' => $model->stock,
                'goods_id' => $model->goods_id,
            ]);
        }

        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //DELETE
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $coupon = Coupon::findAll(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        $list = Scratch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ])
            ->with('attr.goods')
            ->with('coupon')
            ->asArray()
            ->one();

        if ($list && $list['type'] == 4 && $list['attr']) {
            $goodsAttr = GoodsAttr::find()->where(['is_delete' => 0, 'goods_id' => $list['attr']['goods_id']])->asArray()->all();
            foreach ($goodsAttr as $key => $item) {
                $attr_list = (new Goods())->signToAttr($item['sign_id'], $list['attr']['goods']['attr_groups']);
                $attr_str = '';
                foreach ($attr_list as $item1) {
                    $attr_str .= $item1['attr_group_name'] . ':' . $item1['attr_name'] . ';';
                }
                $goodsAttr[$key]['attr_str'] = $attr_str;
            }
            $list['attr_list'] = $goodsAttr;
            $list['goods_name'] = $list['attr']['goods']['name'];
            unset($list['attr']);
        };
        $list = [];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'coupon' => $coupon,
            ]
        ];
    }

    public function editStock()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Scratch::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        try {
            $new_model = clone $model;
            $new_model->stock = $this->stock;

            $this->setCouponCount($model, $new_model);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
        if ($model->type == 4) {
            $add = $this->stock - $model->stock;
            if ($add > 0) {
                // 追加占用
                CommonEcard::getCommon()->occupy($model->goods, $add);
            } elseif ($add < 0) {
                // 返还未占用的
                CommonEcard::getCommon()->refundEcard([
                    'type' => 'occupy',
                    'sign' => 'scratch',
                    'num' => abs($add),
                    'goods_id' => $model->goods_id,
                ]);
            } else {
                // 库存不变 不做处理
            }
        }

        $model->stock = $this->stock;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功'
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            //不支持修改
            $model = new Scratch();
            $model->attributes = $this->attributes;
            $model->mall_id = \Yii::$app->mall->id;

            if ($this->type == 4) {
                $attr_id = $this->attr_id;
                $attr = GoodsAttr::findOne($attr_id);

                $goods = new GoodsEditForm();
                $common = CommonGoods::getCommon();
                $detail = $common->getGoodsDetail($attr->goods_id);
                $goods->attributes = $this->attributes;
                $goods->attributes = $detail;
                $goods->status = 1;
                $goods->attrGroups = ArrayHelper::toArray($detail['attr_groups']);

                foreach ($detail['attr'] as $item) {
                    if ($item['id'] == $attr->id) {
                        $goods->attr = [$item];
                        break;
                    }
                }
                $goods->attr[0]['stock'] = $model->stock;
                $goods->goods_num = $model->stock;
                $goods->save();
                $model->goods_id = $goods->goods_id;
                CommonEcard::getCommon()->occupy($goods->goods, $model->stock);
            }
            $this->setCouponCount($model, $this->attributes);
            if ($model->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            } else {
                return $this->getErrorResponse($model);
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }

    }

    public function editStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = Scratch::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->status = $this->status;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '修改成功'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonGoodsList();
        $form->keyword = $this->keyword;
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->mch_id = 0;
        $form->page = $this->page;
        $list = $form->search();

        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = ArrayHelper::toArray($item->goodsWarehouse);
            $newItem['attr'] = ArrayHelper::toArray($item->attr);
            $newItem['goods_name'] = $newItem['goodsWarehouse']['name'];

            foreach ($newItem['attr'] as $k => $v) {
                $attr_list = (new Goods())->signToAttr($v['sign_id'], $item['attr_groups']);
                $attr_str = '';
                foreach ($attr_list as $v2) {
                    $attr_str .= $v2['attr_group_name'] . ':' . $v2['attr_name'] . ';';
                }
                $newItem['attr'][$k]['attr_str'] = $attr_str;
            }
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

    private function setCouponCount($model, $item)
    {
        $coupon = new Coupon();
        if ($model->type == 2 && $item['type'] == 2) {
            if ($model->coupon_id == $item['coupon_id']) {
                if ($model->stock > $item['stock']) {
                    $num = $model->stock - $item['stock'];
                    $coupon->updateCount($num, 'add', $model->coupon_id);
                } elseif ($model->stock < $item['stock']) {
                    $num = $item['stock'] - $model->stock;
                    $coupon->updateCount($num, 'sub', $model->coupon_id);
                }
            } else {
                try {
                    $coupon->updateCount($model->stock, 'add', $model->coupon_id);
                } catch (\Exception $e) {
                    \Yii::error('优惠券删除=>' . $e);
                }
                $coupon->updateCount($item['stock'], 'sub', $item['coupon_id']);
            }
        } elseif ($model->type == 2) {
            try {
                $coupon->updateCount($model->stock, 'add', $model->coupon_id);
            } catch (\Exception $e) {
                \Yii::error('优惠券删除=>' . $e);
            }
        } elseif ($item['type'] == 2) {
            $coupon->updateCount($item['stock'], 'sub', $item['coupon_id']);
        }
    }
}
