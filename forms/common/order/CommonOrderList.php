<?php

namespace app\forms\common\order;

use app\core\Pagination;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;

/**
 * @property Order $order
 * @property BaseActiveQuery $query
 * @property Pagination $pagination
 */
class CommonOrderList extends Model
{
    public $query;
    public $pagination;
    public $is_pagination;
    public $goods;

    public $mall_id;
    public $user_id;
    public $sign_id;
    public $all_mch;
    public $mch_id;
    public $limit;
    public $page;
    public $keyword;
    public $is_array;
    public $sort;
    public $status;
    public $is_cancel_status = 1;
    public $add_where = [];
    public $is_recycle;
    public $dateArr = [];
    /**
     * 关联关系
     * @var
     */
    public $is_detail;
    public $is_mch_order;
    public $is_user;
    public $is_goods;
    public $is_comment;
    public $is_refund;
    public $relations = [];

    public function rules()
    {
        return [
            [['sign_id', 'keyword'], 'string'],
            [['mall_id', 'limit', 'mch_id', 'is_detail', 'is_refund', 'is_array', 'limit', 'sort',
                'is_pagination', 'is_mch_order', 'is_user', 'is_goods', 'all_mch', 'status',
                'is_comment', 'user_id', 'is_recycle'], 'integer'],
            [['limit'], 'default', 'value' => 10],
            [['is_array', 'status'], 'default', 'value' => 0],
            [['page', 'is_pagination', 'sort'], 'default', 'value' => 1],
            [['add_where'], 'safe'],
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    private function getMethod($key)
    {
        $array = [
            'keyword' => 'setKeyword',
            'sort' => 'setSortWhere',
            'all_mch' => 'setAllMch',
            'mch_id' => 'setMchId',
            'user_id' => 'setUserId',
            'is_detail' => 'setWithDetail',
            'is_refund' => 'setWithRefund',
            'is_mch_order' => 'setWithMchOrder',
            'is_goods' => 'setWithGoods',
            'is_user' => 'setWithUser',
            'is_cancel_status' => 'setCancelStatus',
            'status' => 'setStatus',
            'is_comment' => 'setWithComment',
            'sign_id' => 'setSignId',
            'add_where' => 'setAddWhere',
            'is_recycle' => 'setIsRecycle',
            'dateArr' => 'setDate',
            'relations' => 'setRelations',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    public function getQuery()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $this->query = $query = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
        ]);
        foreach ($this->attributes as $key => $value) {
            $method = $this->getMethod($key);
            if ($method && method_exists($this, $method) && $value !== null && $value !== false) {
                $this->$method();
            }
        }

        if ($this->is_pagination) {
            $this->query->page($this->pagination, $this->limit, $this->page);
        }

        return $this->query;
    }

    //持续改进
    public function search()
    {
        try {
            $this->getQuery();
            $list = $this->query->asArray($this->is_array)->groupBy('o.id')->all();

            foreach ($list as &$item) {
                if ($item['order_form']) {
                    $item['order_form'] = \Yii::$app->serializer->decode($item['order_form']);
                }
            }

            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function setKeyword()
    {
        if ($this->keyword) {
            $this->query->andWhere([
                'or',
                ['LIKE', 'o.order_no', $this->keyword],
                ['LIKE', 'o.name', $this->keyword],
                ['LIKE', 'o.mobile', $this->keyword],
                ['exists', (OrderDetail::find()->alias('od')
                        ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                            $query1->where(['like', 'gw.name', $this->keyword]);
                        }])->where("o.id = od.order_id"))],
            ]);
        }
    }

    private function setAllMch()
    {
        $this->query->andWhere(['>', 'o.mch_id', 0]);
    }

    private function setMchId()
    {
        $this->query->andWhere(['o.mch_id' => $this->mch_id]);
    }

    private function setUserId()
    {
        $this->query->andWhere(['o.user_id' => $this->user_id]);
    }

    private function setCancelStatus()
    {
        $this->query->andWhere(['<>', 'o.cancel_status', 1]);
    }

    private function setSignId()
    {
        $this->query->andWhere(['o.sign' => $this->sign_id]);
    }

    private function setStatus()
    {
        switch ($this->status) {
            case 0:
                break;
            // 待付款
            case 1:
                // TODO 货到付款订单除外
                $this->query->andWhere(['o.is_pay' => 0])->andWhere(['!=', 'o.pay_type', 2]);
                break;
            // 待发货
            case 2:
                $this->query->andWhere(['o.is_send' => 0])->andWhere([
                    'or',
                    ['o.pay_type' => 2],
                    ['o.is_pay' => 1],
                ])->andWhere([
                    'or',
                    [
                        'and',
                        ['o.sign' => 'gift',],
                        ['!=', 'o.auto_cancel_time', ''],
                    ], ['not in', 'o.sign', ['gift']],
                ]);
                break;
            // 待收货
            case 3:
                $this->query->andWhere(['o.is_send' => 1, 'o.is_confirm' => 0]);
                break;
            // 已完成
            case 4:
                $this->query->andWhere(['o.is_confirm' => 1]);
                break;
            // 已取消
            case 6:
                $this->query->andWhere(['o.cancel_status' => 1]);
                break;
            // 取消待处理
            case 7:
                $this->query->andWhere(['o.cancel_status' => 2]);
                break;
            case 8:
                $this->query->andWhere(['is_sale' => 1]);
            // 待评价
            case 9:
                $this->query->joinWith(['comments c' => function ($query) {
                    $this->query->andWhere('ISNULL(c.id)');
                }])
                    ->andWhere(['o.is_confirm' => 1])
                    ->andWhere(['o.status' => 1])
                    ->andWhere(['!=', 'o.sign', 'scan_code_pay'])
                    ->andWhere(['!=', 'o.sign', 'vip_card'])
                    ->andWhere(['!=', 'o.is_comment', 1]);
                break;
            default:
                break;
        }
    }

    private function setWithRefund()
    {
        $this->query->with('detail.refund', 'refund');
    }

    private function setWithDetail()
    {
        $this->query->with('detail');
    }

    private function setWithMchOrder()
    {
        try {
            \Yii::$app->plugin->getPlugin('mch');
            $this->query->with('mchOrder');
        } catch (\Exception $exception) {
        }
    }

    private function setWithUser()
    {
        $this->query->with('user');
    }

    private function setWithGoods()
    {
        $this->query->with('detail.goods.goodsWarehouse');
    }

    private function setWithComment()
    {
        $this->query->with('comments');
    }

    private function setSortWhere()
    {
        switch ($this->sort) {
            case 1:
                $this->query->orderBy(['o.created_at' => SORT_DESC]);
                break;
            default:
        }
    }

    private function setAddWhere()
    {
        $this->query->andWhere($this->add_where);
    }

    private function setIsRecycle()
    {
        $this->query->andWhere(['is_recycle' => $this->is_recycle]);
    }

    private function setDate()
    {
        if (is_array($this->dateArr) && isset($this->dateArr[0]) && isset($this->dateArr[1])) {

            if ($this->dateArr[0] && $this->dateArr[1]) {
                $this->query->andWhere(['>=', 'o.created_at', $this->dateArr[0] . ' 00:00:00']);
                $this->query->andWhere(['<=', 'o.created_at', $this->dateArr[1] . ' 23:59:59']);
            } else if ($this->dateArr[0] && !$this->dateArr[1]) {
                $this->query->andWhere(['>=', 'o.created_at', $this->dateArr[0] . ' 00:00:00']);
                $this->query->andWhere(['<=', 'o.created_at', date('Y-m-d', time()) . ' 23:59:59']);
            } else {
                $this->query->andWhere(['<=', 'o.created_at', $this->dateArr[1] . ' 23:59:59']);
            }
        }
    }

    private function setRelations()
    {
        $this->query->with($this->relations);
    }
}
