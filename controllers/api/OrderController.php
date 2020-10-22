<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 16:01
 */


namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\core\response\ApiCode;
use app\forms\api\order\CancelRefundForm;
use app\forms\api\order\OrderAppraiseForm;
use app\forms\api\order\OrderClerkForm;
use app\forms\api\order\OrderEditForm;
use app\forms\api\order\OrderExpressForm;
use app\forms\api\order\OrderForm;
use app\forms\api\order\OrderListPayForm;
use app\forms\api\order\OrderPayForm;
use app\forms\api\order\OrderRefundForm;
use app\forms\api\order\OrderRefundSendForm;
use app\forms\api\order\OrderRefundSubmitForm;
use app\forms\api\order\OrderPayResultForm;
use app\forms\api\order\OrderSubmitForm;
use app\forms\api\StoreForm;
use app\models\Express;

class OrderController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setEnableFullReduce(true)->preview());
    }

    public function actionUsableCouponList()
    {
        $form = new OrderSubmitForm();
        $form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $form->isCheckWhite = false;
        $list = $form->getUsableCouponList($form_data);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    public function actionSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        return $this->asJson($form->setEnableFullReduce(true)->setSupportPayTypes($mallPaymentTypes)->submit());
    }

    public function actionPayData()
    {
        $form = new OrderPayForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }

    public function actionPayResult()
    {
        $form = new OrderPayResultForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getResponseData());
    }

    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionList()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }

    /**
     * 订单详情
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $form = new OrderEditForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getDetail());
    }

    /**
     * 售后详情
     * @return \yii\web\Response
     */
    public function actionApplyRefund()
    {
        $form = new OrderRefundForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getDetail());
    }

    /**
     * 售后 生成退换货订单
     * @return \yii\web\Response
     */
    public function actionRefundSubmit()
    {
        $form = new OrderRefundSubmitForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->submit());
    }

    /**
     * 售后 退换货用户 发货
     * @return \yii\web\Response
     */
    public function actionRefundSend()
    {
        $form = new OrderRefundSendForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->send());
    }

    /**
     * 售后 退换货订单详情
     * @return \yii\web\Response
     */
    public function actionRefundDetail()
    {
        $form = new OrderRefundForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getOrderRefundDetail());
    }

    /**
     * 订单确认收货
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->orderConfirm());
    }

    /**
     * 订单取消 | 申请取消退款
     * @return \yii\web\Response
     */
    public function actionCancel()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->orderCancel());
    }

    /**
     * 订单列表 未付款订单支付
     * @return \yii\web\Response
     */
    public function actionListPayData()
    {
        $form = new OrderListPayForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getResponseData());
    }

    /**
     * 订单评价
     * @return \yii\web\Response
     */
    public function actionAppraise()
    {
        $form = new  OrderAppraiseForm();
        $form->appraiseData = \Yii::$app->request->post('appraiseData');
        $form->order_id = \Yii::$app->request->post('order_id');

        return $this->asJson($form->appraise());
    }

    /**
     * 订单物流详情
     * @return \yii\web\Response
     */
    public function actionExpressDetail()
    {
        $form = new OrderExpressForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->search());
    }

    /**
     * 订单核销确认收款
     */
    public function actionClerkAffirmPay()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->affirmPay());
    }

    /**
     * 订单核销
     */
    public function actionOrderClerk()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->OrderClerk());
    }

    /**
     * 核销码
     * @return \yii\web\Response
     */
    public function actionClerkQrCode()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->qrClerkCode());
    }

    public function actionExpressList()
    {
        $list = Express::getExpressList();

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ]);
    }

    public function actionStoreList($longitude = null, $latitude = null)
    {
        $form = new StoreForm();
        $form->attributes = \Yii::$app->request->get();
        $form->limit = 30;
        $result = $form->search();
        if (!$longitude || !$latitude) {
            return $result;
        }
        if ($result['code'] == 0 && isset($result['data']) && isset($result['data']['list'])) {
            foreach ($result['data']['list'] as &$store) {
                if (!$store['longitude'] || !$store['latitude']) {
                    $store['distance'] = '-';
                    continue;
                }
                $distance = get_distance($longitude, $latitude, $store['longitude'], $store['latitude']);
                if (is_nan($distance)) {
                    continue;
                }
                if ($distance >= 1000) {
                    $distance = round($distance / 1000, 2) . 'km';
                } else {
                    $distance = round($distance, 2) . 'm';
                }
                $store['distance'] = $distance;
            }
            return $result;
        } else {
            return $result;
        }
    }

    public function actionCancelCauseList()
    {
        $res = [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => [
                    '多买/错买/不想要',
                    '未按时间发货',
                    '地址填写错误',
                    '缺货',
                    '其它'
                ]
            ]
        ];
        return $this->asJson($res);
    }

    // 用户撤销申请退款（未发货）
    public function actionCancelApply()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->cancelApply());
    }

    // 用户撤销售后申请
    public function actionCancelRefund()
    {
        $form = new CancelRefundForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->cancelRefund());
    }

    // 同城配送 骑手地图信息
    public function actionCityMap()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->cityMap());
        }
    }
}
