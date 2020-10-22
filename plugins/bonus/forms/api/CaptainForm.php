<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/9
 * Time: 13:32
 */

namespace app\plugins\bonus\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\models\BonusCaptain;
use app\validators\PhoneNumberValidator;

class CaptainForm extends Model
{
    public $name;
    public $mobile;
    public $is_agree;

    public function rules()
    {
        return [
            [['name', 'mobile', 'is_agree'], 'required'],
            [['name'], 'trim'],
            [['mobile'], PhoneNumberValidator::className()],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '申请人姓名',
            'mobile' => '申请人联系方式',
            'is_agree' => '阅读申请协议',
        ];
    }

    public function apply()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (empty($this->is_agree)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "请先查看团队分红申请协议并同意"
            ];
        }

        $captain = BonusCaptain::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($captain && $captain->status != CommonCaptain::STATUS_AGAIN) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '你已经申请过了'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $captain = BonusCaptain::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id]);
            if (empty($captain)) {
                $captain = new BonusCaptain();
                $captain->attributes = $this->attributes;
                $captain->mall_id = \Yii::$app->mall->id;
                $captain->user_id = \Yii::$app->user->id;
                $captain->status = CommonCaptain::STATUS_APPLYING;
            } else {
                $captain->name = $this->name;
                $captain->mobile = $this->mobile;
                $captain->status = CommonCaptain::STATUS_APPLYING;
                $captain->created_at = mysql_timestamp();
                $captain->is_delete = 0;
            }

            if ($captain->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '申请队长成功'
                ];
            } else {
                throw new \Exception($this->getErrorMsg($captain));
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**查看队长申请状态**/
    public function getStatus()
    {
        $model = BonusCaptain::find()
            ->select(['b.*', 'i.avatar'])
            ->alias('b')
            ->where(['b.mall_id' => \Yii::$app->mall->id, 'b.user_id' => \Yii::$app->user->id])
            ->joinWith(['user u' => function ($query) {
                $query->select(['nickname', 'id', 'username', 'mobile', 'mall_id']);
            }])
            ->with(['level'])
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
            ->asArray()
            ->one();
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '用户未申请队长'
            ];
        }

        $price = new CommonCaptain();
        $cashed = $price->getPrice(\Yii::$app->user->id);
        $model['all_bonus'] = price_format($model['all_bonus']);
        $model['cash_bonus'] = price_format($cashed['cash_bonus']);
        $model['total_bonus'] = price_format($model['total_bonus']);
        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'captain' => $model
            ]
        ];
    }

    public function clearApply()
    {
        $captain = BonusCaptain::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id]);
        if ($captain) {
            $captain->status = CommonCaptain::STATUS_AGAIN;
            $captain->is_delete = 0;
            $captain->apply_at = '0000-00-00 00:00:00';
            $captain->remark = '';
            $captain->reason = '';
            $captain->save();
        }
        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => ''
        ];
    }
}