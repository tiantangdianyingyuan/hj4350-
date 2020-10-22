<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\check_in\forms\mall;


use app\core\response\ApiCode;
use app\models\User;
use app\plugins\check_in\forms\Model;
use app\plugins\check_in\models\CheckInSign;

class CheckLogForm extends Model
{
    public $page;

    public $time;
    public $keyword;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword'], 'string'],
            [['time'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = CheckInSign::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            if ($this->keyword) {
                $userIds = User::find()->where([
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['is_delete' => 0],
                    ['like', 'nickname', $this->keyword],
                ])->select('id');
                $query->andWhere(['user_id' => $userIds]);
            }
            if (!empty($this->time)) {
                $query->andWhere([
                    'AND',
                    ['>=', 'created_at', current($this->time)],
                    ['<=', 'created_at', next($this->time)],
                ]);
            }

            $list = $query->with('user.userInfo')
                ->orderBy(['id' => SORT_DESC])
                ->page($pagintaion)
                ->asArray()
                ->all();

            foreach ($list as &$item) {
                $item['nickname'] = $item['user']['nickname'];
                $item['platform'] = $item['user']['userInfo']['platform'];
                $item['avatar'] = $item['user']['userInfo']['avatar'];
                unset($item['user']);
            }
            unset($item);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagintaion,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }

    }
}