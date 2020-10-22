<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\page_title;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\Model;
use app\models\Option;

class PageTitleForm extends Model
{
    public function getList()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => CommonAppConfig::getBarTitle()
            ]
        ];
    }

    public function restoreDefault()
    {
        $res = CommonOption::set(
            Option::NAME_PAGE_TITLE,
            PickLinkForm::getCommon()->getTitle(),
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '恢复失败',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '恢复成功',
        ];
    }
}
