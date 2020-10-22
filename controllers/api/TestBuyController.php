<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/11 14:48
 */


namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\controllers\api\notices\TestBuyNotify;
use app\core\payment\PaymentOrder;

class TestBuyController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionOrderSubmit()
    {
        $data = \Yii::$app->request->post();
        $paymentOrder = new PaymentOrder([
            'orderNo' => date('YmdHis'),
            'amount' => (double)$data['amount'],
            'title' => $data['title'],
            'notifyClass' => TestBuyNotify::class,
        ]);
        $id = \Yii::$app->payment->createOrder([$paymentOrder]);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }
}
