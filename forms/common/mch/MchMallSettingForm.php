<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\mch;


use app\models\Model;
use app\plugins\mch\models\MchMallSetting;

class MchMallSettingForm extends Model
{
    public function search()
    {
        $mchMallSetting = MchMallSetting::findOne(['mch_id' => \Yii::$app->user->identity->mch_id]);
        return $mchMallSetting;
    }
}
