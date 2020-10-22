<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\region\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\region\forms\common\CommonRegion;
use Yii;

class LevelForm extends Model
{
    public $city_id;
    public $level;

    public function rules()
    {
        return [
            [['level'], 'required'],
            [['level'], 'integer'],
            [['level'], 'in', 'range' => [1, 2]],
            [['city_id'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '等级',
            'city_id' => '市'
        ];
    }

    public function search()
    {
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = Yii::$app->db->beginTransaction();
        try {
            $common = CommonRegion::getInstance();
            $common->user_id = Yii::$app->user->id;
            $common->city_id = $this->city_id;
            $common->level = $this->level;
            $common->levelUp();

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '区域升级申请成功',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

}
