<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\flash_sale\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\Plugin;

class PosterConfigForm extends Model
{
    use PosterConfigTrait;

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'config' => $this->getConfig(),
                    'info' => $this->getAll(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getExtra(): array
    {
        $flashSale = FlashSaleGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->with('activity')->one();

        $model = new PosterCustomize();
        $data = $model->traitMultiMapContent((object)['other' => ['start_time' => $flashSale->activity->start_at]]);

        $extra_multiMap = $this->formatType($data);
        return [
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getGoods(): array
    {
        $flashSaleGoods = FlashSaleGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods_id,
        ])->with(['goods'])->one();
        if (empty($flashSaleGoods) || empty($goods = $flashSaleGoods->goods)) {
            throw new \Exception('海报商品不存在');
        }

        $prices = array_column($goods->attr, 'price');
        //最低价
        $minPrice = min($prices);
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

        $picUrl = \yii\helpers\BaseJson::decode($goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }

        return [
            'goods_name' => $goods->name,
            'is_negotiable' => $goods->mallGoods->is_negotiable ?? 0,
            'min_price' => $minPrice,
            'max_price' => max($prices),
            'multi_map' => $pic_list,
            'flash_sale_min_price' => $minPrice,
        ];
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}
