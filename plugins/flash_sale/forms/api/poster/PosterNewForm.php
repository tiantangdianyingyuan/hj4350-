<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\flash_sale\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\models\Model;
use app\plugins\flash_sale\models\FlashSaleGoods;

class PosterNewForm extends Model implements BasePoster
{
    public $style;
    public $typesetting;
    public $type;
    public $goods_id;
    public $color;

    public function rules()
    {
        return [
            [['style', 'typesetting', 'goods_id'], 'required'],
            [['style', 'typesetting', 'type'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $class = $this->getClass($this->style);

        $flashSale = FlashSaleGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->one();

        if (empty($flashSale->goods)) {
            throw new \Exception('海报-商品不存在');
        }

        $class->typesetting = $this->typesetting;
        $class->type = $this->type;
        $class->color = $this->color;
        $class->goods = $flashSale->goods;
        $prices = array_column($flashSale->goods->attr, 'price');
        //最低价
        $minPrice = min($prices);
        $goods = $flashSale->goods;
        $type = $goods->attr[0]->attr->type ?? 1;
        if ($type == 1) {
            $discount = (1 - $goods->attr[0]->attr->discount / 10) * $minPrice;
        } else {
            $discount = $goods->attr[0]->attr->cut;
        }
        $minPrice -= min($discount, $minPrice);
        $minPrice = price_format($minPrice);
        if (empty($prices)) {
            throw new \Exception('海报-规格数据异常');
        }
        $class->other = [
            'start_time' => $flashSale->activity->start_at,
            'min_price' => $minPrice,
        ];
        $class->extraModel = PosterCustomize::className();
        return $class->build();
    }


    /**
     * @param int $key
     * @return StyleGrafika
     * @throws \Exception
     */
    private function getClass(int $key): StyleGrafika
    {
        $map = [
            1 => 'app\forms\api\poster\style\StyleOne',
            2 => 'app\forms\api\poster\style\StyleTwo',
            3 => 'app\forms\api\poster\style\StyleThree',
            4 => 'app\forms\api\poster\style\StyleFour',
        ];
        if (isset($map[$key]) && class_exists($map[$key])) {
            return new $map[$key];
        }
        throw new \Exception('调用错误');
    }
}
