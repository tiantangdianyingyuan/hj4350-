<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/15
 * Time: 10:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\theme_color;


use app\core\response\ApiCode;
use app\models\MallSetting;
use app\models\Model;

class ThemeColorForm extends Model
{
    private function getDefault()
    {
        $path = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/theme-color/';
        $arr = [
            [
                'icon' => 'classic-red',
                'name' => '默认风格',
                'color' => [
                    'main' => '#ff4544',
                    'secondary' => '#f39800'
                ],
                'key' => 'a',
            ],
            [
                'icon' => 'vibrant-yellow',
                'name' => '活力黄',
                'color' => [
                    'main' => '#fcc600',
                    'secondary' => '#555555'
                ],
                'key' => 'b',
            ],
            [
                'icon' => 'romantic-powder',
                'name' => '浪漫粉',
                'color' => [
                    'main' => '#ff547b',
                    'secondary' => '#ffe6e8'
                ],
                'key' => 'c',
            ],
            [
                'icon' => 'streamer-gold',
                'name' => '流光金',
                'color' => [
                    'main' => '#ddb766',
                    'secondary' => '#f0ebd8'
                ],
                'key' => 'd',
            ],
            [
                'icon' => 'elegant-purple',
                'name' => '优雅紫',
                'color' => [
                    'main' => '#7783ea',
                    'secondary' => '#e9ebff'
                ],
                'key' => 'e',
            ],
            [
                'icon' => 'taste-red',
                'name' => '品味红',
                'color' => [
                    'main' => '#ff4544',
                    'secondary' => '#555555'
                ],
                'key' => 'f',
            ],
            [
                'icon' => 'fresh-green',
                'name' => '清新绿',
                'color' => [
                    'main' => '#63be72',
                    'secondary' => '#e1f4e3'
                ],
                'key' => 'g',
            ],
            [
                'icon' => 'business-blue',
                'name' => '商务蓝',
                'color' => [
                    'main' => '#4a90e2',
                    'secondary' => '#dbe9f9'
                ],
                'key' => 'h',
            ],
            [
                'icon' => 'pure-black',
                'name' => '纯粹黑',
                'color' => [
                    'main' => '#333333',
                    'secondary' => '#dedede'
                ],
                'key' => 'i',
            ],
            [
                'icon' => 'passionate-red',
                'name' => '热情红',
                'color' => [
                    'main' => '#ff4544',
                    'secondary' => '#ffdada'
                ],
                'key' => 'j',
            ],
        ];
        foreach ($arr as &$item) {
            $newItem = [
                'icon' => $path . $item['icon'] . '-icon.png',
                'pic_list' => [
                    $path . $item['icon'] . '-pic-1.png',
                    $path . $item['icon'] . '-pic-2.png',
                    $path . $item['icon'] . '-pic-3.png',
                ],
                'is_select' => false
            ];
            $item = array_merge($item, $newItem);
        }
        unset($item);
        return $arr;
    }

    private function setSelect($option)
    {
        $default = $this->getDefault();
        foreach ($default as &$item) {
            if ($item['key'] == $option) {
                $item['is_select'] = true;
                break;
            }
        }
        unset($item);
        return $default;
    }

    public function getList()
    {
        $mall = \Yii::$app->mall;
        $option = $mall->getMallSettingOne('theme_color');
        $list = $this->setSelect($option);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public $theme_color;

    public function rules()
    {
        return [
            [['theme_color'], 'trim'],
            [['theme_color'], 'string'],
            [['theme_color'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'theme_color' => '商城风格'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $flag = false;
            $default = $this->getDefault();
            foreach ($default as $item) {
                if ($item['key'] == $this->theme_color) {
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                throw new \Exception('没找到对应的商城风格');
            }

            $mallSetting = MallSetting::findOne(['key' => 'theme_color', 'mall_id' => \Yii::$app->mall->id]);
            if (!$mallSetting) {
                $mallSetting = new MallSetting();
                $mallSetting->key = 'theme_color';
                $mallSetting->mall_id = \Yii::$app->mall->id;
            }
            $mallSetting->value = $this->theme_color;
            if (!$mallSetting->save()) {
                throw new \Exception($this->getErrorMsg($mallSetting));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
