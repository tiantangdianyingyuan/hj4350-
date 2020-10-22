<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/3
 * Time: 10:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\demo\models;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '订单支付成功通知',
                'order_pay_tpl' => '',
                'tpl_name' => 'order_pay_tpl',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/order_pay_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/order_pay_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'order_pay_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/order_pay_tpl.png',
                ],
                'platform' => ['wxapp', 'aliapp','ttapp','bdapp'],
                'tpl_number' => [
                    'wxapp' => '（模板编号：AT0674）',
                    'aliapp' => '（模板编号：BD0221）',
                    'ttapp' => '（模板编号：TT0001）',
                    'bdapp' => '（模板编号：DB0002）',
                ]
            ]
        ];

        return $newDefault;
    }

    //目前支持微信和百度小程序模板消息的一键获取
    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'order_pay_tpl' => [
                    'id' => 'AT0009',
                    'keyword_id_list' => [5, 6, 11, 4],
                    'title' => '订单支付成功通知'
                ],
            ],
            'bdapp' => [
                'order_pay_tpl' => [
                    'id' => 'BD0221',
                    'keyword_id_list' => [2, 9, 81, 34],
                    'title' => '下单成功通知'
                ],
            ]
        ];
    }
}
