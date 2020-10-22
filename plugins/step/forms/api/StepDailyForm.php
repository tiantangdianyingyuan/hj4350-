<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\forms\common\CommonSport;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepDaily;
use app\plugins\step\models\StepUser;
use app\plugins\wxapp\Plugin;

class StepDailyForm extends Model
{
    public $num;

    public $encrypted_data;
    public $iv;
    public $code;

    public function rules()
    {
        return [
            [['code', 'encrypted_data', 'iv'], 'string'],
            [['num'], 'required'],
            [['num',], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $real_num = $this->num;
            if ($real_num > (new CommonSport())->getSportClass($this->attributes)) {
                throw new \Exception('步数异常');
            }
            $stepUser = CommonStep::getUser();
            if (!$stepUser) {
                throw new \Exception('用户不存在');
            }

            $invite = StepUser::find()->select("SUM(invite_ratio) as ratio")->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['parent_id' => $stepUser->id],
                ['>','created_at',date("Y-m-d")]
            ])->one();

            $ratio = $stepUser->ratio;
            if ($invite) {
                $ratio = $ratio - $invite->ratio;
            };
            //实际概率
            $new_ratio = $ratio / 1000;

            $num = floor($real_num + $real_num * $new_ratio);

            $log = StepDaily::find()->select("SUM(num) as num")->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['>','created_at',date("Y-m-d")],
                ['step_id' => $stepUser->id]
            ])->one();

            $log['num'] = $log['num'] ?: 0;
            $num = $num - $log['num'];

            $setting = CommonStep::getSetting();

            $convert_max = $setting['convert_max'];
            $convert_ratio = $setting['convert_ratio'];
            if ($convert_ratio <= 0) {
                throw new \Exception('无法兑换');
            }

            if ($convert_max && $num >= $convert_max - $log['num']) {
                $num = $convert_max - $log['num'];
            };

            //兑换额
            $new_currency = floor($num / $convert_ratio * 100) / 100;
            if ($new_currency < 0.01) {
                throw new \Exception('步数不足');
            }

            //日志
            $t = \Yii::$app->db->beginTransaction();
            $model = new StepDaily();
            $model->mall_id = \Yii::$app->mall->id;
            $model->step_id = $stepUser->id;
            $model->real_num = $real_num;
            $model->num = $num;
            $model->ratio = $ratio;
            if ($model->save()) {
                (new CommonCurrencyModel())->setUser()->add($new_currency, '步数兑换', '每日兑换ID'. $model->id);
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'list' => [
                        'num' => $num,
                        'convert' => $new_currency,
                    ]
                ];
            } else {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($model));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
