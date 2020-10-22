<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\order;


use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\mch\models\MchGoods;
use yii\db\Query;

class CommonOrderStatistic extends Model
{
    public $mch_id;
    public $sign;
    public $year;
    public $monthly;
    public $day;
    public $monthlyDay;

    public $is_user = 0;
    /**
     * @var Query $query
     */
    public $query;
    /**
     * @var Query $refundQuery
     */
    public $refundQuery;

    public function rules()
    {
        return [
            [['mch_id', 'mch_id', 'year', 'monthly', 'day', 'is_user'], 'integer'],
            [['sign'], 'string'],
        ];
    }

    private function getOrderRefundQuery()
    {
        $orderIds = $this->query->select('id');
        $this->refundQuery = OrderRefund::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => $this->mch_id ?: 0,
            'order_id' => $orderIds,
            'type' => 1,
            'is_confirm' => 1,
        ])->andWhere(['!=', 'status', 3]);

        return $this->refundQuery;
    }

    //持续改进
    public function getAll($params = [], $ignore = [])
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->getDate();

        if (count($params) == 0) {
            $params = $this->getDefault();
        }

        $this->query = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
            'o.mch_id' => $this->mch_id ?: 0,
        ]);

        $this->getOrderRefundQuery();

        $result = [];
        foreach ($params as $item) {
            if (in_array($item, $ignore)) {
                continue;
            }
            $get = 'get' . hump($item);
            if (method_exists($this, $get)) {
                $result[$item] = $this->$get();
            }
        }

        return $result;
    }

    /**
     * @return array
     * 获取默认$params信息
     */
    private function getDefault()
    {
        return [
            'order_count', 'order_pay_count', 'order_pay_price_count', 'order_goods_count',
            'day_order_pay_price_count', 'monthly_order_pay_price_count',
        ];
    }

    // 订单总数
    public function getOrderCount()
    {
        $query = clone $this->query;
        return $query->count();
    }

    /**
     * 支付订单总数
     * @return int|string
     */
    public function getOrderPayCount()
    {
        $query = clone $this->query;
        $refundQuery = clone $this->refundQuery;
        $orderCount = $query->andWhere(['is_pay' => 1])->count();
        $refundOrderCount = $refundQuery->count();
        return $orderCount - $refundOrderCount;
    }

    /**
     * 订单成交总额
     * @return int
     */
    public function getOrderPayPriceCount()
    {
        $query = clone $this->query;
        $refundQuery = clone $this->refundQuery;
        // 去除已退款金额
        $refundPrice = $refundQuery->sum('refund_price');
        $totalPayPrice = $query->andWhere(['is_pay' => 1])->sum('total_pay_price');

        return $totalPayPrice - $refundPrice;
    }

    /**
     * 日成交总额
     * @return int
     */
    public function getDayOrderPayPriceCount()
    {
        $query = clone $this->query;
        $date = $this->year . '-' . $this->monthly;
        $query->andWhere([
            'and',
            ['>=', 'created_at', $date . '-' . $this->day . ' 00:00:00'],
            ['<=', 'created_at', $date . '-' . $this->day . ' 23:59:59'],
        ]);

        // 去除已退款金额
        $refundQuery = clone $this->refundQuery;
        $refundQuery->andWhere([
            'and',
            ['>=', 'created_at', $date . '-' . $this->day . ' 00:00:00'],
            ['<=', 'created_at', $date . '-' . $this->day . ' 23:59:59'],
        ]);
        $refundPrice = $refundQuery->sum('refund_price');

        $totalPayPrice = $query->andWhere(['is_pay' => 1])->sum('total_pay_price');
        return $totalPayPrice - $refundPrice;
    }

    /**
     * 月成交总额
     * @return int
     */
    public function getMonthlyOrderPayPriceCount()
    {
        $query = clone $this->query;
        $query->andWhere([
            'and',
            ['>=', 'created_at', $this->year . '-' . $this->monthly . '-01' . ' 00:00:00'],
            ['<=', 'created_at', $this->year . '-' . $this->monthly . '-' . $this->monthlyDay . ' 23:59:59'],
        ]);

        // 去除已退款金额
        $refundQuery = clone $this->refundQuery;
        $refundQuery->andWhere([
            'and',
            ['>=', 'created_at', $this->year . '-' . $this->monthly . '-01' . ' 00:00:00'],
            ['<=', 'created_at', $this->year . '-' . $this->monthly . '-' . $this->monthlyDay . ' 23:59:59'],
        ]);
        $refundPrice = $refundQuery->sum('refund_price');

        $totalPayPrice = $query->andWhere(['is_pay' => 1])->sum('total_pay_price');
        return $totalPayPrice - $refundPrice;
    }

    /**
     * 订单商品销量总数
     * @return int
     */
    public function getOrderGoodsCount()
    {
        $query = clone $this->query;
        $list = $query->andWhere([
            'or',
            ['o.is_pay' => 1],
            ['o.pay_type' => 2]
        ])->andWhere(['!=', 'o.cancel_status', 1])
            ->with('detail.goods')->asArray()->all();

        $goodsCount = 0;
        foreach ($list as $item) {
            foreach ($item['detail'] as $dItem) {
                if ($dItem['is_refund'] == 0) {
                    $goodsCount += $dItem['num'];
                }
            }
        }
        if ($this->is_user) {
            //商品总虚拟销量
            $mchGoods = MchGoods::find()->alias('m')->where([
                'm.mch_id' => $this->mch_id,
                'm.status' => 2,
                'm.mall_id' => \Yii::$app->mall->id,
            ])->leftJoin(['g' => Goods::tableName()], 'g.id = m.goods_id and g.is_delete = 0 and g.status = 1')
                ->select('SUM(g.virtual_sales) as total_virtual_sales')
                ->asArray()
                ->one();
            $goodsCount += $mchGoods['total_virtual_sales'] ?: 0;
        }

        return $goodsCount;
    }

    /**
     * 获取年月信息
     * @return array
     */
    private function getDate()
    {
        $year = $this->year ?: date('Y');
        $monthly = $this->monthly ?: date('m');
        $day = $this->day ?: date('d');
        $monthlyDay = $this->daysInMonth($year, $monthly);

        $this->year = $year;
        $this->monthly = $monthly;
        $this->day = $day;
        $this->monthlyDay = $monthlyDay;
    }


    /**
     * 判断某年的某月有多少天
     * @param string $year
     * @param string $month
     * @return false|string
     */
    function daysInMonth($year = '', $month = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }
        if (empty($month)) {
            $month = date('m');
        }
        $day = '01';

        //检测日期是否合法
        if (!checkdate($month, $day, $year)) {
            return '输入的时间有误';
        }

        //获取当年当月第一天的时间戳(时,分,秒,月,日,年)
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $result = date('t', $timestamp);
        return $result;
    }
}
