<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\printer;

use app\core\response\ApiCode;
use app\models\Option;
use app\models\Printer;
use app\forms\common\CommonOption;
use app\models\Model;

class PrinterOptionForm extends Model
{
    const DEFAULT = [
        'printer_id' => '',
        'is_attr' => 0,
        'block_id' => '',
        'type' => '',
    ];

    public $printer_id;
    public $is_attr;
    public $block_id; //打印模板ID
    public $type;

    public function rules()
    {
        return [
            [['printer_id', 'is_attr', 'type'], 'required'],
            [['printer_id', 'is_attr', 'block_id'], 'integer'],
            [['type'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'printer_id' => '打印机ID',
            'is_attr' => '是否打印规格 ',
            'type' => '打印方式',
            'block_id' => '打印模板ID',
        ];
    }

    public function getList()
    {
        $list =  CommonOption::get(Option::NAME_PRINTER_SETTING, \Yii::$app->mall->id, Option::GROUP_APP, PrinterOptionForm::DEFAULT);
        $select = Printer::find()->where(['mall_id' => \Yii::$app->mall->id])->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => $select,
            ]
        ];
    }
    
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $data = [
            'printer_id' => $this->printer_id,
            'is_attr' => $this->is_attr,
            'type' => $this->type,
            'block_id' => $this->block_id,
        ];
        CommonOption::set(Option::NAME_PRINTER_SETTING, $data, \Yii::$app->mall->id, Option::GROUP_APP);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}
