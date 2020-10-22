<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 11:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\dianqilai\forms;



class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '咨询回复通知',
                'contact_tpl' => '',
                'tpl_name' => 'contact_tpl',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/contact_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/account_change_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/contact_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/contact_tpl.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '(模板编号: 6773)',
                    'aliapp' => '(模板编号: AT0133)',
                    'bdapp' => '(模板编号：BD1941)',
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
                'contact_tpl' => [
                    'id' => '6773',
                    'keyword_id_list' => [1, 2, 3],
                    'title' => '留言回复通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'name1' => '',
                        'date2' => '',
                        'thing3' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'contact_tpl' => [
                    'id' => 'BD1941',
                    'keyword_id_list' => [1, 4, 5],
                    'title' => '咨询回复通知'
                ],
            ]
        ];
    }
}
