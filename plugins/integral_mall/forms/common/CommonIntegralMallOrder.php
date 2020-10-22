<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\common;


use app\models\Mall;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallOrders;

/**
 * @property Mall $mall
 */
class CommonIntegralMallOrder extends Model
{
    public $mall;

    /**
     * @param null $mall
     * @return CommonIntegralMallOrder
     */
    public static function getCommonIntegralMallOrder($mall = null)
    {
        $model = new CommonIntegralMallOrder();
        $model->mall = $mall ? $mall : \Yii::$app->mall;
        return $model;
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定订单
     */
    public function getIntegralMallOrder($id)
    {
        $order = IntegralMallOrders::find()->where(['id' => $id, 'is_delete' => 0])->one();

        return $order;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定token的订单
     */
    public function getTokenOrder($token)
    {
        $bargainOrder = IntegralMallOrders::find()->where(['token' => $token])->one();

        return $bargainOrder;
    }
}
