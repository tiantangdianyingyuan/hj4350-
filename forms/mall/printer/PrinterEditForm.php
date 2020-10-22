<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\printer;

use app\core\response\ApiCode;
use app\models\Printer;
use app\models\Model;
use yii\base\DynamicModel;

class PrinterEditForm extends Model
{
    public $id;
    public $type;
    public $name;
    public $setting;

    public function rules()
    {
        return [
            [['type', 'name', 'setting'], 'required'],
            [['setting'], 'trim'],
            [['id'], 'integer'],
            [['type'], 'in', 'range' => ['360-kdt2', 'yilianyun-k4', 'feie', 'gainscha-gp']],
            [['type', 'name'], 'string', 'max' => 255],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'name' => '名称',
            'setting' => '设置',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->validateInfo()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '打印机配置不全'
            ];
        }

        $model = Printer::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Printer();
            $model->mall_id = \Yii::$app->mall->id;
            $model->mch_id = \Yii::$app->user->identity->mch_id;
        }
        $model->attributes = $this->attributes;
        $model->setting = json_encode($this->setting);
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }

    private function validateInfo()
    {
        $setting = $this->setting;
        $validate = true;
        if ($this->type == '360-kdt2') {
            $validate = $this->validateKdt2($setting['name'], $setting['key'], $setting['time']);
        }

        if ($this->type == 'yilianyun-k4') {
            $validate = $this->validateK4(
                $setting['machine_code'],
                $setting['key'],
                $setting['client_id'],
                $setting['client_key'],
                $setting['time']
            );
        }

        if ($this->type == 'feie') {
            $validate = $this->validateFeie(
                $setting['user'],
                $setting['ukey'],
                $setting['sn'],
                $setting['key'],
                $setting['time']
            );
        }
        if ($this->type == 'gainscha-gp') {
            $validate = $this->validateGp(
                $setting['apiKey'],
                $setting['memberCode'],
                $setting['deviceNo'],
                $setting['time']
            );
        }
        return $validate;
    }

    private function validateKdt2($name, $key, $time)
    {
        $model = DynamicModel::validateData(compact('name', 'key', 'time'), [
            [['name', 'key', 'time'], 'required'],
        ]);
        return $model->hasErrors();
    }

    private function validateK4($machine_code, $key, $client_id, $client_key, $time)
    {
        $model = DynamicModel::validateData(compact('machine_code', 'key', 'client_id', 'client_key', 'time'), [
            [['machine_code', 'key', 'client_id', 'client_key', 'time'], 'required'],
        ]);
        return $model->hasErrors();
    }

    private function validateFeie($user, $ukey, $sn, $key, $time)
    {
        $model = DynamicModel::validateData(compact('user', 'ukey', 'sn', 'key', 'time'), [
            [['user', 'ukey', 'sn', 'key', 'time'], 'required'],
        ]);
        return $model->hasErrors();
    }

    private function validateGp($apiKey, $memberCode, $deviceNo, $time)
    {
        $model = DynamicModel::validateData(compact('apiKey', 'memberCode', 'deviceNo', 'time'), [
            [['apiKey', 'memberCode', 'deviceNo', 'time'], 'required'],
        ]);
        return $model->hasErrors();
    }
}
