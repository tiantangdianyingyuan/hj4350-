<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common;


use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\models\Mall;
use app\models\Model;
use app\plugins\pintuan\models\PintuanSetting;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public function search()
    {
        $setting = PintuanSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
        $default = $this->getDefault();

        if (!$setting) {
            $setting = $this->getDefault();
        } else {
            $setting['rules'] = $setting['rules'] ? \Yii::$app->serializer->decode($setting['rules']) : [];
            $setting['payment_type'] = $setting['payment_type'] ?
                \Yii::$app->serializer->decode($setting['payment_type']) : ['online_pay'];
            $setting['goods_poster'] = $setting['goods_poster'] ?
                \Yii::$app->serializer->decode($setting['goods_poster']) : CommonOption::getPosterDefault();

            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
        }
        $setting = ArrayHelper::toArray($setting);
        $setting['advertisement'] = $setting['advertisement'] ? json_decode($setting['advertisement'], true) : [];

        if ($setting['advertisement']) {

            // TODO 兼容
            if (!isset($setting['advertisement']['list'])) {
                $setting['advertisement'] = [
                    'list' => []
                ];
            }
            $setting['advertisement']['style'] = $setting['advertisement']['current_style_id'] - 1;

            // 样式一
            if (count($setting['advertisement']['list']) == 1 && $setting['advertisement']['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 0;
                $setting['advertisement']['value'] = [
                    [
                        'width' => '100%',
                        'height' => 'auto',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ]
                ];
            }

            if (count($setting['advertisement']['list']) == 2 && $setting['advertisement']['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 1;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                ];
            }
            if (count($setting['advertisement']['list']) == 3 && $setting['advertisement']['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 2;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%',
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 2),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 2),
                    ],
                ];
            }
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 3;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%',
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 2),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 2),
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (525 * 100 / 750) . '%',
                        'top' => '50%',
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 3),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 3),
                    ],
                ];
            }

            // 样式二
            if (count($setting['advertisement']['list']) == 2 && $setting['advertisement']['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 4;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                ];
            }
            if (count($setting['advertisement']['list']) == 3 && $setting['advertisement']['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 5;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (250 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (500 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 2),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 2),
                    ],
                ];
            }
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 2) {
                $block['style'] = 187.5;
                $block['status'] = 6;
                $setting['advertisement']['value'] = [
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '25%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '50%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 2),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 2),
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '75%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 3),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 3),
                    ],
                ];
            }

            // 样式三
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 3) {
                $block['style'] = 372;
                $block['status'] = 7;
                $setting['advertisement']['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 0),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 0),
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0,
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 1),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 1),
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => '50%',
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 2),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 2),
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => '50%',
                        'zIndex' => 11,
                        'link' => $this->getNewData($setting['advertisement']['list'], 3),
                        'pic_url' => $this->getPicUrl($setting['advertisement']['list'], 3),
                    ],
                ];
            }


            // 样式一
            if (count($setting['advertisement']['list']) == 1 && $setting['advertisement']['type'] == 1) {
                $setting['advertisement']['list'][0]['style'] = 'width:750rpx;height:360rpx;top:0;left:0;';
            }

            if (count($setting['advertisement']['list']) == 2 && $setting['advertisement']['type'] == 1) {
                $setting['advertisement']['list'][0]['style'] = 'width:300rpx;height:360rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:450rpx;height:360rpx;top:0;left:300rpx;';
            }
            if (count($setting['advertisement']['list']) == 3 && $setting['advertisement']['type'] == 1) {
                $setting['advertisement']['list'][0]['style'] = 'width:300rpx;height:360rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:450rpx;height:180rpx;top:0;left:300rpx;';
                $setting['advertisement']['list'][2]['style'] = 'width:450rpx;height:180rpx;top:180rpx;left:300rpx;';
            }
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 1) {
                $setting['advertisement']['list'][0]['style'] = 'width:300rpx;height:360rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:450rpx;height:180rpx;top:0;left:300rpx;';
                $setting['advertisement']['list'][2]['style'] = 'width:225rpx;height:180rpx;top:180rpx;left:300rpx;';
                $setting['advertisement']['list'][3]['style'] = 'width:225rpx;height:180rpx;top:180rpx;left:525rpx;';
            }

            // 样式二
            if (count($setting['advertisement']['list']) == 2 && $setting['advertisement']['type'] == 2) {
                $setting['advertisement']['list'][0]['style'] = 'width:375rpx;height:240rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:375rpx;height:240rpx;top:0;left:375rpx;';
            }
            if (count($setting['advertisement']['list']) == 3 && $setting['advertisement']['type'] == 2) {
                $setting['advertisement']['list'][0]['style'] = 'width:250rpx;height:240rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:250rpx;height:240rpx;top:0;left:250rpx;';
                $setting['advertisement']['list'][2]['style'] = 'width:250rpx;height:240rpx;top:0;left:500rpx;';
            }
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 2) {
                $setting['advertisement']['list'][0]['style'] = 'width:188rpx;height:188rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:188rpx;height:188rpx;top:0;left:188rpx;';
                $setting['advertisement']['list'][2]['style'] = 'width:188rpx;height:188rpx;top:0;left:376rpx;';
                $setting['advertisement']['list'][3]['style'] = 'width:188rpx;height:188rpx;top:0;left:564rpx;';
            }

            // 样式三
            if (count($setting['advertisement']['list']) == 4 && $setting['advertisement']['type'] == 3) {
                $setting['advertisement']['list'][0]['style'] = 'width:375rpx;height:186rpx;top:0;left:0;';
                $setting['advertisement']['list'][1]['style'] = 'width:375rpx;height:186rpx;top:0;left:375rpx;';
                $setting['advertisement']['list'][2]['style'] = 'width:375rpx;height:186rpx;top:186rpx;left:0;';
                $setting['advertisement']['list'][3]['style'] = 'width:375rpx;height:186rpx;top:186rpx;left:375rpx;';
            }
        }

        // 修改数据格式
        if (isset($setting['advertisement']['list']) && is_array($setting['advertisement']['list'])) {
            foreach ($setting['advertisement']['list'] as &$item) {
                if (!isset($item['open_type'])) {
                    $item['open_type'] = '';
                }
                if (isset($item['style']) && $item['style']) {
                    $styleArr = explode(';', $item['style']);
                    $newStyleArr = [];
                    foreach ($styleArr as $styleItem) {
                        $newStyleItem = explode(':', $styleItem);
                        if (count($newStyleItem) == 2) {
                            $newStyleArr[$newStyleItem[0]] = $newStyleItem[1];
                        }
                    }

                    $item['width'] = isset($newStyleArr['width']) ? $newStyleArr['width'] : 0;
                    $item['height'] = isset($newStyleArr['height']) ? $newStyleArr['height'] : 0;
                    $item['top'] = isset($newStyleArr['top']) ? $newStyleArr['top'] : 0;
                    $item['left'] = isset($newStyleArr['left']) ? $newStyleArr['left'] : 0;
                } else {
                    $item['width'] = 0;
                    $item['height'] = 0;
                    $item['top'] = 0;
                    $item['left'] = 0;
                }
            }
        }


        return $setting;
    }


    private function getNewData($data, $index) {
        return [
            'id' => $index + 1,
            'open_type' => isset($data[$index]['open_type']) ? $data[$index]['open_type'] : '',
            'value' => isset($data[$index]['link_url']) ? $data[$index]['link_url'] : '',
            'new_link_url' => isset($data[$index]['new_link_url']) ? $data[$index]['new_link_url'] : '',
            'icon' => '',
            'name' => '',
        ];
    }

    private function getPicUrl($data, $index)
    {
        return isset($data[$index]['pic_url']) ? $data[$index]['pic_url'] : '';
    }

    public function getDefault()
    {
        return [
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'advertisement' => [],
            'is_advertisement' => 0,
            'rules' => [],
            'send_type' => ['express', 'offline'],
            'payment_type' => ['online_pay'],
            'goods_poster' => CommonOption::getPosterDefault(),
        ];
    }
}
