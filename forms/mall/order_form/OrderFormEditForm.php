<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_form;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\form\CommonForm;
use app\models\Form;
use app\models\Model;
use app\models\Option;

class OrderFormEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $commonForm = CommonForm::getInstance();
            if ($this->data['id'] && $this->data['id'] > 0) {
                $model = $commonForm->getDetail($this->data['id']);
            } else {
                $model = new Form();
                $model->is_delete = 0;
                $model->is_default = CommonForm::FORM_NOT_DEFAULT;
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->getMchId();
            }
            $model->status = $this->data['status'];
            $model->name = $this->data['name'];
            $model->value = json_encode($this->data['value'], JSON_UNESCAPED_UNICODE);

            if (!$model->save()) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 检测数据
    public function checkData()
    {
        if ($this->data['status'] == 0) {
            !isset($this->data['value']) ? $this->data['value'] = [] : '';
            return;
        }

        foreach ($this->data as $key => $item) {
            if (!is_array($item) && !$item && !in_array($key, ['id'])) {
                throw new \Exception('请检查信息是否填写完整');
            }
        }
        foreach ($this->data['value'] as $item) {
            if (!$item['name']) {
                throw new \Exception('请填写 ' . $item['key_name'] . ' 名称');
            }
            if (isset($item['list'])) {
                foreach ($item['list'] as $item2) {
                    if (!$item2['label']) {
                        throw new \Exception('请填写 ' . $item['key_name'] . ' 选项名称');
                    }
                }
            }
        }
    }
}
