<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\CommonStepGoods;

class StepGoodsForm extends Model
{
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['page', 'id'], 'integer']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\step\models\Goods';
        $form->status = 1;
        $form->page = $this->page;
        $form->limit = 6;
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse', 'stepGoods', 'attr.stepGoods'];
        $list = $form->search();

        $commonEcard = CommonEcard::getCommon();
        $newList = [];
        foreach ($list as $goods) {
            $price = [];
            $currency = [];
            $stock = 0;
            foreach ($goods['attr'] as $item) {
                array_push($price, (float)$item['price']);
                array_push($currency, $item['stepGoods']['currency']);
                $stock += $commonEcard->getEcardStock($item['stock'], $goods);
            }
            $newList[] = [
                'name' => $goods['goodsWarehouse']['name'],
                'subtitle' => $goods['goodsWarehouse']['subtitle'],
                'attr' => $goods['attr'],
                'video_url' => Video::getUrl($goods['goodsWarehouse']['video_url']),
                'cover_pic' => $goods['goodsWarehouse']['cover_pic'],
                'goods_id' => $goods['id'],
                'price' => $goods['price'],
                'currency' => $goods['stepGoods']['currency'],
                'min_price' => min($price),
                'max_price' => max($price),
                'min_currency' => min($currency),
                'max_currency' => max($currency),
                'original_price' => $goods['goodsWarehouse']['original_price'],
                'count_stock' => $stock,
                'goods_stock' => $stock
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = CommonStepGoods::getGoods($this->id);
            if (!$form) {
                throw new \Exception('商品不存在或已删除');
            }

            $commonGoods = CommonGoodsDetail::getCommonGoodsDetail(\Yii::$app->mall);

            //todo 过公共方法，用于记录足迹，需优化
            $commonGoods->getGoods($this->id);

            $commonGoods->user = \Yii::$app->user->identity;
            $commonGoods->goods = $form->goods;
            $detail = CommonStepGoods::getDetail($commonGoods->getAll());
            $setting = (new CommonStep())->getSetting();

            $detail['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $detail['goods_marketing']['limit'] : '';
            $newList = [
                'attr_group' => $detail['attr_groups'],
                'attr_groups' => $detail['attr_groups'],
                'attr' => $detail['attr'],
                'share' => $setting['is_share'] == 1 ? $detail['share'] : 0,
                'cover_pic' => $detail['cover_pic'],
                'name' => $detail['name'],
                'subtitle' => $detail['subtitle'],
                'pic_url' => $detail['pic_url'],
                'video_url' => Video::getUrl($detail['video_url']),
                'unit' => $detail['unit'],
                'detail' => $detail['detail'],
                'min_price' => (float)$detail['min_price'],
                'max_price' => (float)$detail['max_price'],
                'min_currency' => $detail['min_currency'],
                'max_currency' => $detail['max_currency'],
                'app_share_pic' => $detail['app_share_pic'],
                'app_share_title' => $detail['app_share_title'],

                'price' => $detail['price'],
                'goods_id' => $form->goods_id,
                'goods_marketing' => $detail['goods_marketing'],
                'goods_marketing_award' => $detail['goods_marketing_award'],
                'services' => $detail['services'],
                'express' => $detail['express'],
                'goods_stock' => array_sum(array_column($detail['attr'], 'stock')),
                'type' => $detail['type'],
                'guarantee_title' => $detail['guarantee_title'],
                'guarantee_pic' => $detail['guarantee_pic'],
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                    'detail' => $newList,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
