<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\community\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\community\models\CommunityRobots;

class RobotEditForm extends Model
{
    public $id;
    public $avatar;
    public $nickname;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['nickname', 'avatar'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $robot = CommunityRobots::findOne($this->id);
            if (!$robot) {
                $robot = new CommunityRobots();
                $robot->mall_id = \Yii::$app->mall->id;
            }

            $robot->nickname = $this->nickname;
            $robot->avatar = $this->avatar;
            $res = $robot->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($robot));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}