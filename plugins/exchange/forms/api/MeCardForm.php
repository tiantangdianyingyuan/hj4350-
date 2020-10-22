<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeOrder;

class MeCardForm extends Model
{
    public $code;

    public function rules()
    {
        return [
            [['code'], 'string'],
        ];
    }

    private function getStatus(ExchangeOrder $Eorder): string
    {
        if (in_array($Eorder->code->status, [2, 3])) {
            $status = 'used';
        } elseif (
            $Eorder->library->is_delete == 1
            || ($Eorder->library->expire_type !== 'all' && $Eorder->code->valid_end_time <= Date('Y-m-d H:i:s'))
            //|| ($Eorder->library->expire_type !== 'fixed' && $Eorder->code->valid_start_time >= Date('Y-m-d H:i:s'))
            || $Eorder->library->is_recycle == 1
            || $Eorder->code->status = 0
        ) {
            $status = 'invalid';
        } else {
            $status = 'unused';
        }
        return $status;
    }

    public function list()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = ExchangeOrder::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->with(['order', 'library', 'code'])
                ->orderBy(['created_at' => SORT_DESC])
                ->page($pagination)
                ->all();

            $list = array_map(function (ExchangeOrder $Eorder) {
                $goodsInfo = \yii\helpers\BaseJson::decode(current($Eorder->order->detail)->goods_info);
                return [
                    'id' => $Eorder->id,
                    'name' => $goodsInfo['goods_attr']['name'],
                    'created_at' => $Eorder->created_at,
                    'cover_pic' => $goodsInfo['goods_attr']['cover_pic'],
                    'status' => $this->getStatus($Eorder),
                    'code' => $Eorder->code->code,
                    'recycle_at' => $Eorder->code->r_raffled_at,
                ];
            }, $list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
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
            $Eorder = ExchangeOrder::find()->alias('e')->where([
                'e.mall_id' => \Yii::$app->mall->id,
                'e.is_delete' => 0,
                'c.code' => $this->code,
            ])->joinWith('code c')
                ->with(['order', 'library'])
                ->one();
            if (empty($Eorder)) {
                throw new \Exception('数据不存在');
            }

            $goodsInfo = \yii\helpers\BaseJson::decode(current($Eorder->order->detail)->goods_info);
            $goodsInfo['goods_attr']['pic_list'] = \yii\helpers\BaseJson::decode($goodsInfo['goods_attr']['pic_list']);
            $data = [
                'id' => $Eorder->id,
                'goods_attr' => $goodsInfo['goods_attr'],
                'created_at' => $Eorder->created_at,
                'status' => $this->getStatus($Eorder),
                'code' => $Eorder->code->code,
            ];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data,
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
