<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\vip_card\forms\common;

use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Model;

class GoodsEditForm extends Model
{
    public function saveGoods()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $pluginName = 'vip_card';
            $baseUrl = PluginHelper::getPluginBaseAssetsUrl($pluginName) . '/img/';

            $goods = Goods::find()->where(['sign' => $pluginName, 'is_delete' => 0, 'mall_id' => 0,])->one();
            if (!$goods) {
                $goodsWarehouse = new GoodsWarehouse();
                $goodsWarehouse->mall_id = 0;
                $goodsWarehouse->name = '超级会员卡';
                $goodsWarehouse->detail = '超级会员卡';
                $goodsWarehouse->cover_pic = $baseUrl . 'goods_pic.png';
                $goodsWarehouse->pic_url = $baseUrl . 'goods_pic.png';
                $goodsWarehouse->type = 'vip_card';
                $res = $goodsWarehouse->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsWarehouse));
                }

                $attr = $this->defaultAttr($baseUrl);
                $goods = new Goods();
                $goods->mall_id = 0;
                $goods->goods_warehouse_id = $goodsWarehouse->id;
                $goods->attr_groups = \Yii::$app->serializer->encode($attr['attr_groups']);
                $goods->freight_id = 0;
                $goods->individual_share = 1;
                $goods->sign = $pluginName;
                $res = $goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goods));
                }

                $goodsAttr = new GoodsAttr();
                $goodsAttr->goods_id = $goods->id;
                $goodsAttr->sign_id = $attr['sign_id'];
                $res = $goodsAttr->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsAttr));
                }

                $transaction->commit();
                \Yii::warning('超级会员卡商品添加成功');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error('超级会员卡商品异常----->' . $e->getMessage());
            // TODO 可不抛出异常
            throw $e;
        }
    }

    private function defaultAttr($baseUrl)
    {
        $attrList = [
            [

                'attr_group_name' => '规格',
                'attr_group_id' => 1,
                'attr_id' => 1,
                'attr_name' => '默认',
            ]
        ];

        $count = 1;
        $attrGroups = [];
        foreach ($attrList as &$item) {
            $item['attr_group_id'] = $count;
            $count++;
            $item['attr_id'] = $count;
            $count++;
            $newItem = [
                'attr_group_id' => $item['attr_group_id'],
                'attr_group_name' => $item['attr_group_name'],
                'attr_list' => [
                    [
                        'attr_id' => $item['attr_id'],
                        'attr_name' => $item['attr_name']
                    ]
                ]
            ];
            $attrGroups[] = $newItem;
        }
        unset($item);

        // 未使用规格 就添加一条默认规格
        $newAttrs = [
            [
                'attr_list' => $attrList,
                'stock' => 0,
                'price' => 0,
                'no' => '',
                'weight' => 0,
                'pic_url' => $baseUrl . 'goods_pic.png',
            ]
        ];

        $signIds = '';
        foreach ($attrList as $aLItem) {
            $signIds .= $signIds ? ':' . (int)$aLItem['attr_id'] : (int)$aLItem['attr_id'];
        }

        return [
            'attr_groups' => $attrGroups,
            'attrs' => $newAttrs,
            'sign_id' => $signIds,
        ];
    }
}
