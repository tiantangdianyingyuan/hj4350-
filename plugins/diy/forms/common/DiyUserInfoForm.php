<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\common;

use app\forms\common\CommonOption;
use app\forms\mall\user_center\UserCenterForm;
use app\models\MallMembers;
use app\models\Model;
use app\models\Option;

class DiyUserInfoForm extends Model
{
    public function getInfo()
    {
        $level_name = '普通用户';
        $level_pic = '';
        if (\Yii::$app->user->identity->identity->member_level != 0) {
            $level = MallMembers::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'level' => \Yii::$app->user->identity->identity->member_level,
                'status' => 1, 'is_delete' => 0
            ]);
            if ($level) {
                $level_name = $level->name;
                $level_pic = $level->pic_url;
            }
        } else {
            $option = CommonOption::get(
                Option::NAME_USER_CENTER,
                \Yii::$app->mall->id,
                Option::GROUP_APP,
                (new UserCenterForm())->getDefault()
            );
            $level_pic = $option['member_pic_url'];
        }
        $arr = [
            'nickname' => \Yii::$app->user->identity->nickname,
            'level_name' => $level_name,
            'level_pic' => $level_pic,
            'avatar' => \Yii::$app->user->identity->userInfo->avatar,
        ];
        return $arr;
    }
}
