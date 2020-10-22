<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\MallGoods;

class MsGoodsImporting extends BaseImporting
{
    public function import()
    {
        try {
            foreach ($this->v3Data as $datum) {
                $attrGroups = [];
                foreach ($datum['attr'] as $aItem) {
                    foreach ($aItem['attr_list'] as $alItem) {
                        if (count($attrGroups) == 0) {
                            $arr = [];
                            $arr['attr_group_id'] = $alItem['attr_group_id'];
                            $arr['attr_group_name'] = $alItem['attr_group_name'];
                            $arr['attr_list'][] = [
                                'attr_id' => $alItem['attr_id'],
                                'attr_name' => $alItem['attr_name'],
                            ];
                            $attrGroups[] = $arr;
                        } else {
                            $sign = true;
                            foreach ($attrGroups as &$attrGroup) {
                                if ($attrGroup['attr_group_id'] == $alItem['attr_group_id']) {
                                    $sign2 = true;
                                    foreach ($attrGroup['attr_list'] as $galItem) {
                                        if ($galItem['attr_id'] == $alItem['attr_id']) {
                                            $sign2 = false;
                                        }
                                    }
                                    if ($sign2) {
                                        $attrGroup['attr_list'][] = [
                                            'attr_id' => $alItem['attr_id'],
                                            'attr_name' => $alItem['attr_name'],
                                        ];
                                    }
                                    $sign = false;
                                }
                            }
                            if ($sign) {
                                $arr = [];
                                $arr['attr_group_id'] = $alItem['attr_group_id'];
                                $arr['attr_group_name'] = $alItem['attr_group_name'];
                                $arr['attr_list'][] = [
                                    'attr_id' => $alItem['attr_id'],
                                    'attr_name' => $alItem['attr_name'],
                                ];
                                $attrGroups[] = $arr;
                            }
                        }
                    }
                }

                $newAttrGroups = $this->addAttrGroupsId($attrGroups);
                $attr = $this->handleAttr($datum['attr']);

                $goodsWarehouse = new GoodsWarehouse();
                $goodsWarehouse->mall_id = $this->mall->id;
                $goodsWarehouse->name = $datum['name'];
                $goodsWarehouse->original_price = $datum['original_price'];
                $goodsWarehouse->cost_price = $datum['original_price'];
                $goodsWarehouse->detail = $datum['detail'];
                $goodsWarehouse->cover_pic = $datum['cover_pic'] ?: '/';
                $goodsWarehouse->pic_url = \Yii::$app->serializer->encode($datum['goodsPicList']);
                $goodsWarehouse->video_url = $datum['video_url'] ?: '';
                $goodsWarehouse->unit = $datum['unit'];
                $goodsWarehouse->created_at = date('Y-m-d H:i:s', $datum['addtime']);
                $goodsWarehouse->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
                $res = $goodsWarehouse->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsWarehouse));
                }

                // $fullCut = \Yii::$app->serializer->decode($datum['full_cut']);
                $integral = [];
                if ($datum['integral']) {
                    $integral = \Yii::$app->serializer->decode($datum['integral']);
                }

//                if ($datum['name'] == 'EAFIN\'S 2018原创设计潮牌夹克男TPU花膜特氟龙三防两件套冲锋衣') {
//                    dd($newAttrGroups);
//                }

                $goods = new Goods();
                $goods->mall_id = $this->mall->id;
                $goods->goods_warehouse_id = $goodsWarehouse->id;
                $goods->status = $datum['status'];
                $goods->price = $datum['original_price'];
                $goods->use_attr = $datum['use_attr'];
                $goods->attr_groups = \Yii::$app->serializer->encode($newAttrGroups);
                $goods->goods_stock = 0;
                $goods->virtual_sales = $datum['virtual_sales'] ? $datum['virtual_sales'] : 0;
                $goods->confine_count = -1;
                $goods->pieces = 0;
                $goods->forehead = 0;
                $goods->freight_id = 0;
//                $goods->give_integral = '';
//                $goods->give_integral_type = '';
                $goods->forehead_integral = isset($integral['forehead']) && $integral['forehead'] ? $integral['forehead'] : 0;
                $goods->forehead_integral_type = 1;
                $goods->accumulative = 0;
                $goods->individual_share = 0;
                $goods->attr_setting_type = $datum['attr_setting_type'];
                $goods->is_level = 0;
                $goods->is_level_alone = 0;
                $goods->share_type = $datum['share_type'];
                $goods->created_at = date('Y-m-d H:i:s', $datum['addtime']);
                $goods->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
                $res = $goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goods));
                }

                $mallGoods = new MallGoods();
                $mallGoods->mall_id= $this->mall->id;
                $mallGoods->goods_id= $goods->id;
                $mallGoods->is_quick_shop= 0;
                $mallGoods->is_sell_well= 0;
                $mallGoods->is_negotiable= 0;
                $mallGoods->created_at= date('Y-m-d H:i:s', $datum['addtime']);
                $mallGoods->updated_at= date('Y-m-d H:i:s', $datum['addtime']);
                $res = $mallGoods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mallGoods));
                }

                foreach ($attr as $aItem) {
                    $signIds = '';
                    foreach ($aItem['attr_list'] as $aLItem) {
                        $signIds .= $signIds ? ':' . (int)$aLItem['attr_id'] : (int)$aLItem['attr_id'];
                    }
                    // 商品规格
                    $goodsAttr = new GoodsAttr();
                    $goodsAttr->goods_id = $goods->id;
                    $goodsAttr->sign_id = $signIds;
                    $goodsAttr->stock = $aItem['num'];
                    $goodsAttr->price = $aItem['price'] ?: $datum['original_price'];
                    $goodsAttr->no = $aItem['no'];
                    $goodsAttr->pic_url = isset($aItem['pic']) ? $aItem['pic'] : '';
                    $goodsAttr->weight = (int)$datum['weight'];
                    $res = $goodsAttr->save();
                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($goodsAttr));
                    }
                }
                // 商品分类
//                if (isset(CatImporting::$catIds[$datum['cat_id']])) {
//                    $goodsCatRelation = new GoodsCatRelation();
//                    $goodsCatRelation->goods_warehouse_id = $goodsWarehouse->id;
//                    $goodsCatRelation->cat_id = CatImporting::$catIds[$datum['cat_id']];
//                    $res = $goodsCatRelation->save();
//                    if (!$res) {
//                        throw new \Exception($this->getErrorMsg($goodsCatRelation));
//                    }
//                }
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function handleAttr($attr)
    {
        foreach ($attr as &$item) {
            foreach ($item['attr_list'] as &$alItem) {
                $alItem['attr_group_id'] = $this->newAttrGroupList[$alItem['attr_group_name']];
                $alItem['attr_id'] = $this->newAttrList[$alItem['attr_name']];
            }
            unset($alItem);
        }
        unset($item);

        return $attr;
    }

    private $newAttrGroupList = [];
    private $newAttrList = [];

    private function addAttrGroupsId($list, &$id = 1)
    {
        $newId = 1;
        foreach ($list as $key => $item) {
            if (isset($item['attr_list'])) {
                $this->newAttrGroupList[$item['attr_group_name']] = $newId;
                $list[$key]['attr_group_id'] = $newId++;
                $newItemList = $this->addAttrGroupsId($item['attr_list'], $id);
                $list[$key]['attr_list'] = $newItemList;
            } else {
                $this->newAttrList[$item['attr_name']] = $id;
                $list[$key]['attr_id'] = $id++;
            }
        }
        return $list;
    }
}