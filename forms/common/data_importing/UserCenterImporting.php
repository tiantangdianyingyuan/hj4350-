<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\Option;

class UserCenterImporting extends BaseImporting
{
    public function import()
    {
        try {
            $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
                '/statics/img/mall/user-center/';

            $newMenus = [];
            foreach ($this->v3Data['menus'] as $menu) {
                if ($menu['open_type'] == 'contact') {
                    $menu['url'] = 'contact';
                }
                if ($menu['open_type'] == 'no_navigator') {
                    $menu['url'] = 'clear_cache';
                }
                if ($menu['open_type'] == 'tel') {
                    $menu['url'] = 'tel?tel=' . $menu['tel'];
                }

                $pickLink = PickLink::getNewLink($menu['url']);
                $arr = [
                    'icon_url' => $menu['icon'],
                    'link_url' => $pickLink['url'],
                    'name' => $menu['name'],
                    'open_type' => isset($pickLink['data']['open_type']) ? $pickLink['data']['open_type'] : PickLinkForm::OPEN_TYPE_2,
                    'params' => isset($pickLink['data']['params']) ? $pickLink['data']['params'] : []
                ];
                $newMenus[] = $arr;
            }
            $data = [
                'is_account_status' => (string)$this->v3Data['is_wallet'],
                'is_menu_status' => (string)$this->v3Data['is_menu'],
                'is_order_bar_status' => (string)$this->v3Data['is_order'],
                'menu_style' => (string)($this->v3Data['menu_style'] + 1),
                'top_style' => (string)($this->v3Data['top_style'] + 1),
                'top_pic_url' => $this->v3Data['user_center_bg'],
                'member_pic_url' => $iconUrlPrefix . 'icon-member.png',
                'member_bg_pic_url' => $iconUrlPrefix . 'card-member-0.png',
                'account' => [
                    [
                        'icon_url' => $this->v3Data['wallets']['status_2']['icon'],
                        'name' => $this->v3Data['wallets']['status_2']['text'],
                    ],
                    [
                        'icon_url' => $this->v3Data['wallets']['status_0']['icon'],
                        'name' => $this->v3Data['wallets']['status_0']['text'],
                    ],
                    [
                        'icon_url' => $this->v3Data['wallets']['status_1']['icon'],
                        'name' => $this->v3Data['wallets']['status_1']['text'],
                    ],
                ],
                'menus' => $newMenus,
                'order_bar' => [
                    [
                        'icon_url' => $this->v3Data['orders']['status_0']['icon'],
                        'name' => '待付款',
                    ],
                    [
                        'icon_url' => $this->v3Data['orders']['status_1']['icon'],
                        'name' => '待发货',
                    ],
                    [
                        'icon_url' => $this->v3Data['orders']['status_2']['icon'],
                        'name' => '待收货',
                    ],
                    [
                        'icon_url' => $this->v3Data['orders']['status_3']['icon'],
                        'name' => '待评价',
                    ],
                    [
                        'icon_url' => $this->v3Data['orders']['status_4']['icon'],
                        'name' => '售后',
                    ],
                ]
            ];

            $pickLink = PickLink::getNewLink($this->v3Data['copyright']['url']);
            $copyright = [
                'pic_url' =>  $this->v3Data['copyright']['icon'],
                'description' => $this->v3Data['copyright']['text'],
                'type' => $this->v3Data['copyright']['is_phone'] ? 1: 2,
                'link_url' => $pickLink['url'],
                'mobile' => $this->v3Data['copyright']['phone'],
                'link' => isset($pickLink['data']['parmas']) ? $pickLink['data']['parmas'] : [],
            ];

            $res = CommonOption::set(Option::NAME_COPYRIGHT, $copyright, $this->mall->id, Option::GROUP_APP);
            if (!$res) {
                if (!$res) {
                    throw new \Exception('版权设置，保存失败');
                }
            }

            $res = CommonOption::set(Option::NAME_USER_CENTER, $data, $this->mall->id, Option::GROUP_APP);
            if (!$res) {
                throw new \Exception('用户中心配置，保存失败');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}