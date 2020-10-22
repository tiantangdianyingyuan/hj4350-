<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/1
 * Time: 18:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'check_in_tpl',
                'check_in_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/check_in_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/check_in_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/check_in_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '签到提醒(类目: 服装/鞋/箱包 )',
                    'aliapp' => '打卡提醒（模板编号：AT0051 )',
                    'bdapp' => '打卡提醒（模板编号：BD0243 )',
                    'ttapp' => '打卡提醒',
                ]
            ]
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'check_in_tpl' => [
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
                'check_in_tpl' => [
                    'id' => 'BD0243',
                    'keyword_id_list' => [14, 1, 24],
                    'title' => '打卡提醒'
                ],
                //商家活动结果通知BD1041（成功失败都用这个）
                'activity_result_tpl' => [
                    'id' => 'BD1041',
                    'keyword_id_list' => [6, 5, 8],
                    'title' => '商家活动结果通知'
                ],
            ],
        ];
    }
}
