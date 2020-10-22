<?php

namespace app\plugins\check_in\forms\mall;

use app\core\response\ApiCode;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;

class CustomizeForm extends Model
{
    public $remind_font;
    public $daily_font;
    public $prompt_font;
    public $btn_bg;
    public $not_prompt_font;
    public $not_btn_bg;
    public $line_font;
    public $end_bg;
    public $end_style;
    public $not_signed_icon;
    public $signed_icon;
    public $head_bg;
    public $balance_icon;
    public $integral_icon;
    public $calendar_icon;
    public $end_gradient_bg;


    public function rules()
    {
        return [
            [['end_gradient_bg','remind_font', 'daily_font', 'prompt_font', 'btn_bg', 'not_prompt_font', 'not_btn_bg', 'line_font', 'end_bg', 'not_signed_icon', 'signed_icon', 'head_bg', 'balance_icon', 'integral_icon', 'calendar_icon'], 'string'],
            [['end_gradient_bg','remind_font', 'daily_font', 'prompt_font', 'btn_bg', 'not_prompt_font', 'not_btn_bg', 'line_font', 'end_bg', 'not_signed_icon', 'signed_icon', 'head_bg', 'balance_icon', 'integral_icon', 'calendar_icon'], 'default', 'value' => ''],
            [['end_style'], 'integer'],
            [['end_style'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'remind_font' => '签到提醒字体颜色',
            'daily_font' => '今日签到字体颜色',
            'prompt_font' => '已领取字体颜色',
            'btn_bg' => '已领取按钮颜色',
            'not_prompt_font' => '未领取字体颜色',
            'not_btn_bg' => '未领取按钮颜色',
            'line_font' => '分割线颜色',
            'end_bg' => '下半部背景颜色',
            'end_style' => '下半部颜色配置',

            'not_signed_icon' => '未签到图标',
            'signed_icon' => '已签到图标',
            'head_bg' => '头部背景图',
            'balance_icon' => '红包图标',
            'integral_icon' => '积分图标',
            'calendar_icon' => '日历签到图标',
            'end_gradient_bg' => '渐变颜色',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $config = Common::getCommon(\Yii::$app->mall)->getCustomize();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => $config
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $config = Common::getCommon(\Yii::$app->mall)->setCustomize($this->attributes);
        if ($config) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($config);
        }
    }
}
