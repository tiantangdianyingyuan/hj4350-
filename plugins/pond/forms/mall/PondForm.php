<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\pond\models\Pond;
use yii\helpers\ArrayHelper;

class PondForm extends Model
{
    public $list;
    public $keyword;
    public $page;
    public function rules()
    {
        return [
            [['list'], 'trim'],
            [['keyword'], 'string'],
            [['page'], 'integer'],
            [['keyword'], 'default', 'value' => '']
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $coupon = Coupon::findAll(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        $list = Pond::find()->where([
                'mall_id' => \Yii::$app->mall->id
            ])
            ->page($pagination)
            ->with(['goods.goodsWarehouse', 'goods.attr', 'coupon'])
            ->asArray()
            ->all();
        array_walk($list, function (&$item) {

            $item['coupon_id'] = (int)$item['coupon_id'];
            $item['attr_id'] = 0;
            try {
                if ($item['type'] == 4) {
                    $common = CommonGoods::getCommon();
                    $detail = $common->getGoodsDetail($item['goods_id']);
                    foreach ($detail['attr'] as $key2 => $item2) {
                        $attr_list = (new Goods())->signToAttr($item2['sign_id'], $detail['attr_groups']);
                        $attr_str = '';
                        foreach ($attr_list as $item3) {
                                $attr_str .= $item3['attr_group_name'] . ':' . $item3['attr_name'] . ';';
                        }
                        $detail['attr'][$key2]['attr_str'] = $attr_str;
                    }

                    $attr = $item['goods']['attr'][0];
                    $item['attr_id'] = (int)$attr['id'];
                    $item['attr_list'] = $detail['attr'];
                    $item['goods_name'] = $item['goods']['goodsWarehouse']['name'];
                }
            } catch (\Exception $e) {
                $item['type'] = "5";
            }

            unset($item['attr']);
        });
        unset($item);
//追加
        $num = 8 - $pagination->total_count;
        if ($num > 0) {
            for (
                $i = 0; $i < $num;
                $i++
            ) {
                array_push($list, [
                    'id' => 0,
                    'type' => '5',
                    'image_url' => '',
                    'goods_id' => 0,
                    'name' => '',
                    'num' => 0,
                    'price' => 0,
                    'coupon_id' => 0,
                    'stock' => 0,
                    'attr_id' => 0,
                ]);
            };
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'coupon' => $coupon,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $list = $this->list;
            $t = \Yii::$app->db->beginTransaction();
            if (!in_array('5', array_column($list, 'type'))) {
                throw new \Exception("至少选择一个'谢谢参与'");
            };
            foreach ($list as $item) {
                $model = Pond::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $item['id'], ]);
                if (!$model) {
                    $count = Pond::find()->where(['mall_id' => \Yii::$app->mall->id])->count();
                    if ($count + 1 > 8) {
                        throw new \Exception('保存失败，请刷新重试');
                    }
                    $model = new Pond();
                }
                $this->setCouponCount($model, $item);
                $model->mall_id = \Yii::$app->mall->id;
                $model->name = $item['name'];
                $model->type = $item['type'];
                $model->num = $item['num'];
                $model->price = $item['price'];
                $model->image_url = $item['image_url'];
                $model->coupon_id = $item['coupon_id'];
                $model->stock = $item['stock'];
                if ($model->type == 4) {
                    $attr_id = $item['attr_id'];
                    $attr = GoodsAttr::findOne($attr_id);
                    if (!$attr) {
                        throw new \Exception('规格信息错误');
                    }
                    $goods = new GoodsEditForm();
                    $common = CommonGoods::getCommon();
                    $detail = $common->getGoodsDetail($attr->goods_id);
                    $goods->attributes = $this->attributes;
                    $goods->attributes = $detail;
                    $goods->status = 1;
                    $goods->attrGroups = ArrayHelper::toArray($detail['attr_groups']);
                    foreach ($detail['attr'] as $item1) {
                        if ($item1['id'] == $attr->id) {
                            $goods->attr = [$item1];
                            break;
                        }
                    }
                    $goods->attr[0]['stock'] = $item['stock'];
                    $goods->goods_num = $item['stock'];
                    $goods->save();
                    $model->goods_id = $goods->goods_id;
                }
                if (!$model->save()) {
                    $t->rollBack();
                    return $this->getErrorResponse($model);
                }
            };
            $t->commit();
            return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
        } catch (\Exception $e) {
            $t->rollBack();
            return ['code' => ApiCode::CODE_ERROR, 'msg' => $e->getMessage()];
        }
    }


    //商品搜索
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
