<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 15:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\dianqilai\forms\common;


use app\forms\common\CommonOption;
use app\models\Mall;
use app\models\Model;

/**
 * Class CommonSetting
 * @package app\plugins\dianqilai\forms\common
 * @property Mall $mall
 */
class CommonSetting extends Model
{
    public $mall;

    public static function getCommon($mall = null)
    {
        $model = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $model->mall = $mall;
        return $model;
    }

    public function getToken()
    {
        $token = CommonOption::get('dianqilai_token', $this->mall->id, 'plugins', null);
        if (!$token) {
            $token = $this->setToken();
        }
        return $token;
    }

    public function setToken()
    {
        $token = \Yii::$app->security->generateRandomString();
        $res = CommonOption::set('dianqilai_token', $token, $this->mall->id, 'plugins');
        return $token;
    }
}
