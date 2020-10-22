<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/30
 * Time: 14:39
 */

namespace app\forms\mall\finance;

use app\core\response\ApiCode;
use app\forms\common\remit\CommonRemit;
use app\forms\common\remit\RemitForm;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Finance;
use app\models\Model;

abstract class BaseCashApply extends Model
{
    public $mall;

    public $id;
    public $status;
    public $content;

    public function rules()
    {
        return [
            [['id', 'status'], 'required'],
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

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $this->mall = \Yii::$app->mall;
        $cash = Finance::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->id]);

        if (!$cash) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现记录不存在'
            ];
        }

        if ($cash->status == 2) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已打款'
            ];
        }

        if ($cash->status == 3) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $cash->status) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '状态错误, 请刷新重试'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->beforeApply($cash);
                    $this->apply($cash);
                    $this->afterApply($cash);
                    break;
                case 2:
                    $this->beforeRemit($cash);
                    $this->remit($cash);
                    $this->afterRemit($cash);
                    break;
                case 3:
                    $this->beforeReject($cash);
                    $this->reject($cash);
                    $this->afterReject($cash);
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
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    private function apply($cash)
    {
        $extra = \Yii::$app->serializer->decode($cash->extra);
        $cash->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->content ?: '申请通过';
        $cash->extra = \Yii::$app->serializer->encode($extra);
        if (!$cash->save()) {
            throw new \Exception($this->getErrorMsg($cash));
        }
        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    private function remit($cash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($cash->extra);
        $cash->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->content;
        $cash->extra = \Yii::$app->serializer->encode($extra);
        if (!$cash->save()) {
            throw new \Exception($this->getErrorMsg($cash));
        }

        $serviceCharge = round($cash->price * $cash->service_charge / 100, 2);
        $actualPrice = round($cash->price - $serviceCharge, 2);

        $commonRemit = CommonRemit::getInstance();
        $commonRemit->user = $cash->user;
        $commonRemit->remitForm = new RemitForm([
            'orderNo' => $cash->order_no,
            'amount' => $actualPrice,
            'user' => $cash->user,
            'title' => $this->title(),
            'type' => $cash->type,
            'desc' => $this->desc($cash),
            'price' => $cash->price,
            'service_charge' => $serviceCharge,
        ]);
        $commonRemit->remit();

        // 发送模板消息
        try {
            $tplMsg = new WithdrawSuccessTemplate([
                'page' => $this->templatePath(),
                'user' => $cash->user,
                'remark' => $this->content ? $this->content : '提现成功',
                'price' => $cash->price,
                'serviceChange' => price_format($serviceCharge),
                'type' => $cash->getTypeText2($cash->type)
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    private function reject($cash)
    {
        if (!$this->content) {
            throw new \Exception('请填写备注');
        }
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($cash->extra);
        $cash->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = $this->content;
        $cash->extra = \Yii::$app->serializer->encode($extra);
        if (!$cash->save()) {
            throw new \Exception($this->getErrorMsg($cash));
        }

        // 发送模板消息
        try {
            $tplMsg = new WithdrawErrorTemplate([
                'page' => $this->templatePath(),
                'user' => $cash->user,
                'remark' => $extra['reject_content'] ? $extra['reject_content'] : '拒绝提现',
                'price' => $cash->price,
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    public function remark()
    {
        if (!isset($this->content)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写备注'
            ];
        }

        $cash = Finance::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

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

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function beforeApply($cash)
    {
        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function afterApply($cash)
    {
        return true;
    }

    protected function beforeRemit($cash)
    {
        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function afterRemit($cash)
    {
        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function beforeReject($cash)
    {
        return true;
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function afterReject($cash)
    {
        return true;
    }

    protected function templatePath()
    {
        return '';
    }

    protected function title()
    {
        return '提现';
    }

    /**
     * @param Finance $cash
     * @throws \Exception
     * @return bool
     */
    protected function desc($cash)
    {
        return '提现金额：' . $cash->price . '，提现手续费：' . $cash->service_charge . '%';
    }
}
