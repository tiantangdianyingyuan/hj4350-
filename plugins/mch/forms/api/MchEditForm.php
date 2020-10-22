<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\mch\MchSettingForm;
use app\forms\common\mptemplate\MpTplMsgCSend;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Option;
use app\plugins\mch\forms\common\MchEditFormBase;
use app\plugins\mch\models\Mch;

class MchEditForm extends MchEditFormBase
{
    public $form_data;
    public $is_update_apply;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['form_data'], 'safe'],
            [['is_update_apply'], 'integer'],
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setMch();
            $this->setStore();
            $this->setMallMchSetting();
            $this->setMchSetting();
            $this->setUser();

            $transaction->commit();
            $this->sendMpTpl();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    protected function setMch()
    {
        if ($this->id) {
            $mch = $this->getMch();
            if (!$mch) {
                throw new \Exception('商户不存在,ID:' . $this->id);
            }

            // 用户重新申请入驻
            if ($this->is_update_apply == 1) {
                $mch->review_status = 0;
                $mch->review_remark = '用户重新申请:' . \Yii::$app->user->identity->nickname;
            }
        } else {
            $mch = Mch::find()->where([
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
            ])->andWhere(['review_status' => 1])->one();

            if ($mch) {
                throw new \Exception('已是入驻商户！请登录');
            }
            $mch = new Mch();
            $mch->mall_id = \Yii::$app->mall->id;
        }

        $mch->user_id = $this->user_id ?: \Yii::$app->user->id;
        $mch->realname = $this->realname;
        $mch->mobile = $this->mobile;
        $mch->mch_common_cat_id = $this->mch_common_cat_id;
        $mch->wechat = $this->wechat;
        $mch->form_data = $this->form_data;

        $res = $mch->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($mch));
        }
        $this->mch = $mch;
    }

    private function checkData()
    {
        $setting = (new MchSettingForm())->search();
        if ($setting['status'] == 1) {
            if (!$this->form_data) {
                throw new \Exception('请传入完善表单内容');
            }
            $formData = \Yii::$app->serializer->decode($this->form_data);
            foreach ($formData as $item) {
                if ($item['required'] && !$item['value']) {
                    throw new \Exception($item['label'] . '不能为空');
                }
            }
        } else {
            $this->form_data = \Yii::$app->serializer->encode([]);
        }
    }

    private function sendMpTpl()
    {
        // 只有申请入驻时才发送公众号消息
        if (isset($this->is_update_apply)) {
            try {
                $option = CommonOption::get(Option::NAME_WX_PLATFORM, \Yii::$app->mall->id, Option::GROUP_APP);
                $customize = new MpTplMsgCSend();
                $newAdminOptionList = [];
                foreach ($option['admin_open_list'] as $item) {
                    $newAdminOptionList[] = json_encode($item);
                }
                $customize->admin_open_list = $newAdminOptionList;
                $sign = false;
                foreach ($option['template_list'] as $item) {
                    if ($item['key_name'] == 'mch_apply_tpl') {
                        $customize->template_id = $item['mch_apply_tpl'];
                        $sign = true;
                    }
                }
                if (!$sign) {
                    throw new \Exception('未找到公众号模板ID');
                }
                $customize->app_id = $option['app_id'];
                $customize->app_secret = $option['app_secret'];

                $tplMsg = new MpTplMsgSend();
                $tplMsg->method = 'mchApplyTpl';
                $tplMsg->params = [
                    'time' => date('Y-m-d H:i:s'),
                    'content' => '申请已提交',
                ];
                $tplMsg->sendTemplate($customize);
            } catch (\Exception $exception) {
                \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
            }
        }
    }
}
