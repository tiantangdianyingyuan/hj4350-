<?php

namespace app\plugins\stock\forms\common;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\stock\models\StockBonusLog;
use app\plugins\stock\models\StockCashLog;
use app\plugins\stock\models\StockUserInfo;


class CommonBonus extends Model
{
    public $bonus_id;
    public $user_id;
    public $keyword;
    public $keyword_1;
    public $level_name;


    public function rules()
    {
        return [
            [['bonus_id', 'user_id'], 'integer'],
            [['keyword', 'keyword_1', 'level_name'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StockCashLog::find()->alias('s')
            ->where(['s.is_delete' => 0, 's.type' => 1])
            ->andWhere(['>', 's.bonus_id', 0]);

        if ($this->bonus_id) {
            $model->andWhere(['s.bonus_id' => $this->bonus_id]);
        } elseif ($this->user_id) {
            $model->andWhere(['s.user_id' => $this->user_id]);
        }

        //等级名列表
        if (!$this->user_id) {
            $model1 = clone $model;
            $level_list = $model1->select('s.level_name')->groupBy('s.level_name')->asArray()->all();
        } else {
            $level_list = [];//小程序接口不提供等级列表
        }

        //分红详情列表
        $model2 = clone $model;
        $model2->select(['s.user_id', 's.level_name', 's.bonus_rate', 's.price', 's.bonus_id'])->with(['user.userInfo', 'stockUser', 'bonus']);
        switch ($this->keyword) {
            case 1:
                $model2->leftJoin(['u' => User::tableName()], 'u.id = s.user_id')
                    ->andWhere(['like', 'u.nickname', $this->keyword_1]);
                break;
            case 2:
                $model2->leftJoin(['su' => StockUserInfo::tableName()], 'su.user_id = s.user_id')
                    ->andWhere(['like', 'su.name', $this->keyword_1]);
                break;
            case 3:
                $model2->leftJoin(['su' => StockUserInfo::tableName()], 'su.user_id = s.user_id')
                    ->andWhere(['like', 'su.phone', $this->keyword_1]);
                break;
        }
        if ($this->level_name) {
            $model2->andWhere(['s.level_name' => $this->level_name]);
        }
        $list = $model2->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            $item['nickname'] = $item['user']['nickname'];
            $item['avatar'] = $item['user']['userInfo']['avatar'];
            $item['platform'] = $item['user']['userInfo']['platform'];
            $item['name'] = $item['stockUser']['name'];
            $item['phone'] = $item['stockUser']['phone'];
            $item['bonus_type'] = $item['bonus']['bonus_type'];
            $item['start_time'] = str_replace('-', '.', substr($item['bonus']['start_time'], 0, 10));
            $item['end_time'] = str_replace('-', '.', substr($item['bonus']['end_time'], 0, 10));
            $item['order_num'] = $item['bonus']['order_num'];
            unset($item['user']);
            unset($item['stockUser']);
            unset($item['bonus']);
        }
        if ($this->bonus_id) {
            $bonus_data = StockBonusLog::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->bonus_id]);
            $bonus_data->start_time = substr($bonus_data->start_time, 0, 10);
            $bonus_data->end_time = substr($bonus_data->end_time, 0, 10);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'bonus_data' => $bonus_data ?? (object)[],
                'level_list' => $level_list,
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
