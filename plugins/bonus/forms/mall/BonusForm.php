<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\bonus\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainRelation;
use app\plugins\bonus\models\BonusOrderLog;

class BonusForm extends Model
{
    public $captain_id;

    public $order_by;
    public $keyword;
    public $keyword_1;

    public function rules()
    {
        return [
            [['captain_id', 'keyword_1'], 'integer'],
            [['keyword', 'order_by'], 'string'],
            ['order_by', 'default', 'value' => 'bonus_price desc']
        ];
    }

    public function teamBonus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $captain = BonusCaptain::find()->select('user_id,name,mobile,all_member,level')->where(['user_id' => $this->captain_id, 'status' => 1, 'is_delete' => 0])
            ->with('user.userInfo')->with('level')->asArray()->one();
        if (empty($captain)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该用户还不是队长'
            ];
        }
        $captain['nickname'] = '';
        $captain['avatar'] = '';
        if (!empty($captain['user'])) {
            $captain['nickname'] = $captain['user']['nickname'];
            $captain['avatar'] = $captain['user']['userInfo']['avatar'];
        }
        unset($captain['user']);

        $model = BonusCaptainRelation::find()->alias('bc')
            ->select('bc.id,COALESCE(SUM(CASE WHEN bo.from_user_id = bc.user_id THEN bo.bonus_price ELSE 0 END),0) AS `bonus_price`,bc.user_id')
            ->leftJoin(['bo' => BonusOrderLog::tableName()], 'bo.to_user_id = bc.captain_id and bo.status = 1 and  bo.is_delete = 0')
            ->andWhere(['bc.captain_id' => $this->captain_id, 'bc.is_delete' => 0]);

        //搜索条件
        if ($this->keyword || $this->keyword == 0) {
            $infomodel = [];
            switch ($this->keyword_1) {
                case 1://昵称
                    $infomodel = User::find()->where(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->asArray()->all();
                    break;
                case 2://手机号
                    $infomodel = User::find()->where(['like', 'mobile', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->asArray()->all();
                    break;
                case 3://用户ID
                    $infomodel = User::find()->where(['like', 'id', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->asArray()->all();
                    break;
            }
            $ids = [];
            foreach ($infomodel as $k => $item) {
                $ids[$k] = $item['id'];
            }
            $model->andWhere(['in', 'bc.user_id', $ids]);
        }

        //排序，增加默认用户ID排序，防止排序字段数据相同时，每次请求返回数据排序随机
        $model->orderBy($this->order_by . ',user_id');

        $list = $model->groupBy(['bc.user_id'])
            ->page($pagination)
            ->with('user.userInfo')
            ->asArray()
            ->all();

        foreach ($list as $key => $value) {
            $list[$key]['user_id'] = $value['user']['id'];
            $list[$key]['nickname'] = $value['user']['nickname'];
            $list[$key]['mobile'] = $value['user']['mobile'];
            $list[$key]['avatar'] = $value['user']['userInfo']['avatar'];
            unset($list[$key]['user']);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'captain' => $captain,
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    //统计
    public function historicalData()
    {
        $query = BonusOrderLog::find()->where(['status' => 1, 'is_delete' => 0, 'to_user_id' => $this->captain_id])->orderBy('time');

        //昨日
        $day_query = clone $query;
        $day_query->select("DATE_FORMAT(`created_at`, '%H') AS `time`,COALESCE(SUM(`bonus_price`),0) as `bonus_price`")
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-1 day')) . ' 00:00:00'])
            ->andWhere(['<=', 'created_at', date('Y-m-d', strtotime('-1 day')) . ' 23:59:59']);
        $day_data_query = clone $day_query;
        $day_data = $day_data_query->one();
        $day_list = $this->hour_24($day_query->groupBy('time')->asArray()->all());
        //7日
        $day_7_query = clone $query;
        $day_7_query->select("DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `time`,COALESCE(SUM(`bonus_price`),0) as `bonus_price`")
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-7 day')) . ' 00:00:00'])
            ->andWhere(['<=', 'created_at', date('Y-m-d', time()) . ' 23:59:59']);
        $day_7_data_query = clone $day_7_query;
        $day_7_data = $day_7_data_query->one();
        $day_7_list = $this->day_data($day_7_query->groupBy('time')->asArray()->all(), 7);
        //12月
        $month_12_query = clone $query;
        $month_12_query->select("DATE_FORMAT(`created_at`, '%Y-%m') AS `time`,COALESCE(SUM(`bonus_price`),0) as `bonus_price`")
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-12 month')) . ' 00:00:00'])
            ->andWhere(['<=', 'created_at', date('Y-m-d', time()) . ' 23:59:59']);
        $month_12_data_query = clone $month_12_query;
        $month_12_data = $month_12_data_query->one();
        $month_12_list = $this->month_data($month_12_query->groupBy('time')->asArray()->all(), 12);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'all_data' => [
                    'day_data' => $day_data['bonus_price'],
                    'day_7_data' => $day_7_data['bonus_price'],
                    'month_12_data' => $month_12_data['bonus_price'],
                ],
                'list' => [
                    'day_list' => $day_list,
                    'day_7_list' => $day_7_list,
                    'month_12_list' => $month_12_list,
                ],
            ]
        ];
    }

    protected function hour_24($list)
    {
        for ($i = 0; $i < 24; $i++) {
            $bool = false;
            foreach ($list as $item) {
                if ($i == intval($item['time'])) {
                    $bool = true;
                    $arr[$i]['created_at'] = $item['time'];
                    $arr[$i]['bonus_price'] = $item['bonus_price'];
                }
            }
            if (!$bool) {
                $arr[$i]['created_at'] = $i;
                $arr[$i]['bonus_price'] = '0';
            }
        }

        return $arr;
    }

    protected function day_data($list, $day)
    {
        for ($i = 0; $i < $day; $i++) {
            $date = date('Y-m-d', strtotime("-$i day"));
            $bool = false;
            foreach ($list as $item) {
                if ($date == $item['time']) {
                    $bool = true;
                    $arr[$i]['created_at'] = $item['time'];
                    $arr[$i]['bonus_price'] = $item['bonus_price'];
                }
            }
            if (!$bool) {
                $arr[$i]['created_at'] = $date;
                $arr[$i]['bonus_price'] = '0';
            }
        }
        return !empty($arr) ? array_reverse($arr) : [];
    }

    protected function month_data($list, $month)
    {
        for ($i = 0; $i < $month; $i++) {
            $date = date('Y-m', strtotime("-$i month"));
            $bool = false;
            foreach ($list as $item) {
                if ($date == $item['time']) {
                    $bool = true;
                    $arr[$i]['created_at'] = $item['time'];
                    $arr[$i]['bonus_price'] = $item['bonus_price'];
                }
            }
            if (!$bool) {
                $arr[$i]['created_at'] = $date;
                $arr[$i]['bonus_price'] = '0';
            }
        }
        return !empty($arr) ? array_reverse($arr) : [];
    }
}