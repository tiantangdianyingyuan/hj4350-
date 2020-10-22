<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\GoodsCats;
use app\models\HomeBlock;
use app\models\Option;

class BlockImporting extends BaseImporting
{
    public function import()
    {
        try {
            $newBlock = [];
            if (!is_array($this->v3Data['edit_list'])) {
                return true;
            }
            foreach ($this->v3Data['edit_list'] as $block) {
                if ($block['name'] == 'mch') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'mch',
                        'name' => '好店推荐',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'search') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'search',
                        'name' => '搜索框',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'banner') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'banner',
                        'name' => '轮播图',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'nav') {
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'home_nav',
                        'name' => '导航图标',
                        'relation_id' => 0,
                        'row_num' => '4'
                    ];
                }
                if ($block['name'] == 'banner') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'banner',
                        'name' => '轮播图',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'topic') {
                    $data = $this->v3Data['update_list']['topic'];
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'topic',
                        'name' => '专题',
                        'relation_id' => 0,
                        'label_url' => $data['heated'],
                        'topic_num' => $data['count'],
                        'topic_url' => $data['logo_1'],
                        'topic_url_2' => $data['logo_2'],
                    ];
                }
                if ($block['name'] == 'coupon') {
                    $data = $this->v3Data['update_list']['coupon'];
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'coupon',
                        'name' => '领券中心',
                        'relation_id' => 0,
                        'coupon_not_url' => $data['bg'],
                        'coupon_url' => $data['bg_1'],
                    ];
                }
                if ($block['name'] == 'notice') {
                    $data = $this->v3Data['update_list']['notice'];
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'notice',
                        'name' => '公告',
                        'relation_id' => 0,
                        'notice_bg_color' => $data['bg_color'],
                        'notice_text_color' => $data['color'],
                        'notice_url' => $data['icon'],
                        'notice_name' => $data['name'],
                        'notice_content' => $this->v3Data['notice']
                    ];
                }
                if ($block['name'] == 'notice') {
                    $data = $this->v3Data['update_list']['notice'];
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'notice',
                        'name' => $data['name'],
                        'relation_id' => 0,
                        'notice_bg_color' => $data['bg_color'],
                        'notice_text_color' => $data['color'],
                        'notice_url' => $data['icon'],
                    ];
                }
                if (strstr($block['name'], 'video')) {
                    $arr = explode('-', $block['name']);
                    $data = $this->v3Data['update_list']['video'];
                    $newBlock[] = [
                        'is_edit' => 1,
                        'key' => 'video',
                        'name' => '视频',
                        'relation_id' => 0,
                        'video_pic_url' => isset($data[$arr[1]]) ? $data[$arr[1]]['pic_url'] : '',
                        'video_url' => isset($data[$arr[1]]) ? $data[$arr[1]]['url'] : '',
                    ];
                }
                if ($block['name'] == 'miaosha') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'miaosha',
                        'name' => '秒杀',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'pintuan') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'pintuan',
                        'name' => '拼团',
                        'relation_id' => 0
                    ];
                }
                if ($block['name'] == 'yuyue') {
                    $newBlock[] = [
                        'is_edit' => 0,
                        'key' => 'booking',
                        'name' => '预约',
                        'relation_id' => 0
                    ];
                }
                if (strstr($block['name'], 'block')) {
                    $arr = explode('-', $block['name']);
                    if (isset($arr[1])) {
                        $homeBlock = HomeBlock::findOne($arr[1]);
                        if ($homeBlock) {
                            $newBlock[] = [
                                'key' => 'block',
                                'name' => $homeBlock->name,
                                'relation_id' => $homeBlock->id,
                            ];
                        }
                    }
                }
                if (strstr($block['name'], 'cat')) {
                    $arr = explode('-', $block['name']);
                    if (isset($arr[1])) {
                        $goodsCat = GoodsCats::findOne($arr[1]);
                        if ($goodsCat) {
                            $newBlock[] = [
                                'is_edit' => 0,
                                'key' => 'cat',
                                'name' => $goodsCat->name,
                                'relation_id' => $goodsCat->id,
                            ];
                        }
                    } else {
                        $newBlock[] = [
                            'is_edit' => 0,
                            'key' => 'cat',
                            'name' => '所有分类',
                            'relation_id' => 0,
                        ];
                    }
                }
            }
            $option = CommonOption::set(Option::NAME_HOME_PAGE, $newBlock, $this->mall->id, Option::GROUP_APP);
            if (!$option) {
                throw new \Exception('首页布局数据迁移错误');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}