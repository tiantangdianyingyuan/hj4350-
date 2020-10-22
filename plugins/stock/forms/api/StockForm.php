<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 13:32
 */

namespace app\plugins\stock\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Share;
use app\models\UserInfo;
use app\plugins\stock\forms\common\CommonStock;
use app\plugins\stock\models\StockCash;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;
use app\validators\PhoneNumberValidator;

class StockForm extends Model
{
    public $name;
    public $mobile;
    public $is_agree;

    public function rules()
    {
        return [
            [['is_agree'], 'integer'],
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

        $applyType = StockSetting::get(\Yii::$app->mall->id, 'apply_type', 0);
        $defaultLevel = StockLevel::findOne(['is_default' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        $status = CommonStock::STATUS_APPLYING;
        $agreeTime = '0000-00-00 00:00:00';
        if ($this->is_agree != 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "请先查看股东分红申请协议并同意"
            ];
        }
        if (
            $applyType == StockSetting::APPLY_INFO_NONEED_VERIFY
            || $applyType == StockSetting::APPLY_NOINFO_NONEED_VERIFY
        ) {
            $status = CommonStock::STATUS_BECOME;
            $agreeTime = mysql_timestamp();
        }

        if (
            ($applyType == StockSetting::APPLY_INFO_NEED_VERIFY
                || $applyType == StockSetting::APPLY_INFO_NONEED_VERIFY)
            && (empty($this->mobile) || empty($this->name))
        ) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "请完善申请信息"
            ];
        }

        $stock = StockUser::findOne([
            'user_id' => \Yii::$app->user->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if ($stock && $stock->status != -2) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '你已经申请过了'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $stock = StockUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id]);
            if (empty($stock)) {
                $stock = new StockUser();
                $stock->attributes = $this->attributes;
                $stock->mall_id = \Yii::$app->mall->id;
                $stock->user_id = \Yii::$app->user->id;
                $stock->status = $status;
                $stock->level_id = $defaultLevel->id ?? 0;
                $stock->applyed_at = mysql_timestamp();
                $stock->agreed_at = $agreeTime;

                $stockInfo = new StockUserInfo();
                $stockInfo->user_id = \Yii::$app->user->id;

                $share = Share::findOne(['user_id' => \Yii::$app->user->id]);
                $name = $this->name ?? $share->name;
                $phone = $this->mobile ?? $share->mobile;
                $stockInfo->name = $name;
                $stockInfo->phone = $phone;
                $stockInfo->reason = '';
            } else {
                $stock->created_at = mysql_timestamp();
                $stock->applyed_at = mysql_timestamp();
                $stock->agreed_at = $agreeTime;
                $stock->is_delete = 0;
                $stock->status = $status;
                $stock->level_id = $defaultLevel->id ?? 0;

                $stockInfo = StockUserInfo::findOne(['user_id' => \Yii::$app->user->id]);
                $name = $this->name ?? $stockInfo->name;
                $phone = $this->mobile ?? $stockInfo->phone;
                $stockInfo->name = $name;
                $stockInfo->phone = $phone;
                $stockInfo->remark = '';
                $stockInfo->reason = '';
            }

            if (!$stock->save()) {
                throw new \Exception($this->getErrorMsg($stock));
            }

            if (!$stockInfo->save()) {
                throw new \Exception($this->getErrorMsg($stockInfo));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '申请股东成功'
            ];
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
        $model = StockUser::find()
            ->select([
                's.*',
                'si.all_bonus',
                'si.total_bonus',
                'si.out_bonus',
                'si.name',
                'si.phone',
                'i.avatar',
                'si.reason'
            ])
            ->alias('s')
            ->where(['s.mall_id' => \Yii::$app->mall->id, 's.user_id' => \Yii::$app->user->id])
            ->joinWith([
                'user u' => function ($query) {
                    $query->select(['nickname', 'id', 'username', 'mobile', 'mall_id']);
                }
            ])
            ->leftJoin(['si' => StockUserInfo::tableName()], 's.user_id = si.user_id')
            ->with(['level'])
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
            ->asArray()
            ->one();
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '用户未申请股东'
            ];
        }

        if (!empty($model['level'])) {
            $model['bonus_rate'] = $model['level']['bonus_rate'];
        } else {
            $bonusRate = StockSetting::get(\Yii::$app->mall->id, 'bonus_rate', 0);
            $model['bonus_rate'] = $bonusRate;
        }

        $price = new CommonStock();
        $cashed = $price->getPrice(\Yii::$app->user->id);
        $model['all_bonus'] = price_format($model['all_bonus']);
        $model['cash_bonus'] = price_format($cashed['cash_bonus']);
        $model['total_bonus'] = price_format($model['total_bonus']);

        $model['is_up'] = 0;

        try {
            $level = new LevelForm();
            $form = $level->search();
            if (isset($form['data']['list'])) {
                foreach ($form['data']['list'] as $v) {
                    if ($v['is_up'] == 1) {
                        $model['is_up'] = 1;
                        break;
                    }
                }
            }
        } catch (\Exception $exception) {
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'stock' => $model
            ]
        ];
    }

    public function clearApply()
    {
        $t = \Yii::$app->db->beginTransaction();

        try {
            $stock = StockUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id]);
            if ($stock) {
                $stock->status = CommonStock::STATUS_REAPPLYING;
                $stock->is_delete = 0;
                $stock->applyed_at = '0000-00-00 00:00:00';
                $stock->agreed_at = '0000-00-00 00:00:00';
                $stock->level_id = 0;
                $stock->save();

                $stockInfo = StockUserInfo::findOne(['user_id' => \Yii::$app->user->id]);
                $stockInfo->remark = '';
                $stockInfo->reason = '';
                $stockInfo->save();
                $t->commit();
            }
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => ''
        ];
    }

    public function getInfo()
    {
        $model = StockUserInfo::find()
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        $price = new CommonStock();
        $list = $price->getPrice(\Yii::$app->user->id);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '数据请求成功',
            'data' => [
                'all_bonus' => $model->all_bonus,
                'total_bonus' => $model->total_bonus,
                'out_bonus' => $model->out_bonus,
                'cash_bonus' => price_format($list['cash_bonus'], 2),
                'loading_bonus' => price_format($list['un_pay'], 2),
                'user_instructions' => StockSetting::get(\Yii::$app->mall->id, 'user_instructions', '')
            ]
        ];
    }
}
