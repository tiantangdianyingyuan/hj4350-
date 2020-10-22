<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\api;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\plugins\gift\forms\common\CommonGift;
use app\models\Model;

class GiftSettingForm extends Model
{
    public function getList()
    {
        $setting = CommonGift::getSetting();
        $data['title'] = $setting['title'];
        $data['type'] = $setting['type'];
        $data['bless_word'] = $setting['bless_word'];
        $data['ask_gift'] = $setting['ask_gift'];
        $data['explain'] = $setting['explain'];
        $data['background'] = $setting['background'];
        $data['theme'] = $setting['theme'];
        $data['big_gift_pic'] = $setting['poster']['pic']['pic_url'];
        $data['template_message_captain_gift_convert'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
            'gift_convert'
        ]);
        $data['template_message_captain_gift_form_user'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
            'gift_form_user'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $data
        ];
    }
}
