<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/1
 * Time: 18:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mch\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'mch_order_tpl',
                'mch_order_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/mch-tpl-2.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/mch-tpl-2.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/mch-tpl-2.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/mch-tpl-2.png',
                ],
                'platform' => ['wxapp', 'aliapp','bdapp','ttapp'],
                'tpl_number' => [
                    'wxapp' => '订单进度提醒(类目: 服装/鞋/箱包  )',
                    'aliapp' => '订单状态通知（模板编号：AT0049 )',
                    'bdapp' => '新订单通知（模板编号：BD0061 )',
                    'ttapp' => '',
                ]
            ],
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'mch_order_tpl' => [
                    'id' => '4498',
                    'keyword_id_list' => [2, 3, 4, 1],
                    'title' => '订单进度提醒',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string2' => '',
                        'amount3' => '',
                        'date4' => '',
                        'thing1' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'mch_order_tpl' => [
                    'id' => 'BD0061',
                    'keyword_id_list' => [6, 87, 36, 8],
                    'title' => '新订单通知'
                ],
            ]
        ];
    }
}
