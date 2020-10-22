<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/22
 * Time: 9:29
 */

namespace app\plugins\vip_card\models;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '会员卡到期提醒',
                'vip_card_remind' => '',
                'tpl_name' => 'vip_card_remind',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/svip_expire_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/svip_expire_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/svip_expire_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/none.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '（类目: 服装/鞋/箱包 ）',
                    'aliapp' => '（模板编号：AT0002）',
                    'bdapp' => '（模板编号：BD1382）',
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
                'vip_card_remind' => [
                    'id' => '3572',
                    'keyword_id_list' => [2, 3],
                    'title' => '会员卡到期提醒',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'phrase2' => '',
                        'date3' => '',
                    ]
                ]
            ],
            'bdapp' => [
                'vip_card_remind' => [
                    'id' => 'BD1382',
                    'keyword_id_list' => [2, 4],
                    'title' => '产品到期通知'
                ]
            ],
        ];
    }
}
