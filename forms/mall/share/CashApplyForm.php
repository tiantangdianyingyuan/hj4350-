<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/26
 * Time: 16:25
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShareCash;
use app\forms\common\share\CommonShareLevel;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Mall;
use app\models\Model;
use app\models\ShareCash;

/**
 * @property Mall $mall
 */
class CashApplyForm extends Model
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
        $shareCash = ShareCash::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->id]);

        if (!$shareCash) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现记录不存在'
            ];
        }

        if ($shareCash->status == 2) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已打款'
            ];
        }

        if ($shareCash->status == 3) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $shareCash->status) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '状态错误, 请刷新重试'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->apply($shareCash);
                    break;
                case 2:
                    $this->remit($shareCash);
                    $commonShareLevel = CommonShareLevel::getInstance();
                    // 打款触发分销商等级提升
                    $commonShareLevel->userId = $shareCash->user_id;
                    $commonShareLevel->levelShare(CommonShareLevel::TOTAL_CASH);
                    break;
                case 3:
                    $this->reject($shareCash);
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
     * @param ShareCash $shareCash
     * @throws \Exception
     * @return bool
     */
    private function apply($shareCash)
    {
        $extra = \Yii::$app->serializer->decode($shareCash->extra);
        $shareCash->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->content ?: '申请通过';
        $shareCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$shareCash->save()) {
            throw new \Exception($this->getErrorMsg($shareCash));
        }
        return true;
    }

    /**
     * @param ShareCash $shareCash
     * @throws \Exception
     * @return bool
     */
    private function remit($shareCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($shareCash->extra);
        $shareCash->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->content;
        $shareCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$shareCash->save()) {
            throw new \Exception($this->getErrorMsg($shareCash));
        }

        // 提现打款
        $form = new CommonShareCash();
        $form->shareCash = $shareCash;
        $remit = $form->remit();

        // 发送模板消息
        try {
            $serviceCharge = $shareCash->price * $shareCash->service_charge / 100;
            $tplMsg = new WithdrawSuccessTemplate([
                'page' => 'pages/share/cash-detail/cash-detail',
                'user' => $shareCash->user,
                'remark' => $this->content ? $this->content : '提现成功',
                'price' => $shareCash->price,
                'serviceChange' => price_format($serviceCharge),
                'type' => $shareCash->getTypeText2($shareCash->type)
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    /**
     * @param ShareCash $shareCash
     * @throws \Exception
     * @return bool
     */
    private function reject($shareCash)
    {
        if (!$this->content) {
            throw new \Exception('请填写备注');
        }
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($shareCash->extra);
        $shareCash->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = $this->content;
        $shareCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$shareCash->save()) {
            throw new \Exception($this->getErrorMsg($shareCash));
        }

        // 拒绝打款
        $form = new CommonShareCash();
        $form->shareCash = $shareCash;
        $reject = $form->reject();

        // 发送模板消息
        try {
            $tplMsg = new WithdrawErrorTemplate([
                'page' => 'pages/share/cash-detail/cash-detail',
                'user' => $shareCash->user,
                'remark' => $extra['reject_content'] ? $extra['reject_content'] : '拒绝提现',
                'price' => $shareCash->price,
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

        $cash = ShareCash::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

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
}
