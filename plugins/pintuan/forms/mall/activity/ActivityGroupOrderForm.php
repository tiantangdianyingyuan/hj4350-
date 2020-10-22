<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Model;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;
use yii\db\ActiveQuery;

class ActivityGroupOrderForm extends Model
{
    public $id;
    public $keyword;
    public $keyword_name;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['keyword', 'keyword_name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '拼团组ID'
        ];
    }

    // 拼团活动详情
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var PintuanOrders $ptOrder */
            $ptOrder = PintuanOrders::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
            ])->one();

            if (!$ptOrder) {
                throw new \Exception('拼团组不存在');
            }

            // 机器人数
            $robotNum = PintuanOrderRelation::find()
                ->where(['is_delete' => 0, 'pintuan_order_id' => $this->id])
                ->andWhere(['>', 'robot_id', 0])
                ->count();

            $newGroup['id'] = $ptOrder->id;
            $newGroup['preferential_price'] = $ptOrder->preferential_price;
            $newGroup['people_num'] = $ptOrder->people_num;
            $newGroup['robot_num'] = $robotNum;
            $newGroup['status'] = $ptOrder->status;
            $newGroup['status_cn'] = $ptOrder->getStatusText($ptOrder);

            $orderIds = Order::find()->where([
                'or',
                ['is_pay' => 1],
                ['pay_type' => 2],
            ])
                ->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0
                ])
                ->select('id');
            $query = PintuanOrderRelation::find()->where([
                'order_id' => $orderIds,
                'pintuan_order_id' => $this->id,
                'is_delete' => 0,
                'robot_id' => 0,
                'cancel_status' => 0,// 去除超出拼团总人数的订单
            ]);
            /** @var BaseActiveQuery $query */
            $query = $this->setKeyword($query);

            $list = $query->with('order', 'user.userInfo')
                ->orderBy(['created_at' => SORT_ASC])
                ->page($pagination)
                ->all();

            $newList = [];
            /** @var PintuanOrderRelation $item */
            foreach ($list as $item) {
                $newItem = [];
                $newItem['id'] = $item->id;
                $newItem['nickname'] = $item->user->nickname;
                $newItem['avatar'] = $item->user->userInfo->avatar;
                $newItem['is_parent'] = $item->is_parent;
                $newItem['total_pay_price'] = $item->order->total_pay_price;
                $newItem['express_price'] = $item->order->express_price;
                $newItem['order_no'] = $item->order->order_no;
                $newItem['order_id'] = $item->order->id;
                $newItem['pintuan_order_id'] = $item->pintuan_order_id;

                /** @var PaymentOrder $paymentOrder */
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                $newItem['is_show_refund'] = !$paymentRefund && $item->pintuanOrder->status == 4 ? 1 : 0;
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'group' => $newGroup,
                    'search_list' => $this->getSearchList(),
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    /**
     * 商品名称搜索
     * @param ActiveQuery $query
     * @return mixed
     */
    private function setKeyword($query)
    {
        if ($this->keyword && $this->keyword_name) {
            switch ($this->keyword_name) {
                case 'order_no':
                    $orderIds = Order::find()
                        ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'sign' => (new Plugin())->getName()])
                        ->andWhere(['like', 'order_no', $this->keyword])
                        ->select('id');
                    $query->andWhere(['order_id' => $orderIds]);
                    break;
                case 'nickname':
                    $userIds = User::find()
                        ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'nickname', $this->keyword])
                        ->select('id');
                    $query->andWhere(['user_id' => $userIds]);
                    break;
                case 'user_id':
                    $query->andWhere(['user_id' => $this->keyword]);
                    break;
            }
        }

        return $query;
    }

    private function getSearchList()
    {
        return [
            [
                'label' => '订单号',
                'value' => 'order_no'
            ],
            [
                'label' => '用户昵称',
                'value' => 'nickname'
            ],
            [
                'label' => '用户ID',
                'value' => 'user_id'
            ],
        ];
    }
}