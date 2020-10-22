<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\order\rprint\BirdPrint;
use app\forms\common\order\rprint\Kd100Print;
use app\forms\common\order\rprint\NullPrint;
use app\models\Delivery;
use app\models\Express;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetailExpress;
use app\models\OrderExpressSingle;

class PrintForm extends Model
{
    public $order_id;
    public $express;
    public $zip_code;
    public $mch_id;
    public $delivery_account;
    public $order_detail_ids;

    public function rules()
    {
        return [
            [['order_id', 'express'], 'required'],
            [['order_id', 'mch_id'], 'integer'],
            [['zip_code', 'express', 'delivery_account'], 'string'],
            [['order_detail_ids'], 'trim'],
            [['zip_code'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'express' => "快递公司名称",
            'delivery_account' => '面单账户',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $result = $this->track();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => $result
            ];
        } catch (\DomainException $e) {
            $json = \yii\helpers\BaseJson::decode($e->getMessage());
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $json['Reason'] ?? $json['message'],
                'result' => $json,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function track()
    {
        $express = Express::getOne($this->express);
        if (!$express) {
            throw new \Exception('快递公司不正确');
        }
        $config = $this->getSetting();
        if ($cache = $this->getCache($order = $this->getOrder(), $express, $config)) {
            //kd100 格式化
            isset($cache['Order']['LogisticCode']) || $cache['Order']['LogisticCode'] =  current($cache['Order']['data'])['kuaidinum'];
            return $cache;
        }
        $delivery = $this->formatDelivery($express['id']);


        switch ($config['print_type']) {
            case 'kd100':
                $model = new Kd100Print();
                break;
            case '':
                $model = new BirdPrint();
                break;
            default:
                $model = new NullPrint();
                break;
        }
        $model->baseDataSet($delivery, $order, $express, $config);
        return $model->track($this->attributes);
    }

    private function getSetting()
    {
        $select = [
            'kd100_key',
            'kd100_secret',
            'kd100_siid',
            'print_type',
            'kdniao_mch_id',
        ];
        return (new Mall())->getMallSetting($select);
    }

    private function formatDelivery($express_id): array
    {
        $otherWhere = [];
        $this->delivery_account && $otherWhere = ['customer_account' => $this->delivery_account];

        $delivery = Delivery::findOne(array_merge([
            'express_id' => $express_id,
            'is_delete' => 0,
            'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
            'mall_id' => \Yii::$app->mall->id
        ], $otherWhere));
        empty($delivery) && $delivery = CommonOption::get(Option::NAME_DELIVERY_DEFAULT_SENDER, \Yii::$app->mall->id, 'app');

        if (!$delivery) {
            throw new \Exception('请先设置发件人信息');
        }
        return [
            'customer_account' => $delivery['customer_account'] ?? '',
            'customer_pwd' => $delivery['customer_pwd'] ?? '',
            'outlets_code' => $delivery['outlets_code'] ?? '',
            'month_code' => $delivery['month_code'] ?? '',
            'template_size' => $delivery['template_size'] ?? '',
            'is_sms' => $delivery['is_sms'] ?? '',
            'is_goods' => $delivery['is_goods'] ?? 0,
            'goods_alias' => $delivery['goods_alias'] ?? '商品',
            'is_goods_alias' => $delivery['is_goods_alias'] ?? 0,
            'business_type' => $delivery['business_type'] ?? 1,
            'outlets_name' => $delivery['outlets_name'] ?? '',
            'kd100_business_type' => $delivery['kd100_business_type'] ?? '',
            'kd100_template' => $delivery['kd100_template'] ?? '',

            'company' => $delivery['company'],
            'name' => $delivery['name'],
            'tel' => $delivery['tel'],
            'mobile' => $delivery['mobile'],
            'zip_code' => $delivery['zip_code'],
            'province' => $delivery['province'],
            'city' => $delivery['city'],
            'district' => $delivery['district'],
            'address' => $delivery['address'],
        ];
    }

    private function getCache($order, $express, $config)
    {
        $ebusiness_id = $config['print_type'] === 'kd100' ? $config['kd100_key'] : $config['kdniao_mch_id'];
        $expressSingle = OrderExpressSingle::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'ebusiness_id' => $ebusiness_id,
            'order_id' => $order->id,
            'express_code' => $express['code'],
        ]);
        if (!$expressSingle) {
            return false;
        }
        $detailExpress = OrderDetailExpress::findOne([
            'express_single_id' => $expressSingle->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if ($detailExpress) {
            return false;
        }
        return [
            'EBusinessID' => $expressSingle->ebusiness_id,
            'Order' => json_decode($expressSingle->order, true),
            'PrintTemplate' => $expressSingle->print_teplate,
            'express_single' => $expressSingle
        ];
    }

    /**
     * @return Order
     * @throws \Exception
     */
    private function getOrder(): Order
    {
        /** @var Order $order */
        $order = Order::find()->where([
            'id' => $this->order_id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
        ])->with('detailExpress')->one();
        if (!$order) {
            throw new \Exception('订单不存在');
        }

        if ($order->status == 0) {
            throw new \Exception('订单进行中,不能进行操作');
        }
        return $order;
    }
}
