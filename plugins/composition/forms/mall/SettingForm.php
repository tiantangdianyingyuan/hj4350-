<?php


namespace app\plugins\composition\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\plugins\composition\forms\common\CommonSetting;

class SettingForm extends Model
{
    public $is_coupon;
    public $title;
    public $rule;
    public $activityBg;
    public $is_share;
    public $is_territorial_limitation;
    public $payment_type;
    public $send_type;
    public $is_full_reduce;

    public function rules()
    {
        return [
            [['is_coupon', 'activityBg'], 'required'],
            [['title', 'rule', 'activityBg'], 'trim'],
            [['title', 'rule', 'activityBg'], 'string'],
            [['is_share', 'is_coupon', 'is_territorial_limitation', 'is_full_reduce'], 'integer'],
            [['payment_type', 'send_type'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $common = CommonSetting::getCommon();
        $list = $common->checkDefault($this->attributes);
        $boolean = CommonOption::set('composition_setting', $list, \Yii::$app->mall->id, 'plugin');
        if ($boolean) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }
    }

    public function getSetting()
    {
        $common = CommonSetting::getCommon();
        $setting = $common->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }
}
