<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/23
 * Time: 16:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\lottery\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'lottery_tpl',
                'lottery_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/gift_result_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/gift_result_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/gift_result_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp'],
                'tpl_number' => [
                    'wxapp' => '中奖结果通知(类目: 服装/鞋/箱包 )',
                    'aliapp' => '抽奖活动结果通知(模板编号：AT0159)',
                    'bdapp' => '中奖结果通知(模板编号：BD1123)',
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
                'lottery_tpl' => [
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
            ],
            'bdapp' => [
                'lottery_tpl' => [
                    'id' => 'BD1123',
                    'keyword_id_list' => [13, 6, 11, 5],
                    'title' => '中奖结果通知'
                ]
            ]
        ];
    }
}
