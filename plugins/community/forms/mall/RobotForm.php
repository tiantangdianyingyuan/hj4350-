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

class RobotForm extends Model
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'id'], 'integer'],
            [['keyword'], 'trim'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = CommunityRobots::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'nickname', $this->keyword]);
        }

        $list = $query->orderBy(['created_at' => SORT_DESC])->page($pagination)->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $robot = CommunityRobots::findOne($this->id);

            if (!$robot) {
                throw new \Exception('机器人不存在');
            }

            $robot->is_delete = 1;
            $res = $robot->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($robot));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "删除成功"
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}