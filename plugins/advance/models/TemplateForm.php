<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 9:48
 */


namespace app\plugins\advance\models;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'pay_advance_balance' => '',
                'tpl_name' => 'pay_advance_balance',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/tailmoney_pay_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/tailmoney_pay_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/tailmoney_pay_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '商品到货通知（类目: 服装/鞋/箱包 ）',
                    'aliapp' => '尾款支付提醒（模板编号：AT0314）',
                    'bdapp' => '尾款支付提醒（模板编号：BD0768）',
                    'ttapp' => '',
                ]
            ]
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'pay_advance_balance' => [
                    'id' => '2956',
                    'keyword_id_list' => [6, 2, 4],
                    'title' => '商品到货通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing6' => '',
                        'amount2' => '',
                        'thing4' => '',
                    ]
                ]
            ],
            'bdapp' => [
                'pay_advance_balance' => [
                    'id' => 'BD0768',
                    'keyword_id_list' => [6, 2, 4, 5],
                    'title' => '尾款支付提醒'
                ]
            ],
        ];
    }
}
