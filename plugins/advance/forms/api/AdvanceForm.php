<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\template\TemplateList;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;
use app\plugins\advance\models\AdvanceOrder;
use yii\helpers\ArrayHelper;

class AdvanceForm extends Model
{
    public $id;
    public $page;
    public $keyword;
    public $name;


    public function rules()
    {
        return [
            [['id', 'page',], 'integer'],
            [['keyword', 'name'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = AdvanceOrder::find()->alias('ao')
            ->select(['ao.*', 'ag.goods_id', 'ag.start_prepayment_at', 'ag.end_prepayment_at', 'ag.ladder_rules',
                'ag.pay_limit', 'IFNULL(`o`.`is_pay`, 0) AS order_is_pay', 'o.is_send', 'o.sale_status', 'o.pay_type',
                'IFNULL(o.cancel_status,0) AS cancel_status', 'IFNULL(`o`.`auto_cancel_time`, 0) AS auto_cancel_time',
                'or.status as or_status', 'or.is_confirm'])
            ->where([
                'ao.user_id' => \Yii::$app->user->id,
                'ao.is_delete' => 0,
                'ao.mall_id' => \Yii::$app->mall->id,
                'ao.is_recycle' => 0
            ])->leftJoin(['ag' => AdvanceGoods::tableName()], 'ao.goods_id = ag.goods_id')
            ->leftJoin(['o' => Order::tableName()], 'o.id = ao.order_id and o.is_delete = 0')
            ->leftJoin(['or' => OrderRefund::tableName()], 'or.order_id = o.id');

        if (!empty($this->name)) {
            $model->leftJoin(['g' => Goods::tableName()], 'g.id = ao.goods_id')
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
                ->andWhere(['like', 'gw.name', $this->name]);
        }

        switch ($this->keyword) {
            case -1://全部
                break;
            case 1://定金待支付
                $model->andWhere(['ao.is_pay' => 0, 'ao.is_cancel' => 0, 'ao.is_refund' => 0, 'ao.order_id' => 0]);
                break;
            case 2://尾款支付未开始
                $model->andWhere(['ao.is_pay' => 1, 'ao.is_cancel' => 0, 'ao.is_refund' => 0, 'ao.order_id' => 0])
                    ->andWhere(['<', 'ag.start_prepayment_at', date('Y-m-d H:i:s', time())])
                    ->andWhere(['>', 'ag.end_prepayment_at', date('Y-m-d H:i:s', time())]);
                break;
            case 3://尾款待支付
                $model->andWhere(['ao.is_pay' => 1, 'ao.is_cancel' => 0, 'ao.is_refund' => 0])
                    ->andWhere(['IFNULL(`o`.`is_pay`, 0)' => 0])
                    ->andWhere(['<>', 'IFNULL(`o`.`pay_type`, 0)', 2])
                    ->andWhere(['<>', 'IFNULL(`o`.`cancel_status`, 0)', 1])
                    ->andWhere(['<', 'ag.end_prepayment_at', date('Y-m-d H:i:s', time())])
                    ->andWhere(['>', 'DATE_ADD(ag.end_prepayment_at,INTERVAL case ag.pay_limit when -1 then 10000 end DAY)', date('Y-m-d H:i:s', time())]);
                break;
            case 4://购买成功
                $model->andWhere(['ao.is_pay' => 1, 'ao.is_cancel' => 0, 'ao.is_refund' => 0])
                    ->andWhere(['<>', 'ao.order_id', 0])
                    ->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])
                    ->andWhere(['<>', 'o.cancel_status', 1])
                    ->andWhere(['o.sale_status' => 0]);
                break;
//            case 5://订单已取消
//                break;
//            case 6://订单已售后
//                break;
            case 5://购买失败
                $model->andWhere(
                    ['or',
                        ['ao.is_cancel' => 1],
                        ['and',
                            ['ao.is_refund' => 1],
                            ['<>', 'o.sale_status', 1]
                        ],
                        ['and',
                            ['o.is_pay' => 0], ['<>', 'o.pay_type', 2],
                            ['<', 'DATE_ADD(ag.end_prepayment_at,INTERVAL case ag.pay_limit when -1 then 10000 end DAY)', date('Y-m-d H:i:s', time())]
                        ]
                    ]);
                break;
        }
//        var_dump($model->createCommand()->getRawSql());die;
        if ($this->id) {
            $model->andWhere(['ao.id' => $this->id]);
        }

        $list = $model->with('goods.goodsWarehouse')->with('goodsAttr')->with('attr')
            ->orderBy(['ao.created_at' => SORT_DESC])
            ->page($pagination)->asArray()->all();

        foreach ($list as $key => $item) {
            //定金状态判断
            $list[$key]['status'] = '';
            $list[$key]['status_num'] = '0';
            if ($item['is_pay'] == 0 && $item['is_cancel'] == 0 && $item['is_refund'] == 0 && $item['order_id'] == 0) {
                $list[$key]['status'] = '定金待支付';
                $list[$key]['status_num'] = '1';
            }
            if ($item['is_pay'] == 1 && $item['is_cancel'] == 0 && $item['is_refund'] == 0 && $item['order_id'] == 0
                && strtotime($item['start_prepayment_at']) < time() && strtotime($item['end_prepayment_at']) > time()) {
                $list[$key]['status'] = '尾款支付未开始';
                $list[$key]['status_num'] = '2';
            }
            if ($item['is_pay'] == 1 && $item['is_cancel'] == 0 && $item['is_refund'] == 0
                && $item['order_is_pay'] == 0 && $item['cancel_status'] != 1 && strtotime($item['end_prepayment_at']) < time()
                && (strtotime($item['end_prepayment_at']) + ($item['pay_limit'] > 0 ? $item['pay_limit'] : 10000) * 86400) > time()) {
                $list[$key]['status'] = '尾款待支付';
                $list[$key]['status_num'] = '3';
            }
            if ($item['is_pay'] == 1 && $item['is_cancel'] == 0 && $item['is_refund'] == 0 && $item['order_id'] != 0
                && $item['cancel_status'] != 1 && $item['sale_status'] == 0
                && ($item['pay_type'] == 2 || $item['order_is_pay'] == 1)) {
                $list[$key]['status'] = '购买成功';
                $list[$key]['status_num'] = '4';
            }
            if ($item['cancel_status'] == 1) {
                $list[$key]['status'] = '订单已取消';
                $list[$key]['status_num'] = '5';
            }
            if ($item['sale_status'] == 1 && $item['or_status'] == 2) {
                $list[$key]['status'] = '订单已售后';
                $list[$key]['status_num'] = '6';
            }
            if ($item['is_cancel'] == 1 && $item['is_refund'] != 1) {
                $list[$key]['status'] = '购买失败 定金支付超时';
                $list[$key]['status_num'] = '7';
            }
            if ($item['is_refund'] == 1 & $item['cancel_status'] != 1 && $item['sale_status'] != 1) {
                $list[$key]['status'] = '购买失败 定金已退款';
                $list[$key]['status_num'] = '8';
            }
            if ($item['is_pay'] == 1 && $item['order_is_pay'] == 0 && (strtotime($item['end_prepayment_at']) + ($item['pay_limit'] > 0 ? $item['pay_limit'] : 10000) * 86400) < time()) {
                $list[$key]['status'] = '购买失败 尾款支付超时';
                $list[$key]['status_num'] = '9';
            }
            //尾款计算
            $order_model = AdvanceOrder::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $item['goods_id'],
//            'goods_attr_id' => $item['goods_attr_id'],
                'is_pay' => 1,
                'is_cancel' => 0,
                'is_refund' => 0,
                'is_delete' => 0
            ]);
            $advance_order_count = $order_model->sum('goods_num');
            /* @var AdvanceOrder $order_info */
            $order_info = $order_model->andWhere(['id' => $item['id']])->one();

            $discount = 10;//初始10折，等于没有优惠折扣
            if (!is_array($item['ladder_rules'])) {
                $item['ladder_rules'] = json_decode($item['ladder_rules'], true);
            }
            foreach ($item['ladder_rules'] as $value) {
                if ($advance_order_count >= $value['num']) {
                    $discount = $value['discount'];
                }
            }
            $setting = (new SettingForm())->search();

            $goods_info = json_decode($order_info['goods_info'], true);
            $price = $setting['is_member_price'] ? $goods_info['goods_attr']['member_price'] : $goods_info['goods_attr']['price'];
            $list[$key]['tail_money'] = bcmul(bcsub(bcdiv(bcmul($price, $discount), 10), $item['swell_deposit']), $item['goods_num']);//先阶梯折扣，后膨胀金优惠，再乘以数量

            $list[$key]['tail_money'] = ($list[$key]['tail_money'] < 0) ? 0 : $list[$key]['tail_money'];

            $list[$key]['deposit'] = bcmul($item['deposit'], $item['goods_num']);
            $list[$key]['swell_deposit'] = bcmul($item['swell_deposit'], $item['goods_num']);
        }

        try {
            $template = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                'pay_advance_balance',
            ]);
        } catch (\Exception $exception) {
            $template = [];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'template_message' => $template,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $detail = AdvanceOrder::find()->where([
                'id' => $this->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
            ])->asArray()->one();

            if (!$detail) {
                throw new \Exception('预约订单详情不存在');
            }

            $form = new CommonGoodsDetail();
            $form->mall = \Yii::$app->mall;
            $form->user = User::findOne(\Yii::$app->user->id);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $goods = $form->getAll();


            $attrList = AdvanceGoodsAttr::find()->where([
                'goods_id' => $goods['id'],
                'is_delete' => 0,
            ])->asArray()->all();
            foreach ($goods['attr'] as &$aItem) {
                foreach ($attrList as $alItem) {
                    if ($aItem['id'] == $alItem['goods_attr_id']) {
                        $aItem['deposit'] = $alItem['deposit'];
                        $aItem['swell_deposit'] = $alItem['swell_deposit'];
                    }
                }
            }

            $advanceGoods = AdvanceGoods::findOne(['goods_id' => $goods['id']]);
            $goods = ArrayHelper::toArray($goods);
            $goods['advanceGoods'] = $advanceGoods;

            $setting = (new SettingForm())->search();
            $goods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $goods['goods_marketing']['limit'] : '';
            foreach ($goods['attr'] as &$aItem) {
                $aItem['extra'] = [
                    [
                        'value' => $aItem['deposit'],
                        'name' => '定金'
                    ],
                    [
                        'value' => $aItem['swell_deposit'],
                        'name' => '膨胀金'
                    ]
                ];
            }
            unset($aItem);

            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $goods['share'] = 0;
            }

            $goods['advance_order'] = $detail;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods
                ]
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
}
