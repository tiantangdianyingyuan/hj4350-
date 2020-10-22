<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\stock\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\plugins\stock\models\StockSetting;

class SettingForm extends Model
{
    public $is_stock;
    public $stock_rate;
    public $base_rate;
    public $apply_type;
    public $become_type;
    public $condition;
    public $is_agreement;
    public $agreement_title;
    public $agreement_content;
    public $user_instructions;
    public $pay_type;
    public $min_money;
    public $cash_service_charge;
    public $free_cash_min;
    public $free_cash_max;
    public $form;


    public function rules()
    {
        return [
            [['is_stock', 'stock_rate', 'base_rate', 'apply_type', 'become_type', 'condition', 'is_agreement', 'pay_type',
                'min_money', 'cash_service_charge'], 'required'],
            [['is_stock', 'is_agreement',], 'integer'],
            [['is_stock', 'stock_rate', 'base_rate'], 'default', 'value' => 1],
            [['stock_rate', 'base_rate', 'become_type', 'condition', 'min_money', 'cash_service_charge', 'free_cash_min', 'free_cash_max',], 'number', 'min' => 0],
            [['stock_rate', 'apply_type', 'cash_service_charge'], 'number', 'max' => 100],
            [['stock_rate', 'base_rate'], 'default', 'value' => 0],
            [['agreement_title', 'agreement_content', 'user_instructions'], 'string'],
            [['agreement_title', 'agreement_content', 'user_instructions', 'form'], 'trim'],
            ['free_cash_min', 'compare', 'compareAttribute' => 'free_cash_max', 'operator' => '<', 'message' => '起始金额必须小于结束金额'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_stock' => '股东分红开关',
            'stock_rate' => '分红比例',
            'base_rate' => '基础分红比例',
            'apply_type' => '申请股东方式',
            'become_type' => '成为股东条件',
            'condition' => '条件',
            'is_agreement' => '申请协议',
            'agreement_title' => '协议名称',
            'agreement_content' => '协议内容',
            'user_instructions' => '协议内容',
            'pay_type' => '提现方式',
            'min_money' => '提现门槛金额',
            'cash_service_charge' => '分红提现手续费',
            'free_cash_min' => '起始金额',
            'free_cash_max' => '结束金额',
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

        try {
            $setList = [];
            foreach ($this->attributes as $index => $item) {
                $setList[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            StockSetting::setList(\Yii::$app->mall->id, $setList);
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
        $list = StockSetting::getList(\Yii::$app->mall->id);
        if (isset($list['free_cash_min']) && isset($list['free_cash_max'])) {
            if ($list['free_cash_min'] == 0 && $list['free_cash_max'] == 0) {
                $list['free_cash_min'] = '';
                $list['free_cash_max'] = '';
            }
        }

        try {
            $list['template_message_stock'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                'audit_result_tpl',
                'remove_identity_tpl',
            ]);
            $list['template_message_withdraw'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                'withdraw_success_tpl',
                'withdraw_error_tpl'
            ]);
        } catch (\Exception $exception) {
            $list['audit_result_tpl'] = [];
            $list['remove_identity_tpl'] = [];
            $list['template_message_stock'] = [];
            $list['template_message_withdraw'] = [];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }
}
