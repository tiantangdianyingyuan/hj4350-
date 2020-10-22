<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\step\forms\mall;

use app\models\Model;
use app\core\response\ApiCode;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\models\StepUser;
use app\plugins\step\models\StepLog;

class UserForm extends Model
{
    public $id;
    public $keyword;
    public $type;
    public $currency;
    public $step_id;
    public $ids;

    public function rules()
    {
        return [
            [['id', 'type', 'step_id'], 'integer'],
            [['keyword'], 'string'],
            [['currency'], 'number'],
            [['ids'], 'trim'],
            [['keyword'], 'default', 'value' => ''],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $childQuery = StepUser::find()->select('count(*)')
                ->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ])->andWhere('parent_id = s.id');
        $query = StepUser::find()->alias('s')->select(["s.*","child_num" => $childQuery])->where([
                's.mall_id' => \Yii::$app->mall->id,
                's.is_delete' => 0,
            ])->joinwith(['user u' => function ($query) {
                $query->where([
                        'AND',
                        ['u.mall_id' => \Yii::$app->mall->id],
                        ['u.is_delete' => 0],
                        ['LIKE', 'u.nickname', $this->keyword],
                    ]);
            }]);

        $list = $query->page($pagination)->orderBy('s.id desc')->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getLog()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = StepLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'step_id' => $this->id,
            ]);

        $list = $query->page($pagination)->orderBy('id desc')->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }
    public function invite()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'parent_id' => $this->id,
                'is_delete' => 0,
            ])->with('user');

        $list = $query->page($pagination)->orderBy('id desc')->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'invite_list' => $list,
            ]
        ];
    }

    public function destroyCurrency()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $step = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->all();


            array_walk($step, function($item) {
                if($item->step_currency >= 0) {
                    (new CommonCurrencyModel())->setUser($item)->sub(floatval($item->step_currency),'后台扣除', '');
                }
            });

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        }catch(\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage(),
            ];
        }
    }
    public function batchCurrency()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $step = StepUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->ids,
            ])->all();

            array_walk($step, function($item) {
                if($item->step_currency < $this->currency) {
                    (new CommonCurrencyModel())->setUser($item)->add(floatval($this->currency - $item->step_currency),'后台充值', '');
                }
                if($item->step_currency > $this->currency) {
                    (new CommonCurrencyModel())->setUser($item)->sub(floatval($item->step_currency - $this->currency),'后台扣除', '');
                }
            });
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        }catch(\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage(),
            ];
        }
    }
    public function currency()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->type == 1) {
                (new CommonCurrencyModel())->setUser(CommonStep::getUser($this->step_id))->add(floatval($this->currency),'后台充值', '');
            }
            if($this->type == 2) {
                (new CommonCurrencyModel())->setUser(CommonStep::getUser($this->step_id))->sub(floatval($this->currency),'后台扣除', '');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        }catch(\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
