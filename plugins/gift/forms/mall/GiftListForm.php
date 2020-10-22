<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\mall;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Order;
use app\models\User;
use app\models\UserInfo;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;
use app\plugins\gift\models\GiftUserOrder;
use yii\db\Exception;

class GiftListForm extends Model
{
    public $gift_id;
    public $user_order_id;
    public $page;

    public $start_date;
    public $end_date;
    public $type;//玩法
    public $platform;
    public $keyword;
    public $keyword_1;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['gift_id', 'user_order_id', 'page'], 'integer'],
            [['start_date', 'end_date', 'type', 'platform', 'keyword', 'keyword_1'], 'string'],
        ];
    }

    //送出的
    public function getSendList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftLog::find()->alias('gl')
                ->andWhere([
                    'gl.is_delete' => 0,
                    'gl.mall_id' => \Yii::$app->mall->id,
                    'gl.is_pay' => 1
                ])->with('winUser.user.userInfo')
                ->with('userOrder')
                ->with('giftOrderNum')
                ->with('user.userInfo')
                ->with('sendOrder.detail.goods.goodsWarehouse');
            if ($this->start_date) {
                $model->andWhere(['>', 'gl.created_at', $this->start_date]);
            }
            if ($this->end_date) {
                $model->andWhere(['<', 'gl.created_at', $this->end_date]);
            }
            if ($this->type) {
                $model->andWhere(['gl.type' => $this->type]);
            }
            if ($this->platform) {
                $model->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = gl.user_id')
                    ->andWhere(['ui.platform' => $this->platform]);
            }
            if ($this->keyword && $this->keyword_1) {
                switch ($this->keyword) {
                    case 'order_no':
                        $model->leftJoin(['so' => GiftSendOrder::tableName()], 'so.gift_id = gl.id')
                            ->andWhere(['like', 'so.order_no', $this->keyword_1]);
                        break;
                    case 'name':
                        $model->leftJoin(['so' => GiftSendOrder::tableName()], 'so.gift_id = gl.id')
                            ->leftJoin(['d' => GiftSendOrderDetail::tableName()], 'd.send_order_id = so.id')
                            ->leftJoin(['g' => Goods::tableName()], 'g.id = d.goods_id')
                            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
                            ->andWhere(['like', 'gw.name', $this->keyword_1]);
                        break;
                    case 'nickname':
                        $model->leftJoin(['u' => User::tableName()], 'u.id = gl.user_id')
                            ->andWhere(['like', 'u.nickname', $this->keyword_1]);
                        break;
                    case 'user_id':
                        $model->andWhere(['gl.user_id' => $this->keyword_1]);
                        break;
                }
            }
            $list = $model->page($pagination)->orderBy('gl.created_at desc')
                ->asArray()
                ->all();

            //状态
            foreach ($list as &$item) {
                $item['status'] = '';
                switch ($item['type']) {
                    case 'direct_open':
                        //一人拿所有奖品
                        if ($item['open_type'] == 0) {
                            if (count($item['winUser']) > 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '已完成';
                            } elseif (strtotime($item['auto_refund_time']) > time()) {
                                $item['status'] = '等待领取';
                            } elseif (count($item['winUser']) == 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '领取失败';
                            }
                        } else {
                            if (count($item['winUser']) > 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '已完成';
                            } elseif (count($item['winUser']) <= $item['num'] && strtotime($item['auto_refund_time']) > time()) {
                                $item['status'] = '等待领取';
                            } elseif (count($item['winUser']) == 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '领取失败';
                            }
                        }
                        break;
                    case 'time_open':
                        if (count($item['winUser']) > 0 && strtotime($item['open_time']) < time()) {
                            $item['status'] = '已完成';
                        } elseif (strtotime($item['open_time']) > time()) {
                            $item['status'] = '等待开奖';
                        } elseif (count($item['winUser']) == 0 && strtotime($item['open_time']) < time()) {
                            $item['status'] = '开奖失败';
                        }
                        break;
                    case 'num_open':
                        if (count($item['winUser']) >= $item['open_num'] && strtotime($item['auto_refund_time']) < time()) {
                            $item['status'] = '已完成';
                        } elseif (count($item['winUser']) < $item['open_num'] && strtotime($item['auto_refund_time']) > time()) {
                            $item['status'] = '等待开奖';
                        } elseif (count($item['winUser']) < $item['open_num'] && strtotime($item['auto_refund_time']) < time()) {
                            $item['status'] = '开奖失败';
                        }
                        break;
                }

                //判断礼包商品数量
                $item['is_big_gift'] = 0;
                if (@count($item['sendOrder']) > 1 || @count($item['sendOrder'][0]['detail']) > 1) {
                    $item['is_big_gift'] = 1;
                }
                $item['join_num'] = count($item['userOrder']);
                $item['win_num'] = 0;
                foreach ($item['giftOrderNum'] as $a) {
                    $item['win_num'] += $a['num'];
                }
                $item['address_status'] = (
                    (($item['join_num'] > 0) ? $item['win_num'] : 0
                    ) == 0) ? '未填写' :
                    (
                    ($item['win_num'] == $item['num']) ? '已填写' : '部分填写'
                    );

                $item['total_pay_price'] = 0;
                foreach ($item['sendOrder'] as $value) {
                    $item['total_pay_price'] += $value['total_pay_price'];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

//    public function getSendDetail()
//    {
//        if (!$this->validate()) {
//            return $this->getErrorResponse($this);
//        }
//        try {
//            $detail = GiftLog::find()->andWhere([
//                'is_delete' => 0,
//                'id' => $this->gift_id,
//                'mall_id' => \Yii::$app->mall->id,
//            ])->with('winUser.giftOrder')
//                ->with('sendOrder.detail.goods.goodsWarehouse')
//                ->asArray()
//                ->one();
//
//            return [
//                'code' => ApiCode::CODE_SUCCESS,
//                'data' => [
//                    'detail' => $this->dataReturn($detail),
//                ]
//            ];
//        } catch (\Exception $exception) {
//            return [
//                'code' => ApiCode::CODE_ERROR,
//                'msg' => $exception->getMessage()
//            ];
//        }
//
//    }

    //领取记录
    public function getRecordList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftUserOrder::find()->alias('guo')
                ->andWhere([
                    'guo.mall_id' => \Yii::$app->mall->id,
                    'guo.is_delete' => 0,
                    'guo.is_turn' => 0,
                    'guo.is_win' => 1,
                ])->with('user.userInfo')
                ->with('giftLog.user.userInfo')
                ->with('giftLog.sendOrder.detail.goods.goodsWarehouse')
                ->with('giftOrder.order.store')
                ->with('giftOrder.order.detail.goods.goodsWarehouse')
                ->with('giftOrder.goods.goodsWarehouse')
                ->with('giftOrder.goodsAttr')
                ->with(['parent.user.userInfo']);
            if ($this->gift_id) {
                $model->andWhere(['guo.gift_id' => $this->gift_id]);
            }
            if ($this->start_date) {
                $model->andWhere(['>', 'guo.created_at', $this->start_date]);
            }
            if ($this->end_date) {
                $model->andWhere(['<', 'guo.created_at', $this->end_date]);
            }
            if ($this->keyword && $this->keyword_1) {
                switch ($this->keyword) {
                    case 'user_id':
                        $model->leftJoin(['u' => User::tableName()], 'u.id = guo.user_id')
                            ->andWhere(['like', 'u.id', $this->keyword_1]);
                        break;
                    case 'name':
                        $model->leftJoin(['so' => GiftSendOrder::tableName()], 'so.gift_id = guo.gift_id')
                            ->leftJoin(['d' => GiftSendOrderDetail::tableName()], 'd.send_order_id = so.id')
                            ->leftJoin(['g' => Goods::tableName()], 'g.id = d.goods_id')
                            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
                            ->andWhere(['like', 'gw.name', $this->keyword_1]);
                        break;
                    case 'nickname':
                        $model->leftJoin(['u' => User::tableName()], 'u.id = guo.user_id')
                            ->andWhere(['like', 'u.nickname', $this->keyword_1]);
                        break;
                    case 'gift_order_no':
                        $model->leftJoin(['go' => GiftOrder::tableName()], 'go.user_order_id = guo.id')
                            ->leftJoin(['o' => Order::tableName()], 'o.id = go.order_id')
                            ->andWhere(['like', 'o.order_no', $this->keyword_1]);
                        break;
                }
            }
            $list = $model->page($pagination)->orderBy('guo.created_at desc')
                ->asArray()->all();

            foreach ($list as &$item) {
                if ($item['giftOrder'][0]['order_id'] > 0) {
                    $item['address_status'] = '已填写';
                } else {
                    $item['address_status'] = '未填写';
                }
                $turn_count = GiftUserOrder::find()->andWhere(['token' => $item['token']])->count();
                $item['turn_list'] = [
                    'gift_user' => [
                        'nickname' => $item['giftLog']['user']['nickname'],
                        'avatar' => $item['giftLog']['user']['userInfo']['avatar'],
                        'platform' => $item['giftLog']['user']['userInfo']['platform'],
                    ],
                    'turn_num' => $turn_count - 2 > 0 ? $turn_count - 2 : 0,
                    'parent_user' => null,
                    'self_user' => [
                        'nickname' => $item['user']['nickname'],
                        'avatar' => $item['user']['userInfo']['avatar'],
                        'platform' => $item['user']['userInfo']['platform'],
                    ]
                ];
                if (!empty($item['parent'])) {
                    $item['turn_list']['parent_user'] = [
                        'nickname' => $item['parent']['user']['nickname'],
                        'avatar' => $item['parent']['user']['userInfo']['avatar'],
                        'platform' => $item['parent']['user']['userInfo']['platform'],
                    ];
                }

                //统一格式
                $item = (new \app\plugins\gift\forms\api\GiftListForm())->data($item);

            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}