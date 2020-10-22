<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\recharge;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class RechargeSettingForm extends Model
{
    public $status;
    public $type;
    public $bj_pic_url;
    public $ad_pic_url;
    public $page_url;
    public $re_pic_url;
    public $explain;
    public $open_type;
    public $params;
    public $re_name;


    public function rules()
    {
        return [
            [['status', 'type'], 'required'],
            [['status', 'type'], 'integer'],
            [['status', 'type'], 'default', 'value' => 0],
            [['bj_pic_url', 'ad_pic_url', 'page_url', 'explain', 're_pic_url', 'open_type', 'params', 're_name'], 'default', 'value' => ''],
        ];
    }


    public function attributeLabels()
    {
        return [
            'status' => '开启余额',
            'type' => '自定义金额',
            'bj_pic_url' => '背景图片',
            'ad_pic_url' => '广告图片',
            'page_url' => '跳转路径',
            're_pic_url' => '充值图标',
            'explain' => '说明',
            'open_type' => '',
            'params' => '',
            're_name' => '充值按钮文字',
        ];
    }


    public function get()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $this->setting(),
        ];
    }

    public function setting()
    {
        $setting = CommonOption::get(Option::NAME_RECHARGE_SETTING, \Yii::$app->mall->id, Option::GROUP_APP, $this->getDefault());
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        return array_merge($this->getDefault(), $setting);
    }

    public function getDefault()
    {
        return [
            'status' => '0',
            'type' => '0',
            'bj_pic_url' => '',
            'ad_pic_url' => '',
            'page_url' => '',
            're_pic_url' => '',
            'explain' => '',
            'open_type' => '',
            'params' => '',
            're_name' => '充值'
        ];
    }


    public function set()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $data = [
            'status' => $this->status,
            'type' => $this->type,
            'bj_pic_url' => $this->bj_pic_url,
            'ad_pic_url' => $this->ad_pic_url,
            'page_url' => $this->page_url,
            're_pic_url' => $this->re_pic_url,
            'explain' => $this->explain,
            'open_type' => $this->open_type,
            'params' => $this->params,
            're_name' => $this->re_name,
        ];

        $option = CommonOption::set(Option::NAME_RECHARGE_SETTING, $data, \Yii::$app->mall->id, Option::GROUP_APP);
        if ($option) {
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
}
