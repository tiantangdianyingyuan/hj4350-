<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 9:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    public function getUrl()
    {
        return 'plugin/bargain/mall/index/template';
    }

    public function getAddUrl()
    {
        return 'plugin/bargain/mall/index/template';
    }

    public function getSubmitUrl()
    {
        return 'plugin/bargain/mall/index/template';
    }

    public function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'bargain_success_tpl',
                'bargain_success_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/bargain_success_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/bargain_success_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/bargain_success_tpl.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp'],
                'tpl_number' => [
                    'wxapp' => '砍价成功通知(类目: 服装/鞋/箱包 )',
                    'aliapp' => '订单状态通知(模板编号：AT0056)',
                    'bdapp' => '砍价成功通知(模板编号：BD1101)',
                ]
            ],
            [
                'name' => '',
                'tpl_name' => 'bargain_fail_tpl',
                'bargain_fail_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/bargain_fail_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/bargain_success_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/bargain_fail_tpl.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp'],
                'tpl_number' => [
                    'wxapp' => '砍价失败通知(类目: 服装/鞋/箱包 )',
                    'aliapp' => '订单状态通知(模板编号：AT0056)',
                    'bdapp' => '砍价失败通知(模板编号：BD1502)',
                ]
            ],
        ];

        return $newDefault;
    }

    public function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'bargain_success_tpl' => [
                    'id' => '1975',
                    'keyword_id_list' => [2, 3, 4, 5],
                    'title' => '砍价成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing2' => '',
                        'amount3' => '',
                        'amount4' => '',
                        'thing5' => '',
                    ]
                ],
                'bargain_fail_tpl' => [
                    'id' => '1976',
                    'keyword_id_list' => [2, 3, 4, 5],
                    'title' => '砍价失败通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing2' => '',
                        'amount3' => '',
                        'amount4' => '',
                        'thing5' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'bargain_success_tpl' => [
                    'id' => 'BD1101',
                    'keyword_id_list' => [1, 17, 6, 3],
                    'title' => '砍价成功通知'
                ],
                'bargain_fail_tpl' => [
                    'id' => 'BD1502',
                    'keyword_id_list' => [1, 9, 2, 6],
                    'title' => '砍价成功通知'
                ],
            ]
        ];
    }
}
