<?php

namespace app\forms\mall\share;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class ShareCustomForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'trim'],
        ];
    }

    public function saveData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        CommonOption::set(Option::NAME_SHARE_CUSTOMIZE, $this->data, \Yii::$app->mall->id, 'api');
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }

    public function getData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $default = $this->getDefaultData();
//        $default = $this->getBonus($default);
        $data = CommonOption::get(Option::NAME_SHARE_CUSTOMIZE, \Yii::$app->mall->id, 'api', $default);
        $data = $this->checkData($data, $default);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data,
        ];
    }

    //检查是否有新增的值
    private function checkData($list = array(), $default_list = array())
    {
        $ignore = ['menu'];
        $new_list = [];
        foreach ($default_list as $index => $value) {
            if (isset($list[$index])) {
                if (is_array($value) && !in_array($index, $ignore)) {
                    $new_list[$index] = $this->checkData($list[$index], $value);
                } else {
                    $new_list[$index] = $list[$index];
                }
            } else {
                $new_list[$index] = $value;
            }
        }
        return $new_list;
    }


    private function getDefaultData()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/share-custom/';

        return [
            'menus' => [
                'money' => [
                    'name' => '分销佣金',
                    'icon' => $iconUrlPrefix . 'img-share-price.png',
                    'open_type' => 'navigator',
                    'url' => '/pages/share-money/share-money',
                    'tel' => '',
                ],
                'order' => [
                    'name' => '分销订单',
                    'icon' => $iconUrlPrefix . 'img-share-order.png',
                    'open_type' => 'navigator',
                    'url' => '/pages/share-order/share-order',
                    'tel' => '',
                ],
                'cash' => [
                    'name' => '提现明细',
                    'icon' => $iconUrlPrefix . 'img-share-cash.png',
                    'open_type' => 'navigator',
                    'url' => '/pages/cash-detail/cash-detail',
                    'tel' => '',
                ],
                'team' => [
                    'name' => '我的团队',
                    'icon' => $iconUrlPrefix . 'img-share-team.png',
                    'open_type' => 'navigator',
                    'url' => '/pages/share-team/share-team',
                    'tel' => '',
                ],
                'qrcode' => [
                    'name' => '推广二维码',
                    'icon' => $iconUrlPrefix . 'img-share-qrcode.png',
                    'open_type' => 'navigator',
                    'url' => '/pages/share-qrcode/share-qrcode',
                    'tel' => '',
                ],
            ],
            'words' => [
                'can_be_presented' => [
                    'name' => '可提现佣金',
                    'default' => '可提现佣金',
                ],
                'already_presented' => [
                    'name' => '已提现佣金',
                    'default' => '已提现佣金',
                ],
                'parent_name' => [
                    'name' => '推荐人',
                    'default' => '推荐人',
                ],
                'pending_money' => [
                    'name' => '待打款佣金',
                    'default' => '待打款佣金',
                ],
                'cash' => [
                    'name' => '提现',
                    'default' => '提现',
                ],
                'user_instructions' => [
                    'name' => '用户须知',
                    'default' => '用户须知',
                ],
                'apply_cash' => [
                    'name' => '我要提现',
                    'default' => '我要提现',
                ],
                'cash_type' => [
                    'name' => '提现方式',
                    'default' => '提现方式',
                ],
                'cash_money' => [
                    'name' => '提现金额',
                    'default' => '提现金额',
                ],
                'order_money_un' => [
                    'name' => '未结算佣金',
                    'default' => '未结算佣金',
                ],
                'share_name' => [
                    'name' => '分销商',
                    'default' => '分销商',
                ],
                'one_share' => [
                    'name' => '一级分销名称',
                    'default' => '一级分销名称',
                ],
                'second_share' => [
                    'name' => '二级分销名称',
                    'default' => '二级分销名称',
                ],
                'three_share' => [
                    'name' => '三级分销名称',
                    'default' => '三级分销名称',
                ],
            ],
            'apply' => [
                'share_apply' => [
                    'name' => '分销申请',
                    'default' => '分销申请',
                ],
                'share_apply_pact' => [
                    'name' => '分销申请协议',
                    'default' => '分销申请协议',
                ],
                'apply_btn_color' => "#FFFFFF",
                'apply_btn_background' => '#FF4544',
                'apply_btn_title' => '申请成为分销商',
                'apply_btn_round' => 40,
                'apply_head_pic' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/share/img-share-apply.png',
                'apply_end_pic' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/share/apply-end-pic.png',
            ]
        ];
    }

//    private function getBonus($data)
//    {
//        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/share-custom/';
//
//        $list = \Yii::$app->plugin->getList();
//        foreach ($list as $value) {
//            if ($value['display_name'] == '团队分红') {
//                $data['menus']['bonus'] = [
//                    'name' => '团队分红',
//                    'icon' => $iconUrlPrefix . 'img-bonus-price.png',
//                    'open_type' => 'navigator',
//                    'url' => '/plugins/bonus/index/index',
//                    'tel' => '',
//                ];
//
//                break;
//            }
//        }
//        return $data;
//    }
}
