<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateSend;
use app\forms\common\template\tplmsg\AudiResultTemplate;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\mch\forms\common\MchEditFormBase;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchMallSetting;
use app\plugins\mch\models\MchSetting;
use app\plugins\wxapp\models\WxappTemplate;

class MchEditForm extends MchEditFormBase
{
    public $is_review;
    public $review_status;
    public $review_remark;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['review_remark'], 'string'],
            [['review_status', 'is_review'], 'integer']
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
            $this->sendTemplateMsg();

            $transaction->commit();
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

    /**
     * @param Mch $mch
     * @return bool
     * @throws \Exception
     */
    protected function extraMchInfo($mch)
    {
        if ($this->is_review) {
            if (!$this->review_status) {
                throw new \Exception('请选择审核状态');
            }

            // 后台操作 商户审核时提交
            if ($this->is_review) {
                $mch->review_status = $this->review_status;
                $mch->review_remark = $this->review_remark;
                $mch->review_time = mysql_timestamp();
            }
        }
        return true;
    }

    protected function getMch()
    {
        if ($this->id) {
            $mch = Mch::findOne(['id' => $this->id, 'is_delete' => 0]);
            if (!$mch) {
                throw new \Exception('商户不存在,ID:' . $this->id);
            }
        } else {
            $mch = new Mch();
            $mch->mall_id = \Yii::$app->mall->id;
            $mch->review_status = 1;
            $mch->review_remark = '后台添加,操作用户:' . \Yii::$app->user->identity->nickname;
            $mch->review_time = mysql_timestamp();
            $mch->form_data = \Yii::$app->serializer->encode([]);

            $this->review_status = 1;
            $this->review_remark = '创建新商户';
        }

        return $mch;
    }

    private function sendTemplateMsg()
    {
        try {
            $user = User::findOne($this->user_id);
            if (!$user) {
                throw new \Exception('用户不存在！,商户审核订阅消息发送失败');
            }

            $auditResultTemplate = new AudiResultTemplate([
                'remark' => $this->review_remark,
                'result' => $this->review_status == 1 ? '商户审核通过' : '商户审核不通过',
                'name' => $user->nickname,
                'time' => mysql_timestamp()
            ]);

            $auditResultTemplate->page = 'pages/index/index';
            $auditResultTemplate->user = $user;
            $res = $auditResultTemplate->send();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    protected function checkData()
    {
        if ($this->bg_pic_url && !is_array($this->bg_pic_url)) {
            throw new \Exception('店铺背景图参数错误');
        }
        if ($this->transfer_rate < 0 || $this->transfer_rate > 1000) {
            throw new \Exception('请填写0~1000数值之间的手续费');
        }
    }
}
