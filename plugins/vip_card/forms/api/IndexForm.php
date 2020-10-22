<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\vip_card\forms\api;

use app\models\Goods;
use app\models\Model;
use app\plugins\vip_card\forms\common\GoodsEditForm;
use yii\helpers\ArrayHelper;

class IndexForm extends Model
{
    public function getGoods()
    {
        $goods = $this->goods();
        if (!$goods) {
            $form = new GoodsEditForm();
            $res = $form->saveGoods();
            $goods = $this->goods();
        } else {
            $goods->goodsWarehouse->type = 'vip_card';
            $goods->goodsWarehouse->save();
        }

        $newGoods = ArrayHelper::toArray($goods);
        $newGoods['attr_groups'] = \Yii::$app->serializer->decode($goods->attr_groups);
        $newGoods['attr'] = ArrayHelper::toArray($goods->attr);
        return $newGoods;
    }

    public function goods()
    {
        /** @var Goods $goods */
        $goods = Goods::find()->with(['attr'])
            ->where([
                'is_delete' => 0,
                'mall_id' => 0,
                'sign' => 'vip_card'
            ])->one();

        return $goods;
    }
}
