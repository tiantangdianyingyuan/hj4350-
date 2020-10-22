<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 10:47
 */

namespace app\plugins\region\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\region\forms\common\CommonRegion;

class IndexForm extends Model
{
    public $province_id;
    public $level;

    public function rules()
    {
        return [
            [['province_id'], 'required'],
            [['level'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'province_id' => 'province_id',
        ];
    }

    public function search()
    {
        try {
            $common = CommonRegion::getInstance();
            $common->user_id = \Yii::$app->user->id;
            $common->province_id = $this->province_id;
            $info = $common->index($this->level);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $info
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
