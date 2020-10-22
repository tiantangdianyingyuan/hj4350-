<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\pick\models\PickCart;
use yii\helpers\ArrayHelper;

class CartForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['limit'], 'integer'],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        while (PickCart::cacheStatusGet()) {
            // 购物车的编辑、删除等操作完成之后，才可以获取购物车列表
            usleep(500);
        }

        $list = PickCart::find()->alias('c')->where([
            'c.mall_id' => \Yii::$app->mall->id,
            'c.is_delete' => 0,
            'c.user_id' => \Yii::$app->user->id,
        ])
            ->with(['goods.goodsWarehouse'])
            ->with(['attrs'])->orderBy(['c.created_at' => SORT_DESC])->all();

        $newList = [];
        /** @var PickCart[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goods'] = ArrayHelper::toArray($item->goods);
            $newItem['attrs'] = $item->attrs ? ArrayHelper::toArray($item->attrs) : $item->attrs;
            $newItem['reduce_price'] = 0;
            if ($item->attrs) {
                // 还存在的商品
                $newItem['attrs']['attr'] = (new Goods())->signToAttr($item->attrs->sign_id, $item->goods->attr_groups);
                $newItem['attr_str'] = 0;
                if ($item->attr_info) {
                    try {
                        $attrInfo = \Yii::$app->serializer->decode($item->attr_info);
                        $reducePrice = $attrInfo['price'] - $item->attrs->price;
                        if ($attrInfo['price'] - $item->attrs->price) {
                            $newItem['reduce_price'] = price_format($reducePrice);
                        }
                    } catch (\Exception $exception) {
                    }
                }
            } else {
                $newItem['attr_str'] = 1;
            }
            $newItem['goods']['name'] = $item->goods->name;
            $newItem['goods']['cover_pic'] = $item->goods->coverPic;

            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ],
        ];
    }
}
