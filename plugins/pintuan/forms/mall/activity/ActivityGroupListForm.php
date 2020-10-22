<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\User;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;

class ActivityGroupListForm extends Model
{
    public $search;
    public $id;

    public function rules()
    {
        return [
            [['search'], 'string'],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '活动ID',
        ];
    }

    // 当个活动的活动数据列表 | 全部活动数据列表
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $this->search = [];
        }

        $query = PintuanOrders::find()->andWhere(['mall_id' => \Yii::$app->mall->id,])->andWhere(['!=', 'status', 0]);

        $newGoods = [];
        if ($this->id) {
            /** @var PintuanGoods $pintuanGoods */
            $pintuanGoods = PintuanGoods::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $this->id,
                'is_delete' => 0,
                'pintuan_goods_id' => 0
            ])
                ->with('goods')
                ->one();

            if (!$pintuanGoods) {
                throw new \Exception('拼团活动不存在');
            }

            $groupGoods = PintuanGoods::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'pintuan_goods_id' => $pintuanGoods->id
            ])->all();

            $goodsIds[] = $pintuanGoods->goods_id;
            /** @var PintuanGoods $item */
            foreach ($groupGoods as $item) {
                $goodsIds[] = $item->goods_id;
            }

            $query->andWhere(['goods_id' => $goodsIds]);


            $newGoods['name'] = $pintuanGoods->goods->name;
            $newGoods['cover_pic'] = $pintuanGoods->goods->coverPic;
        }

        $query = $this->setKeyword($query);
        /** @var BaseActiveQuery $query */
        $query = $this->setDate($query);

        if (isset($this->search['status'])) {
            // 0.待付款|1.拼团中|2.拼团成功|3.拼团失败|4.未退款
            switch ($this->search['status']) {
                // 拼团中
                case 1:
                    $query->andWhere(['status' => 1]);
                    break;
                // 拼团成功
                case 2:
                    $query->andWhere(['status' => 2]);
                    break;
                // 拼团失败
                case 3:
                    $query->andWhere([
                        'or',
                        ['status' => 3],
                        ['status' => 4],
                    ]);
                    break;
                // 全部
                default:
                    break;
            }
        }

        $list = $query->with('goods', 'orderRelation.user')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var PintuanOrders $item */
        foreach ($list as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['people_num'] = $item->people_num;
            $newItem['preferential_price'] = $item->preferential_price;
            $newItem['goods_name'] = $item->goods->getName();
            $newItem['goods_cover_pic'] = $item->goods->getCoverPic();
            $robotNum = 0;
            /** @var PintuanOrderRelation $orItem */
            foreach ($item->orderRelation as $orItem) {
                if ($orItem->is_groups == 1 && $orItem->is_parent == 1) {
                    $newItem['group_nickname'] = $orItem->user->nickname;
                    $newItem['group_create_time'] = $orItem->order->pay_time;
                    $newItem['platform'] = $orItem->user->userInfo->platform;
                }
                if ($orItem->robot_id > 0) {
                    $robotNum += 1;
                }
            }
            $newItem['robot_num'] = $robotNum;
            $newItem['status'] = $item->status;
            $newItem['status_cn'] = $item->getStatusText($item);
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'goods' => $newGoods,
                'pagination' => $pagination,
                'search_list' => $this->getSearchList()
            ]
        ];
    }

    /**
     * 商品名称搜索
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setKeyword($query)
    {
        $search = $this->search;
        if (isset($search['keyword']) && $search['keyword'] && isset($search['keyword_name']) && $search['keyword_name']) {
            switch ($search['keyword_name']) {
                case 'goods_name':
                    $goodsWarehouseIds = GoodsWarehouse::find()->andWhere([
                        'mall_id' => \Yii::$app->mall->id,
                    ])->andWhere(['like', 'name', $search['keyword']])->select('id');

                    $goodsIds = Goods::find()->andWhere([
                        'mall_id' => \Yii::$app->mall->id,
                        'goods_warehouse_id' => $goodsWarehouseIds,
                        'sign' => (new Plugin())->getName()
                    ])->select('id');
                    $query->andWhere(['goods_id' => $goodsIds]);
                    break;
                case 'order_no':
                    $orderIds = Order::find()
                        ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'sign' => (new Plugin())->getName()])
                        ->andWhere(['like', 'order_no', $search['keyword']])
                        ->select('id');
                    $pintuanOrderIds = PintuanOrderRelation::find()->andWhere(['order_id' => $orderIds])->select('pintuan_order_id');
                    $query->andWhere(['id' => $pintuanOrderIds]);
                    break;
                case 'nickname':
                    $userIds = User::find()
                        ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'nickname', $search['keyword'] ])
                        ->select('id');
                    $pintuanOrderIds = PintuanOrderRelation::find()->andWhere(['user_id' => $userIds])->select('pintuan_order_id');
                    $query->andWhere(['id' => $pintuanOrderIds]);
                    break;
                case 'user_id':
                    $pintuanOrderIds = PintuanOrderRelation::find()->andWhere(['like', 'user_id', $search['keyword']])->select('pintuan_order_id');
                    $query->andWhere(['id' => $pintuanOrderIds]);
                    break;
            }
        }

        return $query;
    }

    /**
     * 日期搜索
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setDate($query)
    {
        // 日期搜索
        $search = $this->search;
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $orderIds = Order::find()
                ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => \Yii::$app->plugin->currentPlugin->getName()])
                ->andWhere(['>=', 'pay_time', $search['date_start']])
                ->andWhere(['<=', 'pay_time', $search['date_end']])
                ->select('id');

            $pintuanOrderIds = PintuanOrderRelation::find()
                ->andWhere(['order_id' => $orderIds, 'is_delete' => 0])
                ->select('pintuan_order_id');

            $query->andWhere(['id' => $pintuanOrderIds]);
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
                'label' => '商品名称',
                'value' => 'goods_name'
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