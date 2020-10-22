<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 17:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\templates;


/**
 * Class FirstTemplate
 * @package app\forms\common\prints\templates
 */
class FirstTemplate extends BaseTemplate
{
    public function getContent()
    {
        $data = $this->data;
        $printer = $this->printer;
        $content = $printer->getTimes();
        $content .= $printer->getCenterBold($data->mall_name);

        $content .= $printer->getBR("订单类型：{$data->order_type}");
        $content .= $printer->getBR("支付方式：{$data->pay_type}");
        $content .= $printer->getBR("发货方式：{$data->send_type_text}");
        $content .= $printer->getBR("订单号：{$data->order_no}");
        $content .= $printer->getBR("下单时间：{$data->created_at}");
        $content .= $printer->getDivide();
        if ($data->is_attr == 0) {
            $content .= $printer->getTableNoAttr($data);
        } else {
            $content .= $printer->getTableAttr($data);
        }
        $content .= $printer->getDivide();
        $content .= $printer->getBR("商品总计：" . $printer->getPrice($data->total_goods_original_price));
        if ($data->send_type == 0) {
            $content .= $printer->getBR("运费：" . $printer->getPrice($data->express_price));
        }
        if (isset($data->coupon_discount_price) && $data->coupon_discount_price) {
            $content .= $printer->getBR("优惠券优惠：" . $printer->getPrice($data->coupon_discount_price));
        }
        if (isset($data->integral_deduction_price) && $data->integral_deduction_price) {
            $content .= $printer->getBR("积分抵扣：" . $printer->getPrice($data->integral_deduction_price));
        }
        if (isset($data->member_discount_price) && $data->member_discount_price) {
            $content .= $printer->getBR("会员优惠：" . $printer->getPrice($data->member_discount_price));
        }
        if ($data->back_price < 0) {
            $subPrice = round(abs($data->back_price), 2);
            $content .= $printer->getBR("后台改价：" . $printer->getPrice($subPrice));
        } elseif ($data->back_price > 0) {
            $subPrice = round(abs($data->back_price), 2);
            $content .= $printer->getBR("后台改价：" . $printer->getPrice($subPrice));
        }
        if (count($data->plugin_data) > 0) {
            foreach ($data->plugin_data as $datum) {
                $content .= $printer->getBR($datum['label'] . '：' . $datum['value']);
            }
        }
        $content .= $printer->getBR("实际支付：" . $printer->getPrice($data->total_pay_price));
        $content .= $printer->getDivide();
        if ($data->send_type != 1) {
            $content .= $printer->getBR("收货人：{$data->name}");
            $content .= $printer->getBR("收货地址：{$data->address}");
            $content .= $printer->getBR("收货人电话：{$data->mobile}");
            $content .= $printer->getDivide();
        } else {
            $content .= $printer->getBR("联系人：{$data->name}");
            $content .= $printer->getBR("联系人电话：{$data->name}");
            $content .= $printer->getDivide();
            $content .= $printer->getBR("门店信息");
            $content .= $printer->getBR("{$data->store_name}");
            $content .= $printer->getBR("{$data->store_mobile}");
            $content .= $printer->getBR("{$data->store_address}");
            $content .= $printer->getDivide();
        }
        if ($data->remark) {
            $content .= $printer->getRemarkText("备注：{$data->remark}");
        } else {
            foreach ($data->order_form as $ofItem) {
                if (isset($ofItem['key']) && $ofItem['key'] == 'text' || $ofItem['key'] == 'textarea') {
                    $label = isset($ofItem['label']) ? $ofItem['label'] : '';
                    $value = isset($ofItem['value']) ? $ofItem['value'] : '';
                    if ($label && $value) {
                        $content .= $printer->getRemarkText("{$label}：{$value}");
                    }
                }
            }
        }
        return $content;
    }

    public function getContentByArray()
    {
        $data = $this->data;

        $show_type = \yii\helpers\BaseJson::decode($this->printer->show_type);
        switch ($this->printer->big) {
            case 1:
                $otherHandle = 'b';
                break;
            case 2:
                $otherHandle = 'dB';
                break;
            default:
                $otherHandle = 'bR';
                break;
        }
        $content = [
            [
                'handle' => 'times',
                'content' => ''
            ],
            [
                'handle' => 'centerBold',
                'content' => $data->mall_name
            ],
            [
                'handle' => 'bR',
                'content' => '订单类型：' . $data->order_type
            ],
            [
                'handle' => 'bR',
                'content' => '支付方式：' . $data->pay_type
            ],
            [
                'handle' => 'bR',
                'content' => '发货方式：' . $data->send_type_text
            ],
            [
                'handle' => 'bR',
                'content' => '订单号：' . $data->order_no
            ],
            [
                'handle' => 'bR',
                'content' => '下单时间：' . $data->created_at
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
        ];

        foreach ($data->new_goods_list as $info) {
            $content[] = [
                'handle' => 'TableNoAttr',
                'content' => array_merge($info, ['is_goods_no' => $show_type['goods_no']]),
                'show' => $show_type['attr'] == 0 ? 1 : 0
            ];

            $content[] = [
                'handle' => 'TableAttr',
                'content' => array_merge($info, ['is_goods_no' => $show_type['goods_no']]),
                'show' => $show_type['attr'] == 1 ? 1 : 0
            ];

            if (!empty($info['form_data']) && $show_type['form_data'] == 1) {
                $content[] = [
                    'handle' => 'divide',
                    'content' => ''
                ];

                if ($info['form_name']) {
                    $content[] = [
                        'handle' => 'bR',
                        'content' => $info['form_name'],
                    ];
                }

                foreach ($info['form_data'] as $form) {
                    if ($form['value'] && in_array($form['key'], ['text', 'textarea', 'radio', 'date', 'time'])) {
                        $content[] = [
                            'handle' => 'bR',
                            'content' => $form['label'] . '：' . $form['value']
                        ];
                    }

                    if ($form['value'] && in_array($form['key'], ['checkbox'])) {
                        $content[] = [
                            'handle' => 'bR',
                            'content' => $form['label'] . '：' . implode(',', $form['value'])
                        ];
                    }
                }
            }
            $content[] = [
                'handle' => 'divide',
                'content' => ''
            ];
        }

        $content3 = [
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->total_goods_original_price
                    ]
                ],
                'content' => '商品总计：'
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->express_price
                    ]
                ],
                'content' => '运费：',
                'show' => $data->send_type != 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->coupon_discount_price
                    ]
                ],
                'content' => '优惠券优惠：',
                'show' => $data->coupon_discount_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->integral_deduction_price
                    ]
                ],
                'content' => '积分抵扣：',
                'show' => $data->integral_deduction_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->member_discount_price
                    ]
                ],
                'content' => '会员优惠：',
                'show' => $data->member_discount_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => abs($data->back_price)
                    ]
                ],
                'content' => '后台改价：',
                'show' => $data->back_price != 0 ? 1 : 0
            ],
        ];

        $content = array_merge($content, $content3);

        if (count($data->plugin_data) > 0) {
            foreach ($data->plugin_data as $datum) {
                $content[] = [
                    'handle' => 'bR',
                    'content' => $datum['label'] . '：' . $datum['value'],
                    'show' => 1
                ];
            }
        }
        $content2 = [
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->total_pay_price
                    ]
                ],
                'content' => '实际支付：'
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => $otherHandle,
                'content' => ($data->send_type != 1 ? '收货人：' : '联系人：') . $data->name
            ],
            [
                'handle' => $otherHandle,
                'content' => '收货地址：' . $data->address,
                'show' => $data->send_type != 1 ? 1 : 0
            ],
            [
                'handle' => $otherHandle,
                'content' => ($data->send_type != 1 ? '收货人电话：' : '联系人电话：') . $data->mobile
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => '门店信息',
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_name,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_mobile,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_address,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'divide',
                'content' => '',
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'remarkText',
                'content' => '备注：' . $data->remark,
                'show' => $data->remark ? 1 : 0
            ],
        ];
        $content = array_merge($content, $content2);

        return $content;
    }
}
