<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\models\Mall;
use app\models\Model;

class DiyQuickNavForm extends Model
{
    public function getQuickNav()
    {
        $mall = new Mall();
        $mall = $mall->getMallSetting();

        $quickNav['closedPicUrl'] = $mall['setting']['quick_navigation_closed_pic'];
        $quickNav['openedPicUrl'] = $mall['setting']['quick_navigation_opened_pic'];
        $quickNav['navStyle'] = $mall['setting']['quick_navigation_style'];
        $quickNav['navSwitch'] = 1;
        $quickNav['useMallConfig'] = true;
        $quickNav['customerService']['picUrl'] = $mall['setting']['customer_services_pic'];
        $quickNav['customerService']['opened'] = (int)$mall['setting']['is_customer_services'];
        $quickNav['home']['opened'] = (int)$mall['setting']['is_quick_home'];
        $quickNav['home']['picUrl'] = $mall['setting']['quick_home_pic'];
        $quickNav['mApp']['opened'] = (int)$mall['setting']['is_small_app'];
        $quickNav['mApp']['small_app_id'] = $mall['setting']['small_app_id'];
        $quickNav['mApp']['small_app_url'] = $mall['setting']['small_app_url'];
        $quickNav['mApp']['small_app_pic'] = $mall['setting']['small_app_pic'];
        $quickNav['mapNav']['opened'] = (int)$mall['setting']['is_quick_map'];
        $quickNav['mapNav']['picUrl'] = $mall['setting']['quick_map_pic'];
        $quickNav['mapNav']['address'] = $mall['setting']['quick_map_address'];
        $quickNav['mapNav']['latitude'] = $mall['setting']['latitude'];
        $quickNav['mapNav']['longitude'] = $mall['setting']['longitude'];
        $quickNav['tel']['opened'] = (int)$mall['setting']['is_dial'];
        $quickNav['tel']['picUrl'] = $mall['setting']['dial_pic'];
        $quickNav['tel']['number'] = $mall['setting']['contact_tel'];
        $quickNav['web']['opened'] = (int)$mall['setting']['is_web_service'];
        $quickNav['web']['picUrl'] = $mall['setting']['web_service_pic'];
        $quickNav['web']['url'] = $mall['setting']['web_service_url'];

        return $quickNav;
    }
}
