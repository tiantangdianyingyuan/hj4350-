<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderComments;
use app\models\OrderDetail;

class OrderAppraiseForm extends Model
{
    public $appraiseData;
    public $order_id;

    public function rules()
    {
        return [
            [['order_id', 'appraiseData'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    public function appraise()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->appraiseData = \Yii::$app->serializer->decode($this->appraiseData);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->appraiseData && !is_array($this->appraiseData)) {
                throw new \Exception('数据异常');
            }

            $order = Order::findOne($this->order_id);
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            $isComment = (new Mall())->getMallSettingOne('is_comment');
            if (!$isComment) {
                throw new \Exception('商城订单评价功能关闭,提交失败');
            }

            $order->is_comment = 1;
            $order->comment_time = mysql_timestamp();
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            foreach ($this->appraiseData as $appraiseDatum) {
                /** @var OrderDetail $orderDetail */
                $orderDetail = OrderDetail::find()->where([
                    'id' => $appraiseDatum['id'],
                ])->one();

                if (!$orderDetail) {
                    throw new \Exception('订单详情不存在');
                }

                $orderComments = OrderComments::find()->where(['order_detail_id' => $orderDetail->id])->one();
                if ($orderComments) {
                    throw new \Exception('订单已评价,无需再次评价');
                } else {
                    $orderComments = new OrderComments();
                }

                $orderComments->mall_id = \Yii::$app->mall->id;
                $orderComments->mch_id = $order->mch_id;
                $orderComments->order_detail_id = $orderDetail->id;
                $orderComments->order_id = $orderDetail->order_id;
                $orderComments->sign = $orderDetail->order->sign;
                $orderComments->user_id = \Yii::$app->user->id;
                $orderComments->score = $appraiseDatum['grade_level'];
                $orderComments->content = $appraiseDatum['content'] ? $appraiseDatum['content'] : '此用户没有填写评价';
                $orderComments->pic_url = \Yii::$app->serializer->encode($appraiseDatum['pic_list']);
                $orderComments->goods_id = $orderDetail->goods_id;
                $orderComments->goods_warehouse_id = $orderDetail->goods->goods_warehouse_id;
                $orderComments->goods_info = $orderDetail->goods_info;
                $orderComments->reply_content = '';
                $orderComments->is_anonymous = $appraiseDatum['is_anonymous'] ? 1 : 0;
                $res = $orderComments->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($orderComments));
                }
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '评价成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }
}
