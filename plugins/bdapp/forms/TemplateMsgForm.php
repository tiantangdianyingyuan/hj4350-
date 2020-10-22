<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\bdapp\forms;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\bdapp\Plugin;

/**
 * @property Mall $mall
 */
class TemplateMsgForm extends Model
{
    public $mall;
    public $user_id;
    public $tpl_id;

    public function getDetail()
    {
        $plugin = \Yii::$app->plugin->getCurrentPlugin();
        if (method_exists($plugin, 'getTemplateList')) {
            $list = $plugin->getTemplateList();
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "当前插件{$plugin->getDisplayName()}不支持"
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $this->getList($list),
            ]
        ];
    }

    public function getList($list)
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/bdapp/';

        $default = [
            [
                'name' => '商城模板消息',
                'key' => 'store',
                'list' => [
                    [
                        'name' => '下单成功通知(模板编号: BD0221 )',
                        'order_pay_tpl' => '',
                        'tpl_name' => 'order_pay_tpl',
                        'img_url' => $iconUrlPrefix . 'order_pay_tpl.png'
                    ],
                    [
                        'name' => '订单取消(模板编号: BD0021 )',
                        'order_cancel_tpl' => '',
                        'tpl_name' => 'order_cancel_tpl',
                        'img_url' => $iconUrlPrefix . 'order_cancel_tpl.png'
                    ],
                    [
                        'name' => '订单发货(模板编号: BD0003 )',
                        'order_send_tpl' => '',
                        'tpl_name' => 'order_send_tpl',
                        'img_url' => $iconUrlPrefix . 'order_send_tpl.png'
                    ],
                    [
                        'name' => '订单退款(模板编号: BD0022 )',
                        'order_refund_tpl' => '',
                        'tpl_name' => 'order_refund_tpl',
                        'img_url' => $iconUrlPrefix . 'order_refund_tpl.png'
                    ],
                    [
                        'name' => '审核结果通知(模板编号: BD0141 )',
                        'audit_result_tpl' => '',
                        'tpl_name' => 'audit_result_tpl',
                        'img_url' => $iconUrlPrefix . 'mch-tpl-1.png'
                    ],
                ]
            ],
            [
                'name' => '分销模板消息',
                'key' => 'share',
                'list' => [
                    [
                        'name' => '提现成功(模板编号: BD0781 )',
                        'withdraw_success_tpl' => '',
                        'tpl_name' => 'withdraw_success_tpl',
                        'img_url' => $iconUrlPrefix . 'withdraw_success_tpl.png'
                    ],
                    [
                        'name' => '提现失败(模板编号: BD1161 )',
                        'withdraw_error_tpl' => '',
                        'tpl_name' => 'withdraw_error_tpl',
                        'img_url' => $iconUrlPrefix . 'withdraw_error_tpl.png'
                    ],
                    [
                        'name' => '账户变动提醒(模板编号: BD0643 )',
                        'remove_identity_tpl' => '',
                        'tpl_name' => 'remove_identity_tpl',
                        'img_url' => $iconUrlPrefix . 'remove_identity_tpl.png'
                    ],
                ]
            ],
        ];

        if (!$list) {
            return $default;
        }

        $newList = [];
        foreach ($list as $item) {
            $newList[$item['tpl_name']] = $item['tpl_id'];
        }

        foreach ($default as $k => $item) {
            foreach ($item['list'] as $k2 => $item2) {
                if (isset($newList[$item2['tpl_name']])) {
                    $default[$k]['list'][$k2][$item2['tpl_name']] = $newList[$item2['tpl_name']];
                }
            }
        }

        return $default;
    }

    public function search()
    {
        try {
            $plugin = new Plugin();
            $templateList = $plugin->templateInfoList();
            $templateIdList = $plugin->addTemplate($templateList);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $this->getList($templateIdList),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
