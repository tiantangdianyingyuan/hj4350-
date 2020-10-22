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

class NavbarImporting extends BaseImporting
{
    public function import()
    {
        try {
            $newNavs = [];
            foreach ($this->v3Data['navs'] as $nav) {
                $pickLink = PickLink::getNewLink($nav['url']);
                $arr = [];
                $arr['active_color'] = $nav['active_color'];
                $arr['active_icon'] = $nav['active_icon'];
                $arr['color'] = $nav['color'];
                $arr['text'] = $nav['text'];
                $arr['icon'] = $nav['icon'];
                $arr['open_type'] = PickLinkForm::OPEN_TYPE_1;
                $arr['url'] = $pickLink['url'];
                $newNavs[] = $arr;
            }

            $data = [
                'bottom_background_color' => '#FFFFFF',
                'top_background_color' => $this->v3Data['backgroundColor'],
                'top_text_color' => $this->v3Data['frontColor'],
                'shadow' => true,
                'navs' => $newNavs
            ];

            $res = CommonOption::set(Option::NAME_NAVBAR, $data, $this->mall->id, Option::GROUP_APP);
            if (!$res) {
                throw new \Exception('底部导航数据迁移失败');
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}