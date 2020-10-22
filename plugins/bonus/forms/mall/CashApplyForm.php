<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/15
 * Time: 16:09
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Model;
use app\plugins\bonus\forms\common\CommonBonusCash;
use app\plugins\bonus\models\BonusCash;

class CashApplyForm extends Model
{
    public $mall;

    public $id;
    public $status;
    public $content;

    public function rules()
    {
        return [
            [['id', 'status',], 'required'],
            [['id', 'status'], 'integer'],
            ['status', 'in', 'range' => [1, 2, 3]],
            ['content', 'trim'],
            ['content', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => '备注'
        ];
    }

    public function remark()
    {
        if (!isset($this->content)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写备注'
            ];
        }

        $cash = BonusCash::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

        if (!$cash) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现记录不存在'
            ];
        }
        $cash->content = $this->content;
        if ($cash->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($cash);
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $this->mall = \Yii::$app->mall;
        $bonusCash = BonusCash::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->id]);

        if (!$bonusCash) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现记录不存在'
            ];
        }

        if ($bonusCash->status == 2) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已打款'
            ];
        }

        if ($bonusCash->status == 3) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $bonusCash->status) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '状态错误, 请刷新重试'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->apply($bonusCash);
                    break;
                case 2:
                    $this->remit($bonusCash);
                    break;
                case 3:
                    if (empty($this->content)) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '请填写驳回理由'
                        ];
                    }
                    $this->reject($bonusCash);
                    break;
                default:
                    throw new \Exception('错误的提现类型');
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param BonusCash $bonusCash
     * @throws \Exception
     * @return bool
     */
    private function apply($bonusCash)
    {
        $extra = \Yii::$app->serializer->decode($bonusCash->extra);
        $bonusCash->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->content;
        $bonusCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$bonusCash->save()) {
            throw new \Exception($this->getErrorMsg($bonusCash));
        }
        return true;
    }

    /**
     * @param BonusCash $bonusCash
     * @throws \Exception
     * @return bool
     */
    private function remit($bonusCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($bonusCash->extra);
        $bonusCash->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->content;
        $bonusCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$bonusCash->save()) {
            throw new \Exception($this->getErrorMsg($bonusCash));
        }

        // 提现打款
        $form = new CommonBonusCash();
        $form->bonusCash = $bonusCash;
        $remit = $form->remit();

        try {
            $serviceCharge = $bonusCash->price * $bonusCash->service_charge / 100;
            $tplMsg = new WithdrawSuccessTemplate([
                'page' => 'plugins/bonus/cash-detail/cash-detail',
                'user' => $bonusCash->user,
                'payPrice' => price_format($bonusCash->price - $serviceCharge),
                'time' => $extra['remittance_at'],
                'price' => $bonusCash->price,
                'serviceChange' => price_format($serviceCharge),
                'type' => $bonusCash->getTypeText2($bonusCash->type)
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    /**
     * @param BonusCash $bonusCash
     * @throws \Exception
     * @return bool
     */
    private function reject($bonusCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($bonusCash->extra);
        $bonusCash->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = $this->content;
        $bonusCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$bonusCash->save()) {
            throw new \Exception($this->getErrorMsg($bonusCash));
        }

        // 拒绝打款
        $form = new CommonBonusCash();
        $form->bonusCash = $bonusCash;
        $reject = $form->reject();

        try {
            $tplMsg = new WithdrawErrorTemplate([
                'page' => 'plugins/bonus/cash-detail/cash-detail',
                'user' => $bonusCash->user,
                'content' => $extra['reject_content'],
                'time' => $extra['reject_at'],
                'price' => $bonusCash->price,
                'type' => $bonusCash->getTypeText2($bonusCash->type)
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
        return true;
    }
}
