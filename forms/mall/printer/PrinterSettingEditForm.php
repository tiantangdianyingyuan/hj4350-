<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\printer;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\PrinterSetting;
use yii\base\DynamicModel;

class PrinterSettingEditForm extends Model
{
    public $printer_id;
    public $is_attr;
    public $block_id; //打印模板ID
    public $type;
    public $id;
    public $status;
    public $store_id;
    public $big;
    public $show_type;
    public $order_send_type;

    public function rules()
    {
        return [
            [['printer_id', 'is_attr', 'type', 'status', 'store_id', 'show_type', 'order_send_type'], 'required'],
            [['printer_id', 'is_attr', 'block_id', 'id', 'status', 'store_id', 'big'], 'integer'],
            [['type', 'show_type', 'order_send_type'], 'trim'],
            [['block_id', 'big'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'printer_id' => '打印机ID',
            'is_attr' => '是否打印规格 ',
            'type' => '打印方式',
            'show_type' => '显示方式',
            'block_id' => '打印模板ID',
            'status' => '是否启用',
            'store_id' => '门店',
            'big' => '倍数',
            'order_send_type' => '订单方式'
        ];
    }

    private function validateType($order, $pay, $confirm)
    {
        $model = DynamicModel::validateData(compact('order', 'pay', 'confirm'), [
            [['order', 'pay', 'confirm'], 'required'],
        ]);
        return $model->hasErrors();
    }

    private function validateShowType($attr, $goods_no, $form_data)
    {
        $model = DynamicModel::validateData(compact('attr', 'goods_no', 'form_data'), [
            [['attr', 'goods_no', 'form_data'], 'required'],
        ]);
        return $model->hasErrors();
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = PrinterSetting::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new PrinterSetting();
        }
        $model->attributes = $this->attributes;

        $type = $this->type;
        $show_type = $this->show_type;
        $order_send_type = $this->order_send_type;
        if ($this->validateType($type['order'], $type['pay'], $type['confirm'])
            || $this->validateShowType($show_type['attr'], $show_type['goods_no'], $show_type['form_data'])
        ) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不合法'
            ];
        }
        $model->type = \yii\helpers\BaseJson::encode($type);
        $model->show_type = \yii\helpers\BaseJson::encode($show_type);
        $model->order_send_type = \yii\helpers\BaseJson::encode($order_send_type);
        $model->mall_id = \Yii::$app->mall->id;
        $model->mch_id = \Yii::$app->user->identity->mch_id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
