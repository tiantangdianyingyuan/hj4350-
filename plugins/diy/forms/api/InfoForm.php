<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/6
 * Time: 9:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\api;


use app\core\response\ApiCode;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Model;
use app\plugins\diy\models\DiyForm;

class InfoForm extends Model
{
    public $form_data;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
            [['form_data'], 'formValidator']
        ];
    }

    public function formValidator($attr, $param)
    {
        if (!is_array($this->$attr) && !is_object($this->$attr)) {
            $this->addError($attr, '上传数据出错');
        }
        foreach ($this->$attr as &$item) {
            if (isset($item['key']) && in_array($item['key'], ['radio', 'checkbox'])) {
                if (isset($item['list']) && is_array($item['list'])) {
                    foreach ($item['list'] as $value) {
                        if ($value['value'] == 'true') {
                            $item['value'][] = $value['label'];
                        }
                    }
                }
            }
            if (isset($item['is_required']) && $item['is_required'] == 1
                && (!isset($item['value']) || !$item['value'])) {
                $this->addError($attr, $item['key_name'] . '必填');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'form_data' => '表单'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = new DiyForm();
        $model->mall_id = \Yii::$app->mall->id;
        $model->user_id = \Yii::$app->user->id;
        $model->form_data = \Yii::$app->serializer->encode($this->form_data);
        $model->is_delete = 0;
        if (!$model->save()) {
            $this->getErrorResponse($model);
        } else {
            try {
                $tplMsg = new MpTplMsgSend();
                $tplMsg->method = 'applySubmitTpl';
                $tplMsg->params = [
                    'time' => $model->attributes['created_at'],
                ];
                $tplMsg->sendTemplate(new MpTplMsgDSend());
            } catch (\Exception $e) {
                \Yii::error('表单提醒发送失败 =>' . $e->getMessage());
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '提交成功'
        ];
    }
}
