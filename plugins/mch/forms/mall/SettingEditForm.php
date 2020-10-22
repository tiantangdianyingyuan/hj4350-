<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\plugins\mch\models\Mch;

class SettingEditForm extends Model
{
    public $form;

    public function rules()
    {
        return [
            [['form'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!isset($this->form['form_data'])) {
                $this->form['form_data'] = [];
            }
            $option = CommonOption::set(
                Option::NAME_MCH_MALL_SETTING,
                $this->form,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            if (!$option) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
