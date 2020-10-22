<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\advance\jobs\AdvanceAutoCancelJob;
use app\plugins\advance\jobs\AdvanceOrderSubmitJob;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;

class AdvanceOrderForm extends Model
{
    public $id;
    public $goods_id;
    public $goods_attr_id;
    public $goods_num;


    public function rules()
    {
        return [
            [['goods_id', 'goods_attr_id', 'goods_num'], 'required'],
            [['goods_id', 'goods_attr_id', 'goods_num', 'id'], 'integer'],
        ];
    }

    //下定金
    public function advance()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $user_info = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        if ($user_info->is_blacklist == 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '下单被限制，请联系管理员'
            ];
        }

        if ($this->id) {
            $order = AdvanceOrder::findOne([
                'id' => $this->id,
                'is_cancel' => 0,
                'is_refund' => 0,
                'is_delete' => 0,
                'is_pay' => 0,
                'is_recycle' => 0,
            ]);
            if (empty($order)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '订单数据异常,无法支付'
                ];
            }
            //过定金时间不让付定金
            $goods_info = AdvanceGoods::findOne(['goods_id' => $order->goods_id]);
            if (empty($goods_info)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '商品不存在'
                ];
            }
            if (strtotime($goods_info->end_prepayment_at) < time()) {
                $queueId = \Yii::$app->queue->delay(0)->push(new AdvanceAutoCancelJob(['id' => $this->id]));
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '该商品已过付定金阶段',
//                    'data' => [
//                        'queue_id' => $queueId
//                    ]
                ];
            }
            return (new AdvanceOrderPayForm())->getReturnData($order);
        } else {
            $token = \Yii::$app->security->generateRandomString();
            $dataArr = [
                'mall' => \Yii::$app->mall,
                'user' => \Yii::$app->user->identity,
                'goods_id' => $this->goods_id,
                'goods_attr_id' => $this->goods_attr_id,
                'goods_num' => $this->goods_num,
                'token' => $token,
                'appVersion' => \Yii::$app->appVersion,
            ];
            $class = new AdvanceOrderSubmitJob($dataArr);
            $queueId = \Yii::$app->queue->delay(0)->push($class);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'token' => $token,
                    'queue_id' => $queueId
                ]
            ];
        }
    }
}
