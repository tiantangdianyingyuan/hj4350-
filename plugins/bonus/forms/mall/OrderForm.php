<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/3
 * Email: <657268722@qq.com>
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Express;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserInfo;
use app\plugins\bonus\forms\export\OrderExport;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusOrderLog;

class OrderForm extends Model
{
    public $flag;
    public $fields;

    public $status;
    public $keyword;
    public $keyword_1;
    public $page;
    public $limit;
    public $send_type;
    public $date_start;
    public $date_end;
    public $mch_id;

    public $order_id;
    public $remark;

    public $captain_id;
    public $platform;

    // 前端操作 显示设置
    public $is_send_show;
    public $is_cancel_show;
    public $is_clerk_show;
    public $is_confirm_show;
    public $orderModel = 'app\models\Order';

    public function rules()
    {
        return [
            [['order_id', 'mch_id', 'captain_id'], 'integer'],
            [['flag', 'platform'], 'string'],
            [['keyword',], 'trim'],
            [['status', 'page', 'limit', 'send_type', 'keyword_1',], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['send_type',], 'default', 'value' => -1],
            [['date_start', 'date_end', 'fields'], 'trim'],
            [['is_send_show', 'is_cancel_show', 'is_clerk_show', 'is_confirm_show'], 'default', 'value' => 1],
            ['remark', 'string', 'max' => 200]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $result = $this->export($new_query);
            return $result;
        }

        if (\Yii::$app->user->identity->mch_id) {
            $query->with('mch.store');
        }
        $list = $query->page($pagination)
            ->orderBy('o.created_at DESC')
            ->select(['o.*', 'u.nickname', 'bo.remark as bonus_remark', 'bc.name as captain_name', 'bc.mobile as captain_mobile', 'bo.bonus_price', 'bo.status as bonus_status'])
            ->with(['detail.refund', 'detail.goods.goodsWarehouse'])
            ->with('detail.expressRelation')
            ->with('clerk', 'detailExpressRelation')
            ->with('user.userInfo')
            ->with('store', 'expressSingle')
            ->with('detailExpress.expressRelation.orderDetail.expressRelation')
            ->with('detailExpress.expressSingle')
            ->asArray()
            ->all();

        $order = new Order();
        foreach ($list as &$item) {
            $item['platform'] = $item['user']['userInfo']['platform'];
            $item['nickname'] = $item['user']['nickname'];
            $item['avatar'] = $item['user']['userInfo']['avatar'];

            unset($item['user']);
            //插件名称
            if ($item['sign'] == '' && $item['mch_id'] == 0) {
                $item['plugin_name'] = '商城';
            } elseif ($item['mch_id'] > 0) {
                $item['plugin_name'] = '多商户';
            } else {
                try {
                    $item['plugin_name'] = \Yii::$app->plugin->getPlugin($item['sign'])->getDisplayName();
                } catch (\Exception $exception) {
                    $item['plugin_name'] = '未知插件';
                }
            }

            $item['order_form'] = json_decode($item['order_form'], true);
            foreach ($item['detail'] as $key => &$detail) {
                $goods_info = \Yii::$app->serializer->decode($detail['goods_info']);
                $item['detail'][$key]['attr_list'] = $goods_info['attr_list'];

                $refund_status = 0;
                if ($detail['refund']) {
                    $refund_status = $detail['refund']['status'];
                }
                $item['detail'][$key]['refund_status'] = $refund_status;
                $detail['goods_info'] = \Yii::$app->serializer->decode($detail['goods_info']);

                //插件名称
                if ($detail['goods']['sign'] == '') {
                    $detail['plugin_name'] = '商城';
                } elseif ($detail['goods']['mch_id'] > 0) {
                    $detail['plugin_name'] = '多商户';
                } else {
                    try {
                        $detail['plugin_name'] = \Yii::$app->plugin->getPlugin($detail['goods']['sign'])->getDisplayName();
                    } catch (\Exception $exception) {
                        $detail['plugin_name'] = '未知插件';
                    }
                }
            }
            // 控制订单操作 是否显示(例如拼团)
            $item['is_send_show'] = $this->is_send_show;
            $item['is_cancel_show'] = $this->is_cancel_show;
            $item['is_clerk_show'] = $this->is_clerk_show;
            $item['is_confirm_show'] = $this->is_confirm_show;
            // 订单操作状态
            $item['action_status'] = $order->getOrderActionStatus($item);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
                'express_list' => Express::getExpressList(),
                'export_list' => $this->getFieldsList(),
            ]
        ];
    }


    protected function where()
    {
        $query = Order::find()->alias('o')
            ->rightJoin(['bo' => BonusOrderLog::tableName()], 'o.id = bo.order_id')
            ->leftJoin(['u' => User::tableName()], 'u.id = o.user_id')
            ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = o.user_id')
            ->leftJoin(['bc' => BonusCaptain::tableName()], 'bc.user_id = bo.to_user_id')
            ->andWhere(['bo.mall_id' => \Yii::$app->mall->id, 'bo.is_delete' => 0])
            ->andWhere(['AND', ['o.is_recycle' => 0], ['not', ['o.cancel_status' => 1]], ['not', ['bo.status' => 2]]]);

        if (\Yii::$app->user->identity->mch_id > 0) {
            $query->andWhere(['o.mch_id' => \Yii::$app->user->identity->mch_id]);
        }

        if ($this->date_start) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end]);
        }

        if ($this->send_type != -1) {
            $query->andWhere(['o.send_type' => $this->send_type]);
        }

        $query->keyword($this->platform, ['ui.platform' => $this->platform]);

        $query->keyword($this->status == 1, ['bo.status' => 0])
            ->keyword($this->status == 2, ['bo.status' => 1]);

        if ($this->keyword || $this->keyword == 0) {
            switch ($this->keyword_1) {
                case 1:
                    $query->andWhere(['like', 'o.order_no', $this->keyword]);
                    break;
                case 2:
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                    break;
                case 3:
                    $query->andWhere(['like', 'o.name', $this->keyword]);
                    break;
                case 4:
                    $query->andWhere(['u.id' => $this->keyword]);
                    break;
                case 5:
                    $query->andWhere(['exists', (OrderDetail::find()->alias('od')
                        ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                            $query1->where(['like', 'gw.name', $this->keyword]);
                        }])->where("o.id = od.order_id"))]);
                    break;
                case 6:
                    $query->andWhere(['like', 'o.mobile', $this->keyword]);
                    break;
                case 7:
                    $query->andWhere(['like', 'bc.name', $this->keyword]);
                    break;
                default:
                    $query->andWhere(['or', ['like', 'u.nickname', $this->keyword], ['like', 'o.order_no', $this->keyword]]);
                    break;
            }
        }

        //用于小程序端默认显示队长下团员订单
        if ($this->captain_id) {
            $query->andWhere(['bo.to_user_id' => $this->captain_id]);
        }

        return $query;
    }

    protected function export($query)
    {
        $exp = new OrderExport();
        $exp->fieldsKeyList = $this->fields;
        $exp->send_type = $this->send_type;
        $exp->page = $this->page;
        return $exp->export($query);
    }

    protected function getFieldsList()
    {
        return (new OrderExport())->fieldsList();
    }

    public function remark()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (BonusOrderLog::updateAll(['remark' => $this->remark], ['order_id' => $this->order_id])) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '备注成功'
            ];
        }
        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '更新失败'
        ];

    }

}
