<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/12
 * Time: 9:31
 */

namespace app\plugins\pick\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\pick\forms\common\CommonSetting;
use app\plugins\pick\models\PickSetting;

class SettingForm extends Model
{
    protected $mall;

    public $title;
    public $rule;
    public $bg_url;
    public $is_share;
    public $send_type;
    public $payment_type;
    public $is_territorial_limitation;
    public $goods_poster;
    public $form;

    public function rules()
    {
        return [
            [['title', 'rule'], 'trim'],
            [['title', 'rule', 'bg_url'], 'string'],
            [['is_share', 'is_territorial_limitation'], 'integer'],
            [['goods_poster', 'payment_type', 'send_type', 'form'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => '股东分红开关',
            'rule' => '分红比例',
            'is_share' => '分销开关',
            'send_type' => '发货方式',
            'payment_type' => '支付方式',
            'is_territorial_limitation' => '区域限制购买开关',
            'form' => '自定义'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $setList = [];
            foreach ($this->attributes as $index => $item) {
                if ($index == 'goods_poster') {
                    $item = (new CommonOptionP())->saveEnd($this->goods_poster);
                }

                $setList[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            PickSetting::setList(\Yii::$app->mall->id, $setList);
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
        $list = (new CommonSetting())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }
}
