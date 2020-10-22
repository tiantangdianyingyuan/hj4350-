<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 17:26
 */

namespace app\forms\mall\offer_price;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class EditForm extends Model
{
    public $is_enable;
    public $total_price;
    public $detail;
    public $is_total_price;

    public function rules()
    {
        return [
            [['is_enable', 'is_total_price'], 'integer'],
            ['total_price', 'number', 'min' => 0],
            ['detail', function ($attr, $param) {
                foreach ($this->$attr as $item) {
                    if ($item['total_price'] < 0) {
                        $this->addError('起送金额不能小于0');
                    }
                }
            }]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $data = [
            'is_enable' => $this->is_enable,
            'total_price' => $this->total_price,
            'is_total_price' => $this->is_total_price,
            'detail' => $this->detail ?: []
        ];

        $res = CommonOption::set(
            Option::NAME_OFFER_PRICE,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->user->identity->mch_id
        );
        if ($res) {
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
