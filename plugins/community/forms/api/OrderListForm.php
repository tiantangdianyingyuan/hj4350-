<?php

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\order\CommonOrderDetail;
use app\forms\common\order\CommonOrderList;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\community\forms\common\CommonForm;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityMiddlemanActivity;
use app\plugins\community\models\CommunityOrder;
use app\plugins\community\models\Order;

class OrderListForm extends Model
{
    public $mall;
    public $page;
    public $status;
    public $id;
    public $type;
    public $keyword;

    public $date_start;
    public $date_end;

    public $is_pagination;//-1不分页的时候，输出用于复制的数据

    public function rules()
    {
        return [
            [['type'], 'required'],
            [['keyword'], 'safe'],
            [['page', 'status', 'id', 'is_pagination'], 'integer'],
            [['date_start', 'date_end'], 'string'],
            [['page', 'is_pagination'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new CommonOrderList();
            $form->status = $this->status;
            $form->sign_id = 'community';
            $form->is_detail = 1;
            $form->is_pagination = ($this->is_pagination == 1) ? 1 : 0;
            $form->page = $this->page;
            $form->is_array = 1;
            $form->is_refund = 1;
            $form->is_goods = 1;
            $form->relations = ['user.userInfo'];
            if ($form->status == 0 || $form->status == 6) {
                $form->is_cancel_status = false;
            }
            //用户
            if ($this->type == 1) {
                $form->user_id = \Yii::$app->user->id;
            }
            $form->getQuery();

            $form->query->rightJoin(['co' => CommunityOrder::tableName()], 'co.order_id = o.id')
                ->leftJoin(['ca' => CommunityActivity::tableName()], 'ca.id = co.activity_id')
                ->addSelect('o.*,co.no,ca.title,co.activity_id');
            if ($this->date_start) {
                $form->query->andWhere(['>=', 'o.created_at', $this->date_start . ' 00:00:00']);
            }
            if ($this->date_end) {
                $form->query->andWhere(['<=', 'o.created_at', $this->date_end . ' 00:00:00']);
            }
            //团长
            if ($this->type == 2) {
                $form->query->andWhere(['co.middleman_id' => \Yii::$app->user->id]);
            }
            switch ($form->status) {
                case 2:
                    $form->query->andWhere(['o.cancel_status' => 0]);
                    break;
                case 4:
                    $form->query->andWhere(['o.is_sale' => 0]);
                    break;
                case 9:
                    $form->query->andWhere(['<>', 'o.cancel_status', 0]);
                    break;
                default:
                    break;
            }
            if ($this->keyword) {
                switch ($this->type) {
                    case 1:
                        $form->query->leftJoin(['cm' => CommunityMiddleman::tableName()], 'co.middleman_id = cm.user_id')
                            ->where(['like', 'cm.mobile', $this->keyword]);
                        break;
                    case 2:
                        $form->query->andWhere(['or', ['ca.title' => $this->keyword], ['like', 'o.mobile', $this->keyword]]);
                        break;
                }
            }


            $list = $form->query->asArray()->all();

            if ($this->is_pagination == 1) {
                foreach ($list as &$item) {
                    if ($item['order_form']) {
                        $item['order_form'] = \Yii::$app->serializer->decode($item['order_form']);
                    }
                    $item['num'] = 0;
                    foreach ($item['detail'] as &$dItem) {
                        $goodsAttrInfo = \Yii::$app->serializer->decode($dItem['goods_info']);
                        $goodsInfo = [
                            'attr_list' => $goodsAttrInfo['attr_list'],
                            'name' => $dItem['goods']['goodsWarehouse']['name'],
                            'num' => $dItem['num'],
                            'total_original_price' => $dItem['total_original_price'],
                            'member_discount_price' => $dItem['member_discount_price'],
                            'pic_url' => $goodsAttrInfo['goods_attr']['pic_url'] ?: $dItem['goods']['goodsWarehouse']['cover_pic']
                        ];
                        $dItem['goods_info'] = $goodsInfo;
                        $item['num'] += $dItem['num'];
                        unset($dItem['goods']);
                    }
                    $item['user_avatar'] = $item['user']['userInfo']['avatar'];
                    unset($item['user']);

                    $item['is_remind'] =
                        (CommunityMiddlemanActivity::findOne(['activity_id' => $item['activity_id'], 'middleman_id' => \Yii::$app->user->id, 'is_delete' => 0]))->is_remind ?? 0;
                    $item['no'] = CommonForm::setNum($item['no']);
                }
            } else {
                $order = [];
                foreach ($list as $listKey => &$item) {
                    $order[$listKey]['name'] = $item['name'];
                    $order[$listKey]['list'] = [];
                    foreach ($item['detail'] as $detailKey => $dItem) {
                        $goodsAttrInfo = \Yii::$app->serializer->decode($dItem['goods_info']);

                        $order[$listKey]['list'][$detailKey]['goods'] = $dItem['goods']['goodsWarehouse']['name'];
                        $order[$listKey]['list'][$detailKey]['attr'] = $goodsAttrInfo['attr_list'];

                        $order[$listKey]['list'][$detailKey]['num'] = $dItem['num'];
                    }
                }
                $list = $order;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->type == 2) {
                $order = CommunityOrder::findOne(['order_id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'middleman_id' => \Yii::$app->user->id]);
                if (!$order) {
                    throw new \Exception('该订单您无权确认');
                }
            }
            $form = new CommonOrderDetail();
            $form->model = 'app\\plugins\\community\\models\\Order';
            $form->id = $this->id;
            $form->is_detail = 1;
            $form->is_goods = 1;
            $form->is_refund = 1;
            $form->is_array = 1;
            $form->is_store = 1;
            $form->relations = ['user.userInfo', 'communityOrder.middleman.user.userInfo', 'communityOrder.middleman.address'];
            $detail = $form->search();
            if (!$detail) {
                throw new \Exception('订单不存在');
            }
            $goodsNum = 0;
            // 统一商品信息，用于前端展示
            foreach ($detail['detail'] as $key => &$item) {
                $goodsNum += $item['num'];
                $goodsInfo['name'] = $item['goods']['goodsWarehouse']['name'];
                $goodsInfo['num'] = $item['num'];
                $goodsInfo['total_original_price'] = $item['total_original_price'];
                $goodsInfo['member_discount_price'] = $item['member_discount_price'];

                $item['is_show_apply_refund'] = 0;

                $refund = OrderRefund::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'order_detail_id' => $item['id']])->orderBy('id DESC')->one();

                if (($detail['is_pay'] == 1 || $detail['pay_type'] == 2) && !$refund && $detail['is_sale'] == 0 && $this->type == 1 && $detail['is_send'] == 1 && $detail['is_confirm'] == 1) {
                    $item['is_show_apply_refund'] = 1;
                }
                // 规格信息json 转 数组
                if ($item['goods_info']) {
                    $goodsAttrInfo = \Yii::$app->serializer->decode($item['goods_info']);
                    $goodsInfo['attr_list'] = $goodsAttrInfo['attr_list'];
                    $picUrl = $goodsAttrInfo['goods_attr']['pic_url'] ?: $item['goods']['goodsWarehouse']['cover_pic'];
                    $goodsInfo['pic_url'] = $picUrl;
                }

                // 售后订单 状态
                if (isset($item['refund'])) {
                    $detail['detail'][$key]['refund']['status_text'] = (new OrderRefund())->statusText($item['refund']);
                    $refundList = OrderRefund::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'order_detail_id' => $item['id']])->all();
                    // 售后被拒绝后可再申请一次
                    if ($refund->status == 3 && count($refundList) == 1 && $this->type == 1) {
                        $item['is_show_apply_refund'] = 1;
                    }
                }
                $detail['detail'][$key]['goods_info'] = $goodsInfo;
            }
            // 订单状态
            $detail['status_text'] = (new Order())->orderStatusText($detail);
            $detail['pay_type_text'] = (new Order())->getPayTypeText($detail['pay_type']);
            // 订单商品总数
            $detail['goods_num'] = $goodsNum;
            //门店信息
//            $bookingGoods = CommonGoods::getGoods($item['goods_id']);
//            $detail['store_list'] = $bookingGoods->store;
            //
            if ($detail['order_form'] && $detail['order_form'] !== "[]") {
                $detail['order_form'] = \Yii::$app->serializer->decode($detail['order_form']);
            } else {
                $detail['order_form'] = [];
            }

            $detail['user_avatar'] = $detail['user']['userInfo']['avatar'];
            $detail['profit_price'] = $detail['communityOrder']['profit_price'] > 0 ? $detail['communityOrder']['profit_price'] : 0;
            $detail['profit_data'] = json_decode($detail['communityOrder']['profit_data'], true);
            $detail['activity_name'] = CommunityActivity::findOne($detail['communityOrder']['activity_id'])->title;
            $detail['no'] = CommonForm::setNum($detail['communityOrder']['no']);
            $detail['no_QrCode'] = (new CommonQrCode())->getQrCode(['id' => $this->id], 430, 'plugins/community/order-detail/order-detail');
            $detail['middleman_info']['nickname'] = $detail['communityOrder']['middleman']['user']['nickname'];
            $detail['middleman_info']['avatar'] = $detail['communityOrder']['middleman']['user']['userInfo']['avatar'];
            $detail['middleman_info']['name'] = $detail['communityOrder']['middleman']['name'];
            $detail['middleman_info']['mobile'] = $detail['communityOrder']['middleman']['mobile'];
            $detail['middleman_info']['middleman_name'] = CommonSetting::getCommon()->getSetting()['middleman'];
            $detail['middleman_info']['address'] =
                (($detail['communityOrder']['middleman']['address']['province']
                    == $detail['communityOrder']['middleman']['address']['city'])
                    ? $detail['communityOrder']['middleman']['address']['city']
                    : ($detail['communityOrder']['middleman']['address']['province']
                        . $detail['communityOrder']['middleman']['address']['city']))
                . $detail['communityOrder']['middleman']['address']['district']
                . $detail['communityOrder']['middleman']['address']['detail']
                . $detail['communityOrder']['middleman']['address']['location'];
            unset($detail['user']);
            unset($detail['communityOrder']);

            //插件优惠信息
            $detail['plugin_data'] = [];

            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getOrderInfo')) {
                    $data = $object->getOrderInfo($detail['id'], $detail);
                    if ($data && is_array($data)) {
                        foreach ($data as $datum) {
                            if (isset($datum['profit_price'])) {
                                continue;
                            }
                            $detail['plugin_data'] = $datum;
                        }
                    }
                }
                if (method_exists($object, 'changeOrderInfo')) {
                    $detail = $object->changeOrderInfo($detail);
                }
            }

            //自动取消时间
            $detail['cancel_time_stamp'] = strtotime($detail['auto_cancel_time']) - time();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail
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

    public function clerkCode()
    {
        try {
            $qrCode = new CommonQrCode();
            $res = $qrCode->getQrCode(['id' => $this->id], 100, 'pages/order/clerk/clerk');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
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
