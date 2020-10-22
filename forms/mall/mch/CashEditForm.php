<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mch;


use app\core\response\ApiCode;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Model;
use app\models\Order;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class CashEditForm extends Model
{
    public $mch_id;
    public $money;
    public $type;
    public $type_data;

    public function rules()
    {
        return [
            [['mch_id', 'money', 'type', 'type_data'], 'required'],
            [['type_data', 'type', 'money'], 'string'],
            [['mch_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $mch = Mch::findOne($this->mch_id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            if ($mch->account_money < $this->money) {
                throw new \Exception('可提现余额不足');
            }

            $form = new \app\forms\common\mch\MchSettingForm();
            $form->isDefaultCashType = true;
            $res = $form->search();
            if (!in_array($this->type, $res['cash_type'])) {
                throw new \Exception('提现类型参数错误,支持: wx|alipay|bank|balance');
            }

            $model = new MchCash();
            $model->mall_id = \Yii::$app->mall->id;
            $model->mch_id = $this->mch_id;
            $model->money = $this->money;
            $model->order_no = Order::getOrderNo('MC');
            $model->type = $this->type;
            $model->type_data = $this->type_data;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $mch->account_money = $mch->account_money - $this->money;
            $res = $mch->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $transaction->commit();

            $this->sendMpTplMsg($model);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '提现已申请',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function checkData()
    {
        $typeData = \Yii::$app->serializer->decode($this->type_data);
        if ($this->type == 'wx' || $this->type == 'alipay') {
            if (!$typeData->nickname) {
                throw new \Exception('请填写提现人姓名');
            }
            if (!$typeData->account) {
                throw new \Exception('请填写提现账号');
            }
        }
        if ($this->type == 'bank') {
            if (!$typeData->nickname) {
                throw new \Exception('请填写开户人');
            }
            if (!$typeData->bank_name) {
                throw new \Exception('请填写开户行');
            }
            if (!$typeData->account) {
                throw new \Exception('请填写提现账号');
            }
        }
    }

    /**
     * @param MchCash $model
     * 发给管理员公众号消息
     */
    private function sendMpTplMsg($model)
    {
        try {
            if ($model->mch->user) {
                $tplMsg = new MpTplMsgSend();
                $tplMsg->method = 'shareWithdrawTpl';
                $tplMsg->params = [
                    'time' => $model->created_at,
                    'money' => $model->money,
                    'user' => $model->mch->store->name . '(商户)',
                ];
                $tplMsg->sendTemplate(new MpTplMsgSend());
            }
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }
}
