<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 18:18
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;


use app\models\Model;

/**
 * @property BaseOrderHandler $orderPayedHandlerClass 订单支付完成事件
 * @property BaseOrderHandler $orderCreatedHandlerClass 订单创建事件
 * @property BaseOrderHandler $orderCanceledHandlerClass 订单取消事件
 * @property BaseOrderHandler $orderSalesHandlerClass 订单售后事件
 * @property BaseOrderHandler $orderChangePriceHandlerClass 订单改价事件
 */
class OrderHandler extends Model
{
    protected $orderPayedHandlerClass;
    protected $orderCreatedHandlerClass;
    protected $orderCanceledHandlerClass;
    protected $orderSalesHandlerClass;
    protected $orderChangePriceHandlerClass;

    public function getOrderPayedHandlerClass()
    {
        try {
            if ($this->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                $orderPayedHandlerClass = $plugin->getOrderPayedHandleClass();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order payed handler--' . $exception->getMessage());
            $orderPayedHandlerClass = new OrderPayedHandlerClass();
        }
        return $orderPayedHandlerClass;
    }

    public function getOrderCreatedHandlerClass()
    {
        try {
            if ($this->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                $orderCreatedHandlerClass = $plugin->getOrderCreatedHandleClass();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order payed handler--' . $exception->getMessage());
            $orderCreatedHandlerClass = new OrderCreatedHandlerClass();
        }
        return $orderCreatedHandlerClass;
    }

    public function getOrderCanceledHandlerClass()
    {
        try {
            if ($this->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                $orderCanceledHandlerClass = $plugin->getOrderCanceledHandleClass();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order payed handler--' . $exception->getMessage());
            $orderCanceledHandlerClass = new OrderCanceledHandlerClass();
        }
        return $orderCanceledHandlerClass;
    }

    public function getOrderChangePriceHandlerClass()
    {
        try {
            if ($this->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                $orderCreatedHandlerClass = $plugin->getOrderChangePriceHandlerClass();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order payed handler--' . $exception->getMessage());
            $orderCreatedHandlerClass = new OrderChangePriceHandlerClass();
        }
        return $orderCreatedHandlerClass;
    }

    public function getOrderSalesHandlerClass()
    {
        try {
            if ($this->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                $orderSalesHandlerClass = $plugin->getOrderSalesHandleClass();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order payed handler--' . $exception->getMessage());
            $orderSalesHandlerClass = new OrderSalesHandlerClass();
        }
        return $orderSalesHandlerClass;
    }
}
