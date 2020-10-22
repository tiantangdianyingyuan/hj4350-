<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/15
 * Time: 16:09
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Model;
use app\plugins\region\forms\common\CommonRegionCash;
use app\plugins\region\models\RegionCash;

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

        $cash = RegionCash::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

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
        $regionCash = RegionCash::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->id]);

        if (!$regionCash) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现记录不存在'
            ];
        }

        if ($regionCash->status == 2) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已打款'
            ];
        }

        if ($regionCash->status == 3) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $regionCash->status) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '状态错误, 请刷新重试'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->apply($regionCash);
                    break;
                case 2:
                    $this->remit($regionCash);
                    break;
                case 3:
                    if (empty($this->content)) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '请填写驳回理由'
                        ];
                    }
                    $this->reject($regionCash);
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
     * @param RegionCash $regionCash
     * @return bool
     * @throws \Exception
     */
    private function apply($regionCash)
    {
        $extra = \Yii::$app->serializer->decode($regionCash->extra);
        $regionCash->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->content;
        $regionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$regionCash->save()) {
            throw new \Exception($this->getErrorMsg($regionCash));
        }
        return true;
    }

    /**
     * @param RegionCash $regionCash
     * @return bool
     * @throws \Exception
     */
    private function remit($regionCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($regionCash->extra);
        $regionCash->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->content;
        $regionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$regionCash->save()) {
            throw new \Exception($this->getErrorMsg($regionCash));
        }

        // 提现打款
        $form = new CommonRegionCash();
        $form->bonusCash = $regionCash;
        $remit = $form->remit();

        try {
            $serviceCharge = $regionCash->price * $regionCash->service_charge / 100;
            $tplMsg = new WithdrawSuccessTemplate(
                [
                    'page' => 'plugins/region/cash-detail/cash-detail',
                    'user' => $regionCash->user,
                    'remark' => $this->content ? $this->content : '提现成功',
                    'price' => $regionCash->price,
                    'serviceChange' => price_format($serviceCharge),
                    'type' => $regionCash->getTypeText2($regionCash->type)
                ]
            );
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    /**
     * @param RegionCash $regionCash
     * @return bool
     * @throws \Exception
     */
    private function reject($regionCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($regionCash->extra);
        $regionCash->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = $this->content;
        $regionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$regionCash->save()) {
            throw new \Exception($this->getErrorMsg($regionCash));
        }

        // 拒绝打款
        $form = new CommonRegionCash();
        $form->bonusCash = $regionCash;
        $reject = $form->reject();

        try {
            $tplMsg = new WithdrawErrorTemplate(
                [
                    'page' => 'plugins/region/cash-detail/cash-detail',
                    'user' => $regionCash->user,
                    'remark' => $extra['reject_content'],
                    'price' => $regionCash->price,
                ]
            );
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
        return true;
    }
}
