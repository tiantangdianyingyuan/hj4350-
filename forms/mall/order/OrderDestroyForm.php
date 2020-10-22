<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\Model;

class OrderDestroyForm extends Model
{
    public $order_id;
    public $is_recycle;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'is_recycle'], 'integer'],
        ];
    }

    //删除订单
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $order = Order::findOne([
                'id' => $this->order_id,
                'is_recycle' => 1,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$order) {
                throw new \Exception('订单不存在，请刷新后重试');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            $order->is_delete = 1;
            $order->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 移入移出回收站
    public function recycle()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::findOne([
            'id' => $this->order_id,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }

        $order->is_recycle = $this->is_recycle;
        $order->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功'
        ];
    }

    //清空回收站
    public function destroyAll()
    {
        $where = [
            'is_recycle' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ];
        if ($this->sign) {
            $where['sign'] = $this->sign;
        }
        $count = Order::updateAll(
            ['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')],
            $where
        );
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "已清空，共删除{$count}个订单"
        ];
    }
}
