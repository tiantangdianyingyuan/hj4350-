<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api\poster;


use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepGoods;
use app\plugins\step\Plugin;

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
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getGoods(): array
    {
        $stepGoods = StepGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods_id,
        ])->one();

        $picUrl = \yii\helpers\BaseJson::decode($stepGoods->goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }


        $setting = CommonStep::getSetting();
        return [
            'goods_name' => $stepGoods->goods->name,
            'price' => $stepGoods->goods->price,
            'multi_map' => $pic_list,
            'unit' => $setting['currency_name'],
            'currency' => $stepGoods->currency,
        ];
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}