<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 9:48
 */


namespace app\plugins\gift\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '中奖结果通知',
                'gift_convert' => '',
                'tpl_name' => 'gift_convert',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/gift_result_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/gift_result_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/gift_result_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '（类目: 服装/鞋/箱包 ）',
                    'aliapp' => '（模板编号：AT0159）',
                    'bdapp' => '（模板编号：BD1123）',
                    'ttapp' => '（模板编号：A3）',
                ]
            ],
            [
                'name' => '',
                'gift_form_user' => '',
                'tpl_name' => 'gift_form_user',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/gift_timeout_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/gift_timeout_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/gift_timeout_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '商品送达通知（类目: 服装/鞋/箱包 ）',
                    'aliapp' => '礼物未成功送达通知（模板编号：AT0049）',
                    'bdapp' => '礼物未成功送达通知（模板编号：BD0545）',
                    'ttapp' => '礼物未成功送达通知（模板编号：A1）',
                ]
            ],
            [
                'name' => '礼物即将超时通知',
                'gift_to_user' => '',
                'tpl_name' => 'gift_to_user',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/gift_fail_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/gift_fail_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/gift_timeout_tpl.png',
//                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['aliapp', 'bdapp'],
                'tpl_number' => [
                    'wxapp' => '（模板编号：AT0583）',
                    'aliapp' => '（模板编号：AT0056）',
                    'bdapp' => '（模板编号：BD0545）',
//                    'ttapp' => '',
                ]
            ]
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'gift_convert' => [
                    'id' => '3217',
                    'keyword_id_list' => [1, 5, 4, 3],
                    'title' => '中奖结果通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing1' => '',
                        'name5' => '',
                        'phrase4' => '',
                        'thing3' => '',
                    ]
                ],
                'gift_form_user' => [
                    'id' => '992',
                    'keyword_id_list' => [1, 2, 3],
                    'title' => '商品送达通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string1' => '',
                        'thing2' => '',
                        'thing3' => '',
                    ]
                ]
            ],
            'bdapp' => [
                'gift_convert' => [
                    'id' => 'BD1123',
                    'keyword_id_list' => [13, 6, 11, 5],
                    'title' => '中奖结果通知'
                ],
                'gift_form_user' => [
                    'id' => 'BD0545',
                    'keyword_id_list' => [7, 9, 15],
                    'title' => '订单超时提醒'
                ],
                'gift_to_user' => [
                    'id' => 'BD0545',
                    'keyword_id_list' => [7, 9, 4, 15],
                    'title' => '订单超时提醒'
                ]
            ],
        ];
    }
}
