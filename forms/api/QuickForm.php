<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\api\cart\CartForm;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Cart;
use app\models\Model;
use app\models\QuickShopCats;

class QuickForm extends Model
{
    public $cat_id;
    public $is_sell_well;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['limit', 'page', 'is_sell_well', 'cat_id'], 'integer',],
            [['limit',], 'default', 'value' => 10],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = QuickShopCats::find()->alias('b')->where([
                'b.is_delete' => 0,
                'b.mall_id' => \Yii::$app->mall->id,
            ])->joinWith(['cats c' => function ($query) {
                $query->where([
                    'c.mall_id' => \Yii::$app->mall->id,
                    'c.is_delete' => 0
                ]);
            }]);

            $list = $query->orderBy('sort ASC, id DESC')->asArray()->all();
            $cats_list = array_map(function ($item) {
                return $item['cats'];
            }, $list);

            list($count) = $this->getGoodList();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'count' => $count,
                    'cats_list' => $cats_list,
                    'hot_list' => [],
                ]
            ];
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function getGoodsList()
    {
        $form = new CommonGoodsList();
        $form->relations = ['attr', 'cart', 'goodsWarehouse'];
        $form->page = $this->page;
        $form->status = 1;
        $form->is_negotiable = 0;
        $form->is_quick_shop = 1;
        $form->is_cart = 1;
        $form->cat_id = $this->cat_id;
        if ($this->is_sell_well == 1) {
            $form->is_sell_well = $this->is_sell_well;
        }
        $form->is_del_ecard = true;
        return $form->search();
    }

    public function goodsList()
    {
        try {
            $list = $this->getGoodsList();
            list($count, $list) = $this->getGoodList($list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'count' => $count,
                    'list' => $list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage()
            ];
        }
    }

    private function getGoodList($list = [])
    {
        $cartForm = (new CartForm)->search();
        $count = 0;
        if ($cartForm['code'] === 0) {
            $arr = [];
            foreach ($cartForm['data']['list'] as $v) {
                foreach ($v['goods_list'] as $v1) {
                    if ($v1['new_status'] === 0) {
                        array_push($arr, $v1['num']);
                    }
                }
            }
            $count = array_sum($arr);
        }

        $new_list = [];
        foreach ($list as $k => $item) {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $form->goods = $item;
            $mallGoods = CommonGoods::getCommon()->getMallGoods($item->id);
            $form->setMember($mallGoods->is_negotiable == 0);
            $form->setShare($mallGoods->is_negotiable == 0);
            $dataAll = $form->getAll(['attr', 'vip_card_appoint']);
            $new_list[$k] = [
                'id' => $item['id'],
                'cover_pic' => $item['goodsWarehouse']['cover_pic'],
                'video_url' => $item['goodsWarehouse']['video_url'],
                'name' => $item['goodsWarehouse']['name'],
                'virtual_sales' => $item['sales'] ? floatval($item['virtual_sales']) + floatval($item['sales']) : floatval($item['virtual_sales']),
                'price' => $item['price'],
                'use_attr' => $item['use_attr'],
                'cart' => $item['cart'],
                'attr' => $dataAll['attr'],
                'goods_num' => array_sum(array_column($dataAll['attr'], 'stock')),
                'total_num' =>  array_sum(array_column($item['cart'], 'num')),
                'attr_groups' => \Yii::$app->serializer->decode($item['attr_groups']),
                'is_level' => $item['is_level'],
                'level_show' => $dataAll['level_show'],
                'level_price' => CommonGoodsMember::getCommon()->getGoodsMemberPrice($item),
                'vip_card_appoint' => $dataAll['vip_card_appoint'] ?? [],
                'type' => $item['goodsWarehouse']['type'],
            ];
            unset($item);
        }
        return [$count, $new_list];
    }
}
