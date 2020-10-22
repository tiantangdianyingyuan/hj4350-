<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 14:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


class TemplateForm extends \app\forms\common\template\TemplateForm
{
    public function getUrl()
    {
        return 'plugin/community/mall/setting/template';
    }

    public function getAddUrl()
    {
        return 'plugin/community/mall/setting/template';
    }

    public function getSubmitUrl()
    {
        return 'plugin/community/mall/setting/template';
    }

    /**
     * @return array
     * 获取默认模板消息设置
     * example：[['name' => '签到提醒(模板编号: AT0264 )','tpl_name' => 'check_in_tpl','check_in_tpl' => '','img_url' => $iconUrlPrefix . 'order_pay_tpl.png','platform' => ['wxapp', 'aliapp']]]
     */
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '',
                'tpl_name' => 'pick_up_tpl',
                'pick_up_tpl' => '',
                'img_url' => [
                    'wxapp' => $iconUrlPrefix . 'wxapp/pick_up_tpl.png',
                    'aliapp' => $iconUrlPrefix . 'aliapp/pick_up_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/pick_up_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/pick_up_tpl.png',
                ],
                'platform' => ['wxapp', 'aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'wxapp' => '订单提货通知(类目: 服装/鞋/箱包 )',
                    'aliapp' => '订单状态通知(模板编号：AT0056)',
                    'bdapp' => '提货通知(模板编号：BD0782)',
                    'ttapp' => '快递派件通知(模板编号：BD1101)',
                ]
            ]
        ];

        return $newDefault;
    }

    /**
     * @return mixed
     * 获取微信、百度小程序模板配置
     * example: ['check_in_tpl' => ['id' => 'AT0264','keyword_id_list' => [0, 1, 5],'title' => '签到提醒'],]
     */
    protected function getTemplateInfo()
    {
        return [
            'wxapp' => [
                'pick_up_tpl' => [
                    'id' => '2306',
                    'keyword_id_list' => [1, 5, 3],
                    'title' => '订单提货通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string1' => '',
                        'thing5' => '',
                        'character_string3' => '',
                    ]
                ],
            ],
            'bdapp' => [
                'pick_up_tpl' => [
                    'id' => 'BD0782',
                    'keyword_id_list' => [11, 10, 6],
                    'title' => '提货通知'
                ],
            ]
        ];
    }
}
