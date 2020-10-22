<?php

namespace app\plugins\region\forms\common;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\region\models\RegionBonusLog;
use app\plugins\region\models\RegionCashLog;
use app\plugins\region\models\RegionUserInfo;


class CommonBonus extends Model
{
    public $bonus_id;
    public $user_id;
    public $keyword;
    public $keyword_1;
    public $level_name;
    public $address_id;


    public function rules()
    {
        return [
            [['bonus_id', 'user_id'], 'integer'],
            [['keyword', 'keyword_1', 'level_name'], 'string'],
            [['address_id'], 'safe']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = RegionCashLog::find()->alias('s')
            ->where(['s.is_delete' => 0, 's.type' => 1])
            ->andWhere(['>', 's.bonus_id', 0]);

        if ($this->bonus_id) {
            $model->andWhere(['s.bonus_id' => $this->bonus_id]);
        } elseif ($this->user_id) {
            $model->andWhere(['s.user_id' => $this->user_id]);
        }

        //分红详情列表
        $model->select(['s.user_id', 's.level_name', 's.bonus_rate', 's.price', 's.bonus_id', 's.level_id'])->with(
            ['user.userInfo', 'regionUser', 'bonus']
        );
        switch ($this->keyword) {
            case 1:
                $model->leftJoin(['u' => User::tableName()], 'u.id = s.user_id')
                    ->andWhere(['like', 'u.nickname', $this->keyword_1]);
                break;
            case 2:
                $model->leftJoin(['su' => RegionUserInfo::tableName()], 'su.user_id = s.user_id')
                    ->andWhere(['like', 'su.name', $this->keyword_1]);
                break;
            case 3:
                $model->leftJoin(['su' => RegionUserInfo::tableName()], 'su.user_id = s.user_id')
                    ->andWhere(['like', 'su.phone', $this->keyword_1]);
                break;
        }
        if ($this->level_name) {
            $model->andWhere(['s.level_name' => $this->level_name]);
        }

        if ($this->address_id) {
            $model->andWhere(
                [
                    'or',
                    ['province_id' => $this->address_id],
                    ['city_id' => $this->address_id],
                    ['district_id' => $this->address_id]
                ]
            );
        }
        $list = $model->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            $item['nickname'] = $item['user']['nickname'];
            $item['avatar'] = $item['user']['userInfo']['avatar'];
            $item['platform'] = $item['user']['userInfo']['platform'];
            $item['name'] = $item['regionUser']['name'];
            $item['phone'] = $item['regionUser']['phone'];
            $item['level'] = $item['level_id'] == 1 ? '省代理' : ($item['level_id'] == 2 ? '市代理' : '区代理');
            $item['bonus_type'] = $item['bonus']['bonus_type'];
            $item['start_time'] = str_replace('-', '.', substr($item['bonus']['start_time'], 0, 10));
            $item['end_time'] = str_replace('-', '.', substr($item['bonus']['end_time'], 0, 10));
            $item['order_num'] = $item['bonus']['order_num'];
            unset($item['user']);
            unset($item['regionUser']);
            unset($item['bonus']);
        }
        if ($this->bonus_id) {
            $bonus_data = RegionBonusLog::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->bonus_id]);
            $bonus_data->start_time = substr($bonus_data->start_time, 0, 10);
            $bonus_data->end_time = substr($bonus_data->end_time, 0, 10);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'bonus_data' => $bonus_data ?? (object)[],
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
