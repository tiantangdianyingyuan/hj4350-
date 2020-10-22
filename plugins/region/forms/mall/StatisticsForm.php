<?php


namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\models\DistrictArr;
use app\models\Model;
use app\plugins\region\forms\export\RegionStatisticsExport;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionAreaDetail;
use app\plugins\region\models\RegionCashLog;

class StatisticsForm extends Model
{
    public $date_start;
    public $date_end;
    public $province_id;
    public $flag;

    public function rules()
    {
        return [
            [['flag',], 'string'],
            [['province_id',], 'default', 'value' => 2],
            [['date_start', 'date_end',], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->query();

        $baseQuery = RegionCashLog::find()
            ->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'province_id' => $this->province_id,
                ]
            );

        //时间查询
        if ($this->date_start) {
            $baseQuery->andWhere(['>=', 'created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $baseQuery->andWhere(['<=', 'created_at', $this->date_end . ' 23:59:59']);
        }

        $provinceQuery = clone $baseQuery;
        $cityQuery = clone $baseQuery;
        $districtQuery = clone $baseQuery;

        $provinceQuery = $provinceQuery->andWhere(['level_id' => 1, 'type' => 1]);
        //时间查询
        if ($this->date_start) {
            $provinceQuery->andWhere(['>=', 'created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $provinceQuery->andWhere(['<=', 'created_at', $this->date_end . ' 23:59:59']);
        }
        $provinceMoneyQuery = clone $provinceQuery;
        $provinceCountQuery = clone $provinceQuery;
        $provinceMoney = $provinceMoneyQuery->select('COALESCE(sum(price),0) as `bonus`');
        $provinceCount = $provinceCountQuery->select('COALESCE(sum(order_num),0) as `pc`');

        $cityQuery = $cityQuery->andWhere(['level_id' => 2, 'type' => 1]);
        //时间查询
        if ($this->date_start) {
            $cityQuery->andWhere(['>=', 'created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $cityQuery->andWhere(['<=', 'created_at', $this->date_end . ' 23:59:59']);
        }
        $cityMoneyQuery = clone $cityQuery;
        $cityCountQuery = clone $cityQuery;
        $cityMoney = $cityMoneyQuery->select('COALESCE(sum(price),0) as `bonus`');
        $cityCount = $cityCountQuery->select('COALESCE(sum(order_num),0) as `cc`');

        $districtQuery = $districtQuery->andWhere(['level_id' => 3, 'type' => 1]);
        //时间查询
        if ($this->date_start) {
            $districtQuery->andWhere(['>=', 'created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $districtQuery->andWhere(['<=', 'created_at', $this->date_end . ' 23:59:59']);
        }
        $districtMoneyQuery = clone $districtQuery;
        $districtCountQuery = clone $districtQuery;
        $districtMoney = $districtMoneyQuery->select('COALESCE(sum(price),0) as `bonus`');
        $districtCount = $districtCountQuery->select('COALESCE(sum(order_num),0) as `dc`');

        $model = $query->select(
            [
                'ad.province_id',
                'a.province_rate',
                'a.city_rate',
                'a.district_rate',
                'province_count' => $provinceCount,
                'province_money' => $provinceMoney,
                'city_count' => $cityCount,
                'city_money' => $cityMoney,
                'district_count' => $districtCount,
                'district_money' => $districtMoney,
            ]
        )->asArray()->one();

        if ($this->flag == "EXPORT") {
            if (!empty($model) && !empty($this->date_end) && !empty($this->date_start)) {
                $model['date_start'] = $this->date_start;
                $model['date_end'] = $this->date_end;
            }
            $this->export($model);
            return false;
        }

        $provinces = RegionAreaDetail::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->select(['province_id'])
            ->distinct(true)
            ->asArray()
            ->all();

        foreach ($provinces as &$item) {
            $item['name'] = DistrictArr::getDistrict($item['province_id'])['name'];
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'model' => $model,
                'provinces' => $provinces
            ]
        ];
    }

    protected function query()
    {
        $query = RegionArea::find()
            ->alias('a')
            ->where(['a.mall_id' => \Yii::$app->mall->id, 'a.is_delete' => 0])
            ->leftJoin(['ad' => RegionAreaDetail::tableName()], 'a.id = ad.area_id')
            ->andWhere(
                [
                    'ad.mall_id' => \Yii::$app->mall->id,
                    'ad.is_delete' => 0,
                    'ad.province_id' => $this->province_id
                ]
            );
        return $query;
    }


    protected function export($query)
    {
        $exp = new RegionStatisticsExport();
        $exp->export($query);
    }
}
