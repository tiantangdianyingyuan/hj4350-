<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\common\CommonUser;
use app\forms\mall\export\PriceStatisticsExport;
use app\forms\mall\finance\FinanceForm;
use app\models\MallMemberOrders;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PaymentOrder;
use app\models\RechargeOrders;
use app\models\User;
use app\models\UserInfo;
use yii\db\Exception;
use yii\db\Query;

class PriceForm extends Model
{
    public $pay_type;//支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付
    public $sign;
    public $start_time;
    public $end_time;
    public $flag;
    public $platform;

    public $in_where;

    public $data = [
        'date' => '',
        'order_price' => 0,
        'member_price' => 0,
        'balance' => 0,
        'cash_price' => 0,
        'income_price' => 0,
        'sign' => [],
    ];

    public function rules()
    {
        return [
            [['pay_type', 'sign', 'flag', 'platform'], 'string'],
            [['start_time', 'end_time'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->platform) {
            $this->in_where = User::find()->alias('u')
                ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = u.id')
                ->andWhere(['u.mall_id' => \Yii::$app->mall->id, 'ui.platform' => $this->platform, 'u.is_delete' => 0])
                ->select('u.id');
        }
        $this->order()->member()->balance()->cash();
        if (!$this->start_time && !$this->end_time) {
            $this->data['date'] = $this->start_time . '-' . $this->end_time;
        }
        $this->data['income_price'] = bcsub(bcadd(bcadd($this->data['order_price'], $this->data['member_price']), $this->data['balance']), $this->data['cash_price']);

        if ($this->flag == "EXPORT") {
            $this->export($this->data);
            return false;
        }
        $cash_map = [];
        $permission = \Yii::$app->role->permission;
        foreach ($permission as $k => $item) {
            if ($item == 'share') {
                $cash_map[] = '分销商提现';
            }
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item);
                if (!$plugin->needCash()) {
                    continue;
                }
                $cash_map[] = $plugin->getDisplayName() . '提现';
            } catch (\Exception $exception) {
            }
        }

        $this->data['cash_map'] = !empty($cash_map) ? $text = '包括' . implode('、', $cash_map) : '';


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $this->data,
        ];
    }


    protected function export($query)
    {
        $exp = new PriceStatisticsExport();
        $exp->start_time = $this->start_time;
        $exp->end_time = $this->end_time;
        $exp->platform = $this->platform;
        $exp->export($query);
    }

    private function order()
    {
        $this->data['sign'] = Order::find()->where(['mall_id' => \Yii::$app->mall->id])->andWhere(['!=', 'sign', ''])->groupBy('sign')->select('sign')->asArray()->all();
        if (!empty($this->data['sign'])) {
            foreach ($this->data['sign'] as &$datum) {
                $PluginClass = 'app\\plugins\\' . $datum['sign'] . '\\Plugin';
                /** @var \app\plugins\Plugin $object */
                if (!class_exists($PluginClass)) {
                    $datum['name'] = '未知插件';
                    continue;
                }
                $object = new $PluginClass();
                $datum['name'] = $object->getDisplayName() ?? '未知插件';

            }
        }
        $model = Order::find()->alias('o')
            ->where(['o.mall_id' => \Yii::$app->mall->id, 'o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_recycle' => 0])
            ->andWhere(['!=', 'o.cancel_status', 1])
//            ->innerJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id and od.is_refund = 0 and od.is_delete = 0')
            ->leftJoin(['po' => PaymentOrder::tableName()], 'po.order_no = o.order_no and po.is_pay = 1')
            ->andWhere(['!=', 'po.pay_type', 3]);
        if ($this->in_where) {
            $model->andWhere(['in', 'o.user_id', $this->in_where]);
        }
        if ($this->pay_type) {
            $model->andWhere(['po.pay_type' => $this->pay_type]);
        }
        if ($this->sign) {
            $model->andWhere(['o.sign' => $this->sign]);
        }
        if ($this->start_time) {
            $model->andWhere(['>=', 'po.created_at', $this->start_time]);
        }
        if ($this->end_time) {
            $model->andWhere(['<=', 'po.created_at', $this->end_time]);
        }
        $data = $model->select('sum(po.amount-po.refund) as order_price')->asArray()->one();
        $this->data['order_price'] = price_format(!empty($data) ? $data['order_price'] : 0);
        return $this;
    }

    private function member()
    {
        $model = MallMemberOrders::find()->alias('mo')
            ->where(['mo.mall_id' => \Yii::$app->mall->id, 'mo.is_delete' => 0, 'mo.is_pay' => 1])
            ->leftJoin(['po' => PaymentOrder::tableName()], 'po.order_no = mo.order_no')
            ->andWhere(['!=', 'po.pay_type', 3])
            ->select('sum(mo.pay_price) as pay_price');
        if ($this->in_where) {
            $model->andWhere(['in', 'mo.user_id', $this->in_where]);
        }
        if ($this->pay_type) {
            $model->andWhere(['po.pay_type' => $this->pay_type]);
        }
        if ($this->start_time) {
            $model->andWhere(['>=', 'mo.created_at', $this->start_time]);
        }
        if ($this->end_time) {
            $model->andWhere(['<=', 'mo.created_at', $this->end_time]);
        }
        $data = $model->asArray()->one();
        $this->data['member_price'] = price_format(!empty($data) ? $data['pay_price'] : 0);
        return $this;
    }

    private function balance()
    {
        $model = RechargeOrders::find()->alias('ro')
            ->where(['ro.mall_id' => \Yii::$app->mall->id, 'ro.is_delete' => 0, 'ro.is_pay' => 1])
            ->leftJoin(['po' => PaymentOrder::tableName()], 'po.order_no = ro.order_no')
            ->select('sum(ro.pay_price) as pay_price');
        if ($this->in_where) {
            $model->andWhere(['in', 'ro.user_id', $this->in_where]);
        }
        if ($this->pay_type) {
            $model->andWhere(['po.pay_type' => $this->pay_type]);
        }
        if ($this->start_time) {
            $model->andWhere(['>=', 'ro.created_at', $this->start_time]);
        }
        if ($this->end_time) {
            $model->andWhere(['<=', 'ro.created_at', $this->end_time]);
        }
        $data = $model->asArray()->one();
        $this->data['balance'] = price_format(!empty($data) ? $data['pay_price'] : 0);
        return $this;
    }

    private function cash()
    {
        $financeForm = new FinanceForm();
        $financeForm->status = 2;
        $financeForm->date_start = $this->start_time;
        $financeForm->date_end = $this->end_time;
        $query = $financeForm->getQuery();
        if (!$query) {
            $this->data['cash_price'] = 0;
            return $this;
        }
        $tempQuery = (new Query())->from($query);
        if ($this->in_where) {
            $tempQuery->where(['in', 'user_id', $this->in_where]);
        }
        $this->data['cash_price'] = price_format($tempQuery->sum('price - (price * service_charge / 100)') ?? 0);
        return $this;
    }
}
