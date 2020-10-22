<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_page;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonHomeBlock;
use app\forms\common\CommonOption;
use app\forms\common\goods\CommonGoodsCats;
use app\models\Model;
use app\models\Option;

class HomePageForm extends Model
{
    public function getDetail()
    {
        $option = CommonAppConfig::getHomePageConfig();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
            ]
        ];
    }

    public function getOption()
    {
        $homeBlock = CommonHomeBlock::getAll();
        $cats = CommonGoodsCats::allParentCat();
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        $storeBlock = [
            [
                'key' => 'search',
                'name' => '搜索框',
            ],
            [
                'key' => 'banner',
                'name' => '轮播图',
            ],
            [
                'key' => 'home_nav',
                'name' => '导航图标'
            ],
            [
                'key' => 'video',
                'permission_key' => 'video',
                'name' => '视频',
            ],
            [
                'key' => 'notice',
                'name' => '公告'
            ],
            [
                'key' => 'topic',
                'permission_key' => 'topic',
                'name' => '专题'
            ],
            [
                'key' => 'coupon',
                'permission_key' => 'coupon',
                'name' => '领券中心'
            ],
        ];

        $newOption = [];
        $normal = [];
        foreach ($storeBlock as $item) {
            if (isset($item['permission_key']) && $item['permission_key'] && !isset($permissionFlip[$item['permission_key']])) {
                continue;
            }
            $arr = [
                'key' => $item['key'],
                'name' => $item['name'],
                'relation_id' => 0,
                'is_edit' => 0
            ];
            if (isset($item['permission_key'])) {
                $arr['permission_key'] = $item['permission_key'];
            }

            if ($item['key'] === 'notice') {
                $arr['is_edit'] = 1;
                $arr['notice_url'] = '';
                $arr['notice_bg_color'] = '#ED7E78';
                $arr['notice_text_color'] = '#FFFFFF';
            }
            if ($item['key'] === 'topic') {
                $arr['is_edit'] = 1;
                $arr['topic_num'] = '1';
                $arr['topic_url'] = '';
                $arr['topic_url_2'] = '';
                $arr['label_url'] = '';
            }
            if ($item['key'] === 'coupon') {
                $arr['is_edit'] = 1;
                $arr['coupon_url'] = '';
                $arr['coupon_not_url'] = '';
            }
            if ($item['key'] === 'video') {
                $arr['is_edit'] = 1;
                $arr['video_url'] = '';
                $arr['video_pic_url'] = '';
            }
            if ($item['key'] === 'home_nav') {
                $arr['is_edit'] = 1;
                $arr['row_num'] = '4';
            }
            $normal[] = $arr;
        }
        $newOption[] = [
            'key' => 'normal',
            'name' => '常用',
            'list' => $normal
        ];

        $catList = [];
        if (isset($cats['list']) && is_array($cats['list'])) {
            $catList[] = [
                'key' => 'cat',
                'name' => '所有分类',
                'relation_id' => 0,
                'is_edit' => 0
            ];
            foreach ($cats['list'] as $item) {
                $catList[] = [
                    'key' => 'cat',
                    'name' => $item['name'],
                    'relation_id' => $item['id'],
                    'is_edit' => 0
                ];
            }
        }
        $newOption[] = [
            'key' => 'cat',
            'name' => '商品分类',
            'list' => $catList
        ];

        $blockList = [];
        if (isset($homeBlock['list']) && is_array($homeBlock['list'])) {
            foreach ($homeBlock['list'] as $item) {
                $blockList[] = [
                    'key' => 'block',
                    'name' => $item['name'],
                    'relation_id' => $item['id'],
                ];
            }
        }
        $newOption[] = [
            'key' => 'block',
            'name' => '图片魔方',
            'list' => $blockList
        ];
        $bgUrl = $this->getBgUrl();
        $corePlugins = \Yii::$app->plugin->getList();

        $pluginList = [];
        foreach ($corePlugins as $corePlugin) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($corePlugin->name);
            } catch (\Exception $exception) {
                continue;
            }
            $res = $plugin->getHomePage('mall');
            if (!$res) {
                continue;
            }

            // 判断插件权限
            $sign = isset($res['key']) && $res['key'] && !isset($permissionFlip[$res['key']]) ? false : true;
            if ($sign) {
                foreach ($res['list'] as $lItem) {
                    $lItem['permission_key'] = $lItem['key'];
                    $pluginList[] = $lItem;
                }
                $bgUrl = array_merge($bgUrl, $res['bgUrl']);
            }
        }
        $newOption[] = [
            'key' => 'plugin',
            'name' => '插件',
            'list' => $pluginList
        ];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newOption,
                'modules' => $bgUrl
            ]
        ];
    }

    public function getDefault()
    {
        return [
            [
                'key' => 'search',
                'name' => '搜索框',
                'relation_id' => 0,
            ],
            [
                'key' => 'banner',
                'name' => '轮播图',
                'relation_id' => 0,
            ],
            [
                'key' => 'home_nav',
                'name' => '导航图标',
                'relation_id' => 0,
            ],
        ];
    }

    public function getBgUrl()
    {
        $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
        return [
            'search' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/search-bg.png'
            ],
            'banner' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/banner-bg.png'
            ],
            'block' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/block-bg.png'
            ],
            'home_nav' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/home-nav-bg.png'
            ],
            'cat' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/cat-bg.png'
            ],
            'video' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/video-bg.png',
            ],
            'notice' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/notice-bg.png',
            ],
            'topic' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/topic-bg.png',
            ],
            'coupon' => [
                'bg_url' => $baseUrl . '/statics/img/mall/home_block/coupon-bg.png',
            ],
        ];
    }
}
