<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\common;

use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\step\models\StepAd;
use app\plugins\step\models\StepSetting;
use app\plugins\step\models\StepUser;

class CommonStep extends Model
{
    public static function getUser($step_id = null, $mall_id = null)
    {
        if (!$mall_id) {
            $mall_id = \Yii::$app->mall->id;
        }
        if ($step_id) {
            $stepUser = StepUser::findOne([
                'id' => $step_id,
                'mall_id' => $mall_id,
                'is_delete' => 0
            ]);
        } else {
            $stepUser = StepUser::findOne([
                'mall_id' => $mall_id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0
            ]);
        }
        return $stepUser;
    }

    public static function getAd($site)
    {
        $stepAd = StepAd::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
            'site' => $site
        ]);
        return $stepAd;
    }

    public static function getSetting($mall_id = null)
    {
        if (!isset($mall_id)) {
            $mall_id = \Yii::$app->mall->id;
        }
        $setting = StepSetting::findOne(['mall_id' => $mall_id]);

        if (!isset(\Yii::$app->request->hostInfo)) {
            $poster_default = ['id' => -1, 'url' => '', 'pic_url' => ''];
        } else {
            $poster_default = ['id' => -1, 'url' => PluginHelper::getPluginBaseAssetsUrl('step') . '/img/poster-default.png', 'pic_url' => PluginHelper::getPluginBaseAssetsUrl('step') . '/img/poster-default.png'];
        }

        $default = [
            'convert_max' => 0,
            'convert_ratio' => 0,
            'currency_name' => '活力币',
            'activity_pic' => '',
            'ranking_pic' => '',
            'qrcode_pic' => [$poster_default],
            'invite_ratio' => 0,
            'remind_at' => '00:00',
            'rule' => '',
            'activity_rule' => '',
            'ranking_num' => '0',
            'title' => '步数宝',
            'share_title' => '',
            'qrcode_title' => '',
            'payment_type' => ['online_pay'],
            'send_type' => ['express', 'offline'],
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'goods_poster' => CommonOption::getPosterDefault(),
            'step_poster' => CommonOption::getStepPosterDefault(),
        ];

        if ($setting) {
            $poster_pic = \yii\helpers\Json::decode(trim($setting['qrcode_pic'], '\'\"')) ?: $default['qrcode_pic'];
            $setting['qrcode_pic'] = count($poster_pic) === 0 ? $default['qrcode_pic'] : $poster_pic;
            $setting['payment_type'] = \yii\helpers\Json::decode($setting['payment_type']) ?: $default['payment_type'];
            $setting['goods_poster'] = \yii\helpers\Json::decode($setting['goods_poster']) ?: $default['goods_poster'];
            $setting['step_poster'] = \yii\helpers\Json::decode($setting['step_poster']) ?: $default['step_poster'];
            $setting['title'] = $setting['title'] ?: $default['title'];

            $setting['currency_name'] = $setting['currency_name'] ?: $default['currency_name'];
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
        } else {
            $setting = $default;
        }
        return $setting;
    }
}
