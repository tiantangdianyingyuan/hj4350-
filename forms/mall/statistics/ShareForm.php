<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\mall\export\ShareStatisticsExport;
use app\models\Model;
use app\models\Share;
use app\models\ShareCash;
use app\models\UserInfo;

class ShareForm extends Model
{
    public $name;
    public $order;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;

    public function rules()
    {
        return [
            [['flag'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['name', 'order', 'platform'], 'string'],
            [['fields'], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        $query->select("s.`user_id` as `id`,s.`user_id`,s.`name`,s.`total_money`,s.`first_children`,s.`all_children`,
        s.`all_money`,  s.`all_order`,  COALESCE(SUM(sc.`price`),0) AS `price`,`i`.`platform`")
            ->groupBy('s.`user_id`,s.`mall_id`');

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query->with('user.userInfo')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as $key => $value) {
            $list[$key]['nickname'] = $value['user']['nickname'];
            $list[$key]['avatar'] = $value['user']['userInfo']['avatar'];
            unset($list[$key]['user']);
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    protected function where()
    {
        $query = Share::find()->alias('s')->where(['s.is_delete' => 0, 's.status' => 1, 's.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['sc' => ShareCash::tableName()], 'sc.`user_id` = s.`user_id` AND sc.`mall_id`=s.`mall_id` AND sc.`status` = 2 ')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = s.user_id');
        if ($this->name) {
            $query->andWhere(['or', ['s.user_id' => $this->name], ['like', 's.name', $this->name]]);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        $query->orderBy(!empty($this->order) ? $this->order : 's.id');

        return $query;
    }


    protected function export($query)
    {
        $exp = new ShareStatisticsExport();
        $exp->export($query);
    }
}