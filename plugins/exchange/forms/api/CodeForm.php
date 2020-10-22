<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\models\ExchangeCode;

class CodeForm extends Model
{
    public $code;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
        ];
    }

    public function log()
    {
        try {
            $query = ExchangeCode::find()->where([
                'AND',
                ['in', 'status', [2, 3]],
                ['mall_id' => \Yii::$app->mall->id],
                ['r_user_id' => \Yii::$app->user->id]
            ]);

            $codeModel = $query->orderBy(['r_raffled_at' => SORT_DESC])
                ->with('library')
                ->page($pagination)
                ->all();

            $newData = [];
            foreach ($codeModel as $item) {
                $item->status == 3 && $last_num = 0;
                if ($item->status == 2) {
                    $mode = intval($item->library->mode);
                    $reward = \yii\helpers\BaseJson::decode($item->r_rewards);
                    $send_num = 0;
                    foreach ($reward as $j) {
                        $j['is_send'] == 1 && $send_num++;
                    }
                    $last_num = $mode > 0 ? $mode - $send_num : count($reward) - $send_num;
                }

                $newData[] = [
                    'code' => $item->code,
                    'r_raffled_at' => $item->r_raffled_at,
                    'last_num' => $last_num,
                    'status' => $item->status,
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newData,
                    'pagination' => $pagination,
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $codeModel = ExchangeCode::find()->where([
                'AND',
                ['code' => $this->code],
                ['in', 'status', [2, 3]],
                ['r_user_id' => \Yii::$app->user->id]
            ])->one();
            if (!$codeModel) {
                throw new \Exception('数据不存在');
            }
            $rewards = CommonModel::getFormatRewards($codeModel->r_rewards);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'codeModel' => $codeModel,
                    'rewards' => $rewards,
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }
}
