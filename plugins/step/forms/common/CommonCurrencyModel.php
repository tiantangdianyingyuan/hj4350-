<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\common;

use app\models\Model;
use app\plugins\step\models\StepLog;

class CommonCurrencyModel extends Model
{
    public $stepUser;

    public function setUser($step = null)
    {
        $this->stepUser = $step ?: CommonStep::getUser();
        return $this;
    }

    public function add($currency, $desc, $customDesc = '用户活力币变动详情')
    {
        if (!is_float($currency) && !is_int($currency) && !is_double($currency)) {
            throw new \Exception('金额必须为数字类型');
        }

        $t = \Yii::$app->db->beginTransaction();
        $this->stepUser->step_currency += $currency;
        if ($this->stepUser->save()) {
            try {
                $this->createLog(1, $currency, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (\Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new \Exception($this->getErrorMsg($this->stepUser), $this->stepUser->errors, 1);
        }
    }

    public function sub($currency, $desc, $customDesc = '用户活力币变动详情')
    {
        if (!is_float($currency) && !is_int($currency) && !is_double($currency)) {
            throw new \Exception('金额必须为数字类型');
        }

        if ($this->stepUser->step_currency < $currency) {
            $currency_name = CommonStep::getSetting($this->stepUser->mall_id)['currency_name'];
            throw new \Exception(sprintf("用户%s不足", $currency_name));
        }

        $t = \Yii::$app->db->beginTransaction();
        $this->stepUser->step_currency -= $currency;
        if ($this->stepUser->save()) {
            try {
                $this->createLog(2, $currency, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (\Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new \Exception($this->getErrorMsg($this->stepUser), $this->stepUser->errors, 1);
        }
    }

    private function createLog($type, $currency, $desc, $customDesc = '用户余额变动说明')
    {
        $form = new StepLog();
        $form->mall_id = $this->stepUser->mall_id;
        $form->step_id = $this->stepUser->id;
        $form->type = $type;
        $form->currency = $currency;
        $form->remark = $desc;
        $form->data = $customDesc;
        if ($form->save()) {
            return true;
        } else {
            throw new \Exception($this->getErrorMsg($form), $form->errors, 1);
        }
    }
}
