<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\mch;


use app\forms\common\version\Compatible;
use app\models\Mall;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchSetting;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'], 'integer']
        ];
    }

    public function search()
    {
        $mchId = $this->mch_id ?: \Yii::$app->user->identity->mch_id;
        $setting = MchSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchId,
        ])->one();

        $mch = Mch::findOne($mchId);

        if (!$setting && $mch) {
            $setting = $this->getDefault();
        }
        $setting = ArrayHelper::toArray($setting);
        $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
        if (($key = array_search('city', $setting['send_type'])) !== false) {
            unset($setting['send_type'][$key]);
        }
        return $setting ?: [];
    }

    public function getDefault()
    {
        return [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'send_type' => ['express'],
            'is_web_service' => 0,
            'web_service_url' => '',
            'web_service_pic' => ''
        ];
    }
}
