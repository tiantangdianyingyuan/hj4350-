<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\goods;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\GoodsBase;
use app\plugins\exchange\forms\common\CommonModel;

class CardGoodsForm extends GoodsBase
{
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonGoods::getCommon();
            $detail = $common->getGoodsDetail($this->id, false);
            $cardGoods = CommonModel::getCardGoods($this->id);
            if (!$cardGoods) {
                throw new \Exception('数据异常，插件商品不存在');
            }
            $detail = array_merge($detail, ['status' => intval($detail['status'])], $this->getDistrictPrice($detail['attr']));
            $detail['plugin_data']['library'] = [
                'library_name' => $cardGoods->library->name,
                'library_id' => (string)$cardGoods->library_id,
            ];
            if (!$detail) {
                throw new \Exception('请求失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function getDistrictPrice($attr)
    {
        $minPrice = 0;
        $maxPrice = 0;
        if ($attr && is_array($attr)) {
            foreach ($attr as $key => $value) {
                $minPrice = $minPrice == 0 ? $value['price'] : $minPrice;
                $maxPrice = $maxPrice == 0 ? $value['price'] : $maxPrice;
                if ($value['price'] < $minPrice) {
                    $minPrice = $value['price'];
                }

                if ($value['price'] > $maxPrice) {
                    $maxPrice = $value['price'];
                }
            }
        }

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];
    }
}
