<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonQrCode;
use app\forms\common\template\TemplateSend;
use app\models\Model;
use app\models\Option;
use app\models\TemplateRecord;
use app\models\User;

class TestUserForm extends Model
{
    public $user;
    public $user_id;
    public $tpl_id;

    public function addTestUser()
    {
        $name = null;
        $platform = \Yii::$app->request->post('platform');
        switch ($platform) {
            case 'wxapp':
                $name = Option::NAME_WX_TEMPLATE_TEST_USER;
                break;
            case 'aliapp':
                $name = Option::NAME_ALI_TEMPLATE_TEST_USER;
                break;
            case 'bdapp':
                $name = Option::NAME_BD_TEMPLATE_TEST_USER;
                break;
            case 'ttapp':
                $name = Option::NAME_TT_TEMPLATE_TEST_USER;
                break;
            default:
                throw new \Exception("未知平台标识$platform");
                break;
        }
        $option = CommonOption::set(
            $name,
            $this->user,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }

    public function getTestUser()
    {
        $name = null;
        $platform = \Yii::$app->request->get('platform');
        switch ($platform) {
            case 'wxapp':
                $name = Option::NAME_WX_TEMPLATE_TEST_USER;
                break;
            case 'aliapp':
                $name = Option::NAME_ALI_TEMPLATE_TEST_USER;
                break;
            case 'bdapp':
                $name = Option::NAME_BD_TEMPLATE_TEST_USER;
                break;
            case 'ttapp':
                $name = Option::NAME_TT_TEMPLATE_TEST_USER;
                break;
            default:
                throw new \Exception("未知平台标识$platform");
                break;
        }
        $users = CommonOption::get(
            $name,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
            'data' => [
                'users' => $users ?: []
            ]
        ];
    }

    public function testSend()
    {
        try {
            $user = User::findOne($this->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $form = new TemplateSend();
            $form->user = $user;
            $newArr = [];
            for ($i = 0; $i < 6; $i++) {
                $keyName = 'keyword' . ($i + 1);
                $newArr[$keyName] = [
                    'value' => '测试' . ($i + 1)
                ];
            }

            $form->page = 'pages/index/index';
            $form->data = $newArr;
            $form->templateId = $this->tpl_id;
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

    public $platform;

    public function rules()
    {
        return [
            [['platform'], 'trim'],
            [['platform'], 'string'],
            [['platform'], function ($attr, $param) {
                if (!in_array($this->$attr, ['wxapp', 'aliapp', 'bdapp', 'ttapp'])) {
                    $this->addError('未能识别平台标示' . $this->$attr);
                }
            }],
        ];
    }

    public function getQrcode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $qrcode = new CommonQrCode();
            $qrcode->appPlatform = $this->platform;
            $list = $qrcode->getQrCode('', 430, 'pages/template-msg/template-msg');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $list['file_path']
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
