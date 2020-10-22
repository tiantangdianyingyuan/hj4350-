<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mch;

use app\core\response\ApiCode;
use app\forms\common\version\Compatible;
use app\models\Model;
use app\models\Option;
use app\plugins\mch\models\MchSetting;

class MchSettingEditForm extends Model
{
    public $id;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $is_web_service;
    public $web_service_url;
    public $web_service_pic;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_web_service'], 'integer'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'send_type'], 'required'],
            [['web_service_url', 'web_service_pic'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $model = MchSetting::findOne($this->id);

                if (!$model) {
                    throw new \Exception('商户设置异常');
                }
            } else {
                $model = new MchSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
            }
            $this->send_type = Compatible::getInstance()->sendType($this->send_type);

            $model->is_mail = $this->is_mail;
            $model->is_print = $this->is_print;
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_territorial_limitation = $this->is_territorial_limitation;
            $model->send_type = \Yii::$app->serializer->encode($this->send_type);
            $model->is_web_service = $this->is_web_service;
            $model->web_service_pic = $this->web_service_pic;
            $model->web_service_url = $this->web_service_url;
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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
