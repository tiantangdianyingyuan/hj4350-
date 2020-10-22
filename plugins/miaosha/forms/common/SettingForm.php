<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\common;


use app\forms\common\version\Compatible;
use app\models\Mall;
use app\models\Model;
use app\plugins\miaosha\models\MiaoshaSetting;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public $setting;

    public function search()
    {
        if ($this->setting) {
            return $this->setting;
        }
        /** @var MiaoshaSetting $setting */
        $setting = MiaoshaSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();

        if (!$setting) {
            $setting = $this->getDefault();
        } else {
            $setting = ArrayHelper::toArray($setting);
            $default = $this->getDefault();

            $setting['open_time'] = $setting['open_time'] ?
                \yii\helpers\Json::decode($setting['open_time']) : $default['open_time'];
            $setting['payment_type'] = $setting['payment_type'] ?
                \yii\helpers\Json::decode($setting['payment_type']) :
                $default['payment_type'];
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
            $setting['goods_poster'] = $setting['goods_poster'] ?
                \yii\helpers\Json::decode($setting['goods_poster']) :
                CommonOption::getPosterDefault();
        }
        $this->setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        return [
            'over_time' => 10,
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'open_time' => [],
            'payment_type' => ['online_pay'],
            'send_type' => ['express', 'offline'],
            'goods_poster' => CommonOption::getPosterDefault()
        ];
    }
}
