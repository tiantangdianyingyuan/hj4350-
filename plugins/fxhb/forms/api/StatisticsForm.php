<?php


namespace app\plugins\fxhb\forms\api;

use app\forms\mall\export\FxhbStatisticsExport;
use app\models\Model;
use app\core\response\ApiCode;
use app\models\UserInfo;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbUserActivity;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;

    public $name;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;

    public function rules()
    {
        return [
            [['flag', 'name', 'platform'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['date_start', 'date_end', 'fields'], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        $query->select("f.`start_time`,f.`end_time`,f.`name`,f.`number` as `num`,
                                    COUNT(DISTINCT CASE fu.`parent_id` WHEN 0 THEN fu.`user_id` ELSE NULL END) AS `launch_user_num`,
                                    COUNT(fu.`user_id`) AS `participate_num`,
                                    SUM(CASE fu.`parent_id` WHEN 0 THEN 1 ELSE 0 END) AS `launch_num`,
                                    SUM(CASE WHEN fu.`parent_id` = 0 AND fu.`status` = 1 THEN 1 ELSE 0 END) AS `success_num`,
                                    SUM(CASE fu.`status` WHEN 1 THEN 1 ELSE 0 END) AS `coupon_num`,
                                    SUM(CASE fu.`status` WHEN 1 THEN fu.`get_price` ELSE 0 END) AS `coupon_price`,
                                    CASE WHEN f.`start_time` > NOW() THEN '未开始' WHEN f.`end_time` < NOW() THEN '已结束' ELSE '进行中' END AS `status`");
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }
        $list = $query
            ->page($pagination)
            ->asArray()
            ->all();

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
        $query = FxhbActivity::find()->alias('f')
            ->leftJoin(['fu' => FxhbUserActivity::tableName()], 'fu.`fxhb_activity_id` = f.`id`')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = fu.user_id')
            ->where(['f.mall_id' => \Yii::$app->mall->id, 'f.is_delete' => 0])
            ->groupBy('f.`id`')
            ->orderBy('f.`created_at` desc');

        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'f.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'f.created_at', $this->date_end . ' 23:59:59']);
        }

        if ($this->name) {
            $query->andWhere(['like', 'f.name', $this->name]);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        return $query;
    }


    protected function export($query)
    {
        $exp = new FxhbStatisticsExport();
        $exp->export($query);
    }
}