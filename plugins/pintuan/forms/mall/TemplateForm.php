<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/1
 * Time: 18:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\pintuan\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '拼团成功通知',
                'tpl_name' => 'pintuan_success_notice',
                'pintuan_success_notice' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/pt_success_notice.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/pt_success_notice.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/pt_success_notice.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp','bdapp','ttapp'],
                'tpl_number' => [
                    'wxapp' => '(类目: 服装/鞋/箱包 )',
                    'aliapp' => '(模板编号：AT0143 )',
                    'bdapp' => '(模板编号：BD0041 )',
                    'ttapp' => '',
                ]
            ],
            [
                'name' => '拼团失败通知',
                'tpl_name' => 'pintuan_fail_notice',
                'pintuan_fail_notice' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/pt_fail_notice.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/pt_fail_notice.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/pt_fail_notice.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp','bdapp','ttapp'],
                'tpl_number' => [
                    'wxapp' => '(类目: 服装/鞋/箱包 )',
                    'aliapp' => '(模板编号：AT0141 )',
                    'bdapp' => '(模板编号：BD0301 )',
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
                'pintuan_success_notice' => [
                    'id' => '980',
                    'keyword_id_list' => [1, 3, 5],
                    'title' => '拼团成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing1' => '',
                        'number3' => '',
                        'thing5' => '',
                    ]
                ],
                'pintuan_fail_notice' => [
                    'id' => '1953',
                    'keyword_id_list' => [8, 1, 5],
                    'title' => '拼团失败通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string8' => '',
                        'thing1' => '',
                        'thing5' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'pintuan_success_notice' => [
                    'id' => 'BD0041',
                    'keyword_id_list' => [6, 13, 15],
                    'title' => '拼团成功通知'
                ],
                'pintuan_fail_notice' => [
                    'id' => 'BD0301',
                    'keyword_id_list' => [6, 2, 5],
                    'title' => '拼团失败通知'
                ],
            ]
        ];
    }
}
