<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\plugins\region\models\RegionSetting;

class SettingForm extends Model
{
    public $is_region;
    public $region_rate;
    public $apply_type;
    public $is_agreement;
    public $agreement_title;
    public $agreement_content;
    public $user_instructions;
    public $level_up_content;
    public $pay_type;
    public $min_money;
    public $cash_service_charge;
    public $free_cash_min;
    public $form;


    public function rules()
    {
        return [
            [
                [
                    'is_region',
                    'region_rate',
                    'apply_type',
                    'is_agreement',
                    'pay_type',
                    'min_money',
                    'cash_service_charge'
                ],
                'required'
            ],
            [['is_region', 'is_agreement',], 'integer'],
            [['is_region', 'region_rate',], 'default', 'value' => 1],
            [['region_rate', 'min_money', 'cash_service_charge', 'free_cash_min',], 'number', 'min' => 0],
            [['region_rate', 'apply_type', 'cash_service_charge'], 'number', 'max' => 100],
            [['region_rate',], 'default', 'value' => 0],
            [['agreement_title', 'agreement_content', 'user_instructions', 'level_up_content'], 'string'],
            [['agreement_title', 'agreement_content', 'user_instructions', 'form'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_region' => '区域代理开关',
            'region_rate' => '分红比例',
            'apply_type' => '申请区域方式',
            'is_agreement' => '申请协议',
            'agreement_title' => '协议名称',
            'agreement_content' => '协议内容',
            'user_instructions' => '协议内容',
            'level_up_content' => '升级说明',
            'pay_type' => '提现方式',
            'min_money' => '提现门槛金额',
            'cash_service_charge' => '分红提现手续费',
            'free_cash_min' => '免手续费门槛金额',
            'form' => '自定义参数',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_agreement == 1) {
            if (empty($this->agreement_title)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '协议名称不能为空'
                ];
            }
            if (empty($this->agreement_content)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '协议内容不能为空'
                ];
            }
        }

        if (empty($this->form['bg_url'])) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '头部banner图片不能为空'
            ];
        }

        try {
            $setList = [];
            foreach ($this->attributes as $index => $item) {
                $setList[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            RegionSetting::setList(\Yii::$app->mall->id, $setList);
            return [
                'code' => 0,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        $list = RegionSetting::getList(\Yii::$app->mall->id);
        if (isset($list['free_cash_min'])) {
            if ($list['free_cash_min'] == 0) {
                $list['free_cash_min'] = '';
            }
        }

        try {
            $list['template_message_region'] = TemplateList::getInstance()->getTemplate(
                \Yii::$app->appPlatform,
                [
                    'audit_result_tpl',
                    'remove_identity_tpl',
                ]
            );
            $list['template_message_withdraw'] = TemplateList::getInstance()->getTemplate(
                \Yii::$app->appPlatform,
                [
                    'withdraw_success_tpl',
                    'withdraw_error_tpl'
                ]
            );
        } catch (\Exception $exception) {
            $list['audit_result_tpl'] = [];
            $list['remove_identity_tpl'] = [];
            $list['template_message_region'] = [];
            $list['template_message_withdraw'] = [];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }
}
