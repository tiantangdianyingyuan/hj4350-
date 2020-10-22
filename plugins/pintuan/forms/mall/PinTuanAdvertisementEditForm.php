<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pintuan\forms\common\v2\SettingForm;
use app\plugins\pintuan\models\PintuanSetting;

class PinTuanAdvertisementEditForm extends Model
{
    public $advertisement;
    public $is_advertisement;

    public function rules()
    {
        return [
            [['advertisement'], 'safe'],
            [['is_advertisement'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!count($this->advertisement)) {
                throw new \Exception('请选择板块样式');
            }

            $setting = PintuanSetting::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->one();


            if (!$setting) {
                $default = (new SettingForm())->getDefault();
                $setting = new PintuanSetting();
                $setting->mall_id = \Yii::$app->mall->id;
                $setting->rules = \Yii::$app->serializer->encode($default['rules']);
                $setting->payment_type = \Yii::$app->serializer->encode($default['payment_type']);
                $setting->goods_poster = \Yii::$app->serializer->encode($default['goods_poster']);
                $setting->send_type = \Yii::$app->serializer->encode($default['send_type']);
            }

            $setting->advertisement = \Yii::$app->serializer->encode($this->advertisement);
            $setting->is_advertisement = $this->is_advertisement;
            $res = $setting->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($setting));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
