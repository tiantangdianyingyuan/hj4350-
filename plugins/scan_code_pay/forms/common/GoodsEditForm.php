<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\common;


use app\forms\mall\goods\BaseGoodsEdit;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\scan_code_pay\Plugin;

class GoodsEditForm extends BaseGoodsEdit
{
    public $is_goods_edit = 0;
    public $goods;
    public $is_share;

    public function rules()
    {
        return [];
    }

    protected function setGoodsSign()
    {
        // TODO: Implement setGoodsSign() method.
    }

    public function save()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $pluginName = (new Plugin())->getName();
            $baseUrl = PluginHelper::getPluginBaseAssetsUrl($pluginName) . '/img/';

            try {
                $shareData = \Yii::$app->serializer->decode($this->goods);
            } catch (\Exception $exception) {
                $shareData = [];
            }

            $goods = Goods::find()->where(['sign' => $pluginName, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->with('shareLevel')->one();
            if (!$goods) {
                $this->is_goods_edit = 1;
                $goodsWarehouse = new GoodsWarehouse();
                $goodsWarehouse->mall_id = \Yii::$app->mall->id;
                $goodsWarehouse->name = '当面付';
                $goodsWarehouse->detail = '当面付';
                $goodsWarehouse->cover_pic = $baseUrl . 'goods_pic.png';
                $goodsWarehouse->pic_url = $baseUrl . 'goods_pic.png';
                $res = $goodsWarehouse->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsWarehouse));
                }

                $attr = $this->defaultAttr($baseUrl);
                $goods = new Goods();
                $goods->mall_id = \Yii::$app->mall->id;
                $goods->goods_warehouse_id = $goodsWarehouse->id;
                $goods->attr_groups = \Yii::$app->serializer->encode($attr['attr_groups']);
                $goods->freight_id = 0;
                $goods->individual_share = 1;
                $goods->use_attr = 0;
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
            }

            if ($this->is_goods_edit == 1) {
                $goods->share_type = isset($shareData['share_type']) ? $shareData['share_type'] : 0;
                $res = $goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goods));
                }
                $shareLevelList = isset($shareData['shareLevelList']) ? $shareData['shareLevelList'] : [];
                $this->isNewRecord = false;
                $this->goods = $goods;
                $this->individual_share = $this->is_share;
                $this->setGoodsShare(0, $shareLevelList);

                $transaction->commit();
                \Yii::warning('当面付商品添加/编辑成功');
            }

            return $goods;

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error('当面付商品异常----->' . $e->getMessage());
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