<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/26
 * Time: 18:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\share;


use app\forms\mall\export\ShareOrderExport;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\ShareOrder;
use app\models\User;
use app\models\UserInfo;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall;
 * @property User $shareUser;
 */
class CommonShareOrder extends Model
{
    public $shareUser; // 分销商用户对象
    public $mall;
    public $userId; // 订单用户ID
    public $parentId; // 分销商ID

    public $page = 1;
    public $limit = 20;
    public $order_no;
    public $nickname;
    public $date_start;
    public $date_end;

    public $fields;
    public $flag;
    public $pagination;
    public $goods_name;
    public $platform;
    public $send_type = -1;

    /**
     * @param $status
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList($status = 0)
    {
        if ($this->shareUser) {
            $this->parentId = $this->shareUser->id;
        }
        $shareOrder = ShareOrder::find()->where(['mall_id' => $this->mall->id, 'is_refund' => 0])
            ->keyword($this->userId, ['user_id' => $this->userId])
            ->keyword($this->parentId, [
                'or',
                ['first_parent_id' => $this->parentId],
                ['second_parent_id' => $this->parentId],
                ['third_parent_id' => $this->parentId],
            ])->select('order_id')->groupBy('order_id');

        $query = Order::find()->with(['detail.share', 'detail.goodsWarehouse', 'refund', 'user.userInfo', 'store'])
            ->where([
                'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $shareOrder, 'is_recycle' => 0
            ])->andWhere(['!=', 'cancel_status', 1])
            ->keyword($status == 1, ['is_pay' => 0])
            ->keyword($status == 2, ['is_pay' => 1, 'is_sale' => 0])
            ->keyword($status == 3, ['is_sale' => 1])
            ->keyword($this->order_no, ['like', 'order_no', $this->order_no])
            ->keyword($this->userId, ['user_id' => $this->userId])
            ->keyword($this->date_start, ['>=', 'created_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'created_at', $this->date_end])
            ->keyword($this->sign, $this->getSignCondition());

        if ($this->nickname) {
            $userId = User::find()->where(['like', 'nickname', $this->nickname])->select('id');
            $query->andWhere(['user_id' => $userId]);
        }

        if ($this->platform) {
            $userIds = UserInfo::find()->andWhere(['platform' => $this->platform])->select('user_id');
            $query->andWhere(['user_id' => $userIds]);
        }

        if ($this->send_type != -1) {
            $query->andWhere(['send_type' => $this->send_type]);
        }

        if ($this->goods_name) {
            $goodsWarehouse = GoodsWarehouse::find()->select('id')
                ->where(['like', 'name', $this->goods_name, 'mall_id' => $this->mall->id]);
            $goods = Goods::find()->select('id')
                ->where(['goods_warehouse_id' => $goodsWarehouse, 'mall_id' => $this->mall->id]);
            $orderDetail = OrderDetail::find()->select('order_id')
                ->where(['goods_id' => $goods]);
            $query->andWhere(['id' => $orderDetail]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new ShareOrderExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->page = $this->page;
            return $exp->export($new_query);
        }

        $orderList = $query->page($this->pagination, $this->limit, $this->page)
            ->orderBy(['created_at' => SORT_DESC])->all();
        return $orderList;
    }

    /**
     * @param Order[] $orderList
     * @return array
     * @throws \Exception
     */
    public function search($orderList)
    {
        $list = [];
        /* @var Order[] $orderList */
        foreach ($orderList as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['store'] = $item->store ? ArrayHelper::toArray($item->store) : [];
            $newItem = array_merge($newItem, [
                'nickname' => $item->user->nickname,
                'avatar' => $item->user->userInfo->avatar,
                'status' => '',
                'first_price' => 0,
                'second_price' => 0,
                'third_price' => 0,
            ]);
            $newItem['platform'] = isset($item->user->userInfo->platform) ? $item->user->userInfo->platform : '';

            // 商品列表
            $goodsList = [];
            foreach ($item->detail as $value) {
                $shareOrder = $value->share;
                if (!$shareOrder) {
                    continue;
                }
                $newItem['first_parent_id'] = $shareOrder->first_parent_id;
                $newItem['second_parent_id'] = $shareOrder->second_parent_id;
                $newItem['third_parent_id'] = $shareOrder->third_parent_id;
                if ($item->is_sale == 1 && $shareOrder->is_transfer == 1 || $item->is_sale == 0 && $shareOrder->is_transfer == 0) {
                    $newItem['first_price'] += floatval($shareOrder->first_price);
                    $newItem['second_price'] += floatval($shareOrder->second_price);
                    $newItem['third_price'] += floatval($shareOrder->third_price);
                }
                $goodsList[] = [
                    'id' => $value->goods->id,
                    'order_detail_id' => $value->id,
                    'num' => $value->num,
                    'name' => $value->goodsWarehouse->name,
                    'cover_pic' => $value->goodsWarehouse->cover_pic,
                    'attr_list' => $value->decodeGoodsInfo($value->goods_info)['attr_list'],
                    'goods' => [
                        'goodsWarehouse' => [
                            'name' => $value->goodsWarehouse->name,
                            'cover_pic' => $value->goodsWarehouse->cover_pic
                        ],
                        'name' => $value->goodsWarehouse->name,
                        'cover_pic' => $value->goodsWarehouse->cover_pic,
                        'refund_status' => $value->refund_status == 1 ? 1 : 0
                    ],
                    'total_price' => $value->total_price,
                    'total_original_price' => $value->total_original_price,
                    'is_refund' => $value->is_refund,
                    'refund_status_text' => $value->refundStatusText,
                    'goods_info' => \Yii::$app->serializer->decode($value->goods_info)
                ];
            }
            $newItem['first_price'] = price_format($newItem['first_price']);
            $newItem['second_price'] = price_format($newItem['second_price']);
            $newItem['third_price'] = price_format($newItem['third_price']);
            $newItem['detail'] = $goodsList;
            $newItem['plugin_name'] = $item->signName;

            // 订单状态
            if ($item->is_pay == 0) {
                $newItem['status'] = '未付款';
                if ($item->cancel_status == 1) {
                    $newItem['status'] = '已取消';
                }
            } elseif ($item->is_pay == 1 && $item->is_sale == 0) {
                if ($item->cancel_status == 2) {
                    $newItem['status'] = '申请取消';
                }
                if ($item->cancel_status == 1) {
                    $newItem['status'] = '已取消';
                }
                $newItem['status'] = '已付款';
            } elseif ($item->is_sale == 1) {
                $newItem['status'] = '已完成';
            } else {
                $newItem['status'] = '未知错误';
            }

            $list[] = $newItem;
        }
        return $list;
    }

    public function getSignCondition()
    {
        if (!in_array($this->sign, ['all', 'mall', ''])) {
            $signCondition = ['sign' => $this->sign];
        } elseif ($this->sign == 'mch') {
            $signCondition = ['>', 'mch_id', 0];
        } elseif ($this->sign == 'mall') {
            $signCondition = ['sign' => ''];
        } else {
            $signCondition = [];
        }
        return $signCondition;
    }
}
