<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\bargain\forms\api\poster;


use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\Plugin;

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
        $bargain = BargainGoods::find()->where([
            'goods_id' => $this->goods_id,
        ])->one();

        $model = new PosterCustomize();
        $data = $model->traitMultiMapContent((object)['other' => ['end_time' => $bargain->end_time]]);

        $extra_multiMap = $this->formatType($data);
        return [
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getGoods(): array
    {
        $bargain = BargainGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods_id,
        ])->with(['goods'])->one();
        if (empty($bargain) || empty($goods = $bargain->goods)) {
            throw new \Exception('海报商品不存在');
        }

        $prices = array_column($goods->attr, 'price');
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
            'min_price' => min($prices),
            'max_price' => max($prices),
            'multi_map' => $pic_list,
            'bargain_min_price' => $bargain->min_price,
        ];
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}