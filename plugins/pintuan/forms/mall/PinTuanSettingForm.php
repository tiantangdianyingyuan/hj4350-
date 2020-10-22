<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\pintuan\forms\common\v2\BannerListForm;
use app\plugins\pintuan\forms\common\v2\CommonOption;
use app\plugins\pintuan\forms\common\v2\SettingForm;

class PinTuanSettingForm extends Model
{
    public function getSetting()
    {
        $setting = (new SettingForm())->search();
        $poster = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        $poster['price']['text'] = CommonOption::getPosterDefault()['price']['text'];
        unset($setting['goods_poster']);

        $bannerList = (new BannerListForm())->search();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $setting,
                'poster' => $poster,
                'banner_list' => $bannerList
            ]
        ];
    }
}
