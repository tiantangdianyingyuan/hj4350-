<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 10:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;
use yii\helpers\ArrayHelper;

class ApplyDataForm extends Model
{
    public function getData()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig(\Yii::$app->user->id);
        $res = [];
        if ($middleman && $middleman->delete_first_show == 1) {
            $res = $common->getMiddleman($middleman);
            if (in_array($middleman->status, [2, 3])) {
                $middleman->delete_first_show = 0;
                $middleman->save();
            }
        }
        return $this->success([
            'setting' => $setting,
            'middleman' => $res
        ]);
    }
}
