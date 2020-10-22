<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\template\TemplateSend;
use app\models\Model;
use app\models\Option;
use app\models\TemplateRecord;
use app\models\User;

class TemplateSendForm extends Model
{
    public $userId;
    public $formData;
    public $templateData;

    public function rules()
    {
        return [
            [['userId', 'formData', 'templateData',], 'safe']
        ];
    }

    public function send()
    {
        try {

            $option = CommonOption::get(Option::NAME_WX_TEMPLATE, \Yii::$app->mall->id, Option::GROUP_APP);
            $sign = false;
            foreach ($option as $item) {
                if ($item['name'] == $this->templateData['name'] && $item['tpl_id'] == $this->templateData['tpl_id']) {
                    $sign = true;
                }
            }

            if (!$sign) {
                throw new \Exception('请选择模板');
            }

            if ($this->formData['is_all']) {
                $user = User::findOne($this->userId);

                $form = new TemplateSend();
                $form->user = $user;
                $newArr = [];
                foreach ($this->templateData['fields'] as $index => $item) {
                    $keyName = 'keyword' . ($index + 1);
                    $newArr[$keyName] = [
                        'value' => $item['field_value']
                    ];
                }
                $form->page = $this->templateData['link_url'];
                $form->data = $newArr;
                $form->templateId = $this->templateData['tpl_id'];
                $form->titleStyle = $this->templateData['style'];
                $res = $form->sendTemplate();
                $isDone = true;

                while ($isDone) {
                    if (\Yii::$app->queue->isDone($res['queueId'])) {
                        $templateRecord = TemplateRecord::find()->where(['token' => $res['token']])->one();
                        $isDone = false;
                    }
                }

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '发送成功',
                    'data' => [
                        'template_record' => $templateRecord
                    ]
                ];
            } else {
            }
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
