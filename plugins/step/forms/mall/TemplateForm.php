<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/25
 * Time: 10:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\step\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    public function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'step_notice',
                'step_notice' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/check_in_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/check_in_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/check_in_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp','bdapp'],
                'tpl_number' => [
                    'wxapp' => '签到提醒(类目: 服装/鞋/箱包 )',
                    'aliapp' => '打卡提醒（模板编号：AT0051 )',
                    'bdapp' => '打卡提醒（模板编号：BD0381 )',
                ]
            ]
        ];

        return $newDefault;
    }

    public function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'step_notice' => [
                    'id' => '817',
                    'keyword_id_list' => [1, 3, 5],
                    'title' => '邀请成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'name1' => '',
                        'time3' => '',
                        'thing5' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'step_notice' => [
                    'id' => 'BD0243',
                    'keyword_id_list' => [14, 1, 24],
                    'title' => '打卡提醒'
                ],
            ],
        ];
    }
}
