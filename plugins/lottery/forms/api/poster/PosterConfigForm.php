<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\api\poster;


use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\Model;
use app\plugins\lottery\Plugin;

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
        $model = new PosterCustomize();
        $data = $model->traitMultiMapContent();

        $extra_multiMap = $this->formatType($data);
        return [
            'iconFreeUrl' => PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/free-p.png',
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getGoods(): array
    {
        $goods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->goods_id,
        ])->with(['attr', 'mallGoods'])->one();
        if (empty($goods)) {
            throw new \Exception('海报商品不存在');
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
            'price' => $goods->price,
            'multi_map' => $pic_list,
        ];
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}