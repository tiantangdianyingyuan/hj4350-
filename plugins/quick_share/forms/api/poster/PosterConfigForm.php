<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\quick_share\forms\common\CommonGoods;
use app\plugins\quick_share\models\Goods;

class PosterConfigForm extends Model
{
    use PosterConfigTrait {
        PosterConfigTrait::getGoods as traitGoods;
    }
    public $id;
    public $goods_id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'goods_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
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
    protected function getGoods(): array
    {
        if ($this->id) {
            $share_goods = CommonGoods::getGoods($this->id);
            $mallGoods = $share_goods->goods;
        }

        if ($this->goods_id) {
            $mallGoods = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->goods_id,
            ])->with(['attr'])->one();
            $share_goods = $mallGoods->quickShareGoods;
        }

        if (empty($mallGoods)) {
            throw new \Exception('海报商品不存在');
        }

        $prices = array_column($mallGoods->attr, 'price');
        if (empty($prices)) {
            throw new \Exception('海报-规格数据异常');
        }

        $picUrl = isset($share_goods) ? $share_goods->share_pic : $mallGoods->picUrl;
        $pic_list = array_column(\yii\helpers\BaseJson::decode($picUrl), 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }

        return [
            'goods_name' => $mallGoods->name ?? '',
            'multi_map' => $pic_list,
            'is_negotiable' => $mallGoods->mallGoods->is_negotiable ?? 0,
            'min_price' => min($prices),
            'max_price' => max($prices),
        ];
    }

}