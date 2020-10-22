<?php

/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/17
 * Time: 9:29 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\forms\subscribe;

use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\forms\common\template\tplmsg\AudiResultTemplate;
use app\forms\common\template\tplmsg\OrderCancelTemplate;
use app\forms\common\template\tplmsg\OrderPayTemplate;
use app\forms\common\template\tplmsg\OrderRefund;
use app\forms\common\template\tplmsg\OrderSendTemplate;
use app\forms\common\template\tplmsg\RemoveIdentityTemplate;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Model;
use Yii;

class SubscribeConfig extends Model
{
    /**
     * @return array
     * [
     * 'config' => [ 一键添加的配置
     *      'id'= > '', 类目307--服装/鞋/箱包 774--广告/设计 订阅消息配置
     *      'keyword_id_list' => [], 订阅消息内容的键值需要按照顺序
     *      'title' => '', 订阅消息的标题 必须填写小程序平台上的标题
     *      'categoryId' => '', 类目ID
     *      'type' => '', 订阅类型 2--一次性订阅 1--永久订阅
     *      'data' => [], 订阅笑系内容的字段类型
     *    ],
     * 'tpl_name' => '', 标示
     * 'tpl_id' => '', 订阅消息模板id 小程序平台上的模板id
     * 'key' => '', 所属标示
     * 'key_name' => '', 所属的名称
     * 'local' => [
     *         'name' => '', 后台显示的名称
     *         'img_url' => '', 示例图片
     *    ],
     * 'class' => '' 订阅消息发送类
     * ]
     */
    public static function config()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/wxapp/';
        return [
            [
                'config' => [
                    'id' => '434',
                    'keyword_id_list' => [6, 5, 9, 1],
                    'title' => '下单成功提醒',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string6' => '',
                        'date5' => '',
                        'amount9' => '',
                        'thing1' => '',
                    ],
                ],
                'tpl_name' => 'order_pay_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '下单成功提醒(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'order_pay_tpl.png',
                ],
                'class' => OrderPayTemplate::class,
                'chinese_name' => '下单成功提醒',
            ],
            [
                'config' => [
                    'id' => '994',
                    'keyword_id_list' => [12, 1, 4, 7],
                    'title' => '订单取消通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing12' => '',
                        'character_string1' => '',
                        'amount4' => '',
                        'thing7' => '',
                    ],
                ],
                'tpl_name' => 'order_cancel_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '订单取消通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'order_cancel_tpl.png',
                ],
                'class' => OrderCancelTemplate::class,
                'chinese_name' => '订单取消通知',
            ],
            [
                'config' => [
                    'id' => '855',
                    'keyword_id_list' => [2, 7, 4, 8],
                    'title' => '订单发货通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing2' => '',
                        'thing7' => '',
                        'character_string4' => '',
                        'thing8' => '',
                    ],
                ],
                'tpl_name' => 'order_send_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '订单发货通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'order_send_tpl.png',
                ],
                'class' => OrderSendTemplate::class,
                'chinese_name' => '订单取消通知',
            ],
            [
                'config' => [
                    'id' => '1435',
                    'keyword_id_list' => [4, 5, 2, 1],
                    'title' => '退款通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string4' => '',
                        'thing5' => '',
                        'amount2' => '',
                        'thing1' => '',
                    ],
                ],
                'tpl_name' => 'order_refund_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '退款通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'order_refund_tpl.png',
                ],
                'class' => OrderRefund::class,
            ],
            [
                'config' => [
                    'id' => '1437',
                    'keyword_id_list' => [1, 2, 4],
                    'title' => '活动状态通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing1' => '',
                        'thing2' => '',
                        'thing4' => '',
                    ],
                ],
                'tpl_name' => 'enroll_success_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '活动状态通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'enroll_success_tpl.png',
                ],
                'class' => ActivitySuccessTemplate::class,
            ],
            [
                'config' => [
                    'id' => '818',
                    'keyword_id_list' => [4, 2, 1, 3],
                    'title' => '审核结果通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing4' => '',
                        'phrase2' => '',
                        'thing1' => '',
                        'time3' => '',
                    ],
                ],
                'tpl_name' => 'audit_result_tpl',
                'tpl_id' => '',
                'key' => 'store',
                'key_name' => '商城订阅消息',
                'local' => [
                    'name' => '审核结果通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'mch-tpl-1.png',
                ],
                'class' => AudiResultTemplate::class,
            ],
            [
                'config' => [
                    'id' => '2001',
                    'keyword_id_list' => [1, 2, 3, 4],
                    'title' => '提现成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'amount1' => '',
                        'amount2' => '',
                        'thing3' => '',
                        'thing4' => '',
                    ],
                ],
                'tpl_name' => 'withdraw_success_tpl',
                'tpl_id' => '',
                'key' => 'share',
                'key_name' => '分销订阅消息',
                'local' => [
                    'name' => '提现成功通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'withdraw_success_tpl.png',
                ],
                'class' => WithdrawSuccessTemplate::class,
            ],
            [
                'config' => [
                    'id' => '3173',
                    'keyword_id_list' => [1, 2],
                    'title' => '提现失败通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'amount1' => '',
                        'name2' => '',
                    ],
                ],
                'tpl_name' => 'withdraw_error_tpl',
                'tpl_id' => '',
                'key' => 'share',
                'key_name' => '分销订阅消息',
                'local' => [
                    'name' => '提现失败通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'withdraw_error_tpl.png',
                ],
                'class' => WithdrawErrorTemplate::class,
            ],
            [
                'config' => [
                    'id' => '861',
                    'keyword_id_list' => [3, 2],
                    'title' => '会员等级变更通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing3' => '',
                        'date2' => '',
                    ],
                ],
                'tpl_name' => 'remove_identity_tpl',
                'tpl_id' => '',
                'key' => 'share',
                'key_name' => '分销订阅消息',
                'local' => [
                    'name' => '会员等级变更通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'remove_identity_tpl.png',
                ],
                'class' => RemoveIdentityTemplate::class,
            ]
        ];
    }
}
