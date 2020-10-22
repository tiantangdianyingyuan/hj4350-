<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\mptemplate\tplmsg;

use app\models\Model;

class MpTemplateMsgData extends Model
{
    /**
     * 订单下单支付
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function newOrderTpl($config, $params = array())
    {
        if (!isset($params['time'])) {
            throw new \Exception('数组缺少参数time');
        }
        if (!isset($params['sign'])) {
            throw new \Exception('数组缺少参数sign');
        }
        if (!isset($params['user'])) {
            throw new \Exception('数组缺少参数user');
        }
        if (!isset($params['goods'])) {
            throw new \Exception('数组缺少参数goods');
        }

        $corePlugins = \Yii::$app->plugin->list;
        $select = [];
        foreach ($corePlugins as $plugin) {
            try {
                $select[$plugin['name']] = $plugin['display_name'] . '订单';
            } catch (ClassNotFoundException $exception) {
                continue;
            }
        }

        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '您有一笔新订单，请及时处理。',
                    'color' => '#666666',
                ],
                'tradeDateTime' => [
                    'value' => $params['time'],
                    'color' => '#000000',
                ],
                'orderType' => [
                    'value' => $select[$params['sign']] ?? '商城订单',
                    'color' => '#000000',
                ],
                'customerInfo' => [
                    'value' => $params['user'],
                    'color' => '#000000',
                ],
                'orderItemName' => [
                    'value' => '商品信息',
                    'color' => '#000000',
                ],
                'orderItemData' => [
                    'value' => $params['goods'],
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '',
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送分销商入驻申请通知
     * @return bool
     */
    public function shareApplyTpl($config, $params = array())
    {
        if (!isset($params['content'])) {
            throw new \Exception('数组缺少参数goods');
        }

        if (!isset($params['time'])) {
            throw new \Exception('数组缺少参数time');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '有新的用户申请成为分销商，请及时处理。',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => $params['time'],
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['content'],
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '',
                    'color' => '#666666',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送分销商提现通知
     */
    public function shareWithdrawTpl($config, $params = array())
    {
        if (!isset($params['money'])) {
            throw new \Exception('数组缺少参数money');
        }

        if (!isset($params['user'])) {
            throw new \Exception('数组缺少参数user');
        }

        if (!isset($params['time'])) {
            throw new \Exception('数组缺少参数time');
        }

        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '有分销商申请提现，请及时处理。',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => $params['time'],
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['money'],
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => "申请用户：{$params['user']}",
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送多商户入驻申请通知
     */
    public function mchApplyTpl($config, $params = array())
    {
        if (!isset($params['content'])) {
            throw new \Exception('数组缺少参数content');
        }
        if (!isset($params['time'])) {
            throw new \Exception('数组缺少参数time');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '有新的用户申请成为入驻商，请及时处理。',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => $params['time'],
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['content'],
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '',
                    'color' => '#666666',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送入驻商商品上架申请通知
     */
    public function mchGoodApplyTpl($config, $params = array())
    {
        if (!isset($params['goods'])) {
            throw new \Exception('数组缺少参数goods');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '入驻商有新的商品申请上架，请及时处理。',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => '商品上架',
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => date('Y-m-d H:i:s'),
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => "商品信息：{$params['goods']}",
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送订单取消申请通知
     */
    public function cancelOrderTpl($config, $params = array())
    {
        if (!isset($params['order_no'])) {
            throw new \Exception('数组缺少参数order_no');
        }
        if (!isset($params['price'])) {
            throw new \Exception('数组缺少参数price');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '订单申请取消通知',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => $params['order_no'],
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['price'],
                    'color' => '#000000',
                ],
                'keyword3' => [
                    'value' => date('Y-m-d H:i'),
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '您有新的订单取消申请，请尽快处理。',
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }

    /**
     * 发送订单售后申请通知
     */
    public function saleOrderTpl($config, $params = array())
    {
        if (!isset($params['order_no'])) {
            throw new \Exception('数组缺少参数order_no');
        }
        if (!isset($params['status'])) {
            throw new \Exception('数组缺少参数status');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '订单申请售后通知',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => $params['order_no'],
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['status'],
                    'color' => '#000000',
                ],
                'keyword3' => [
                    'value' => date('Y-m-d H:i'),
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '您有新的订单售后申请，请尽快处理。',
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }

    public function applySubmitTpl($config, $params = array())
    {
        if (!isset($params['time'])) {
            throw new \Exception('数组缺少参数time');
        }
        $result = [
            'templateId' => $config['template_id'],
            'miniprogram' => [
                'appid' => $config['app_id'],
                'pagepath' => $params['page'] ?? '',
            ],
            'data' => [
                'first' => [
                    'value' => '申请提交成功通知',
                    'color' => '#666666',
                ],
                'keyword1' => [
                    'value' => '表单提交',
                    'color' => '#000000',
                ],
                'keyword2' => [
                    'value' => $params['time'],
                    'color' => '#000000',
                ],
                'remark' => [
                    'value' => '您有一个新的表单提交信息，请到管理后台处理',
                    'color' => '#000000',
                ],
            ],
        ];
        return $result;
    }
}
