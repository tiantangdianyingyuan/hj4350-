<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Store;

class StoreForm extends Model
{
    public $page;
    public $limit;
    public $id;
    public $keyword;
    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['longitude', 'latitude', 'keyword'], 'trim'],
            [['id', 'limit', 'page'], 'integer',],
            [['limit',], 'default', 'value' => 20],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        //误删
        //getDistance(36.8103, 118.014, latitude, longitude)

        // CREATE FUNCTION getDistance(curLat DOUBLE, curLon DOUBLE, shopLat DOUBLE, shopLon DOUBLE)
        // RETURNS DOUBLE
        // BEGIN
        //   DECLARE  dis DOUBLE;
        //     set dis = ACOS(SIN((curLat * 3.1415) / 180 ) * SIN((shopLat * 3.1415) / 180 ) + COS((curLat * 3.1415) / 180 ) * COS((shopLat * 3.1415) / 180 ) * COS((curLon * 3.1415) / 180 - (shopLon * 3.1415) / 180 ) ) * 6370.996 ;
        //     RETURN dis;
        // END;

        if (!$this->longitude || !$this->latitude) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '地址获取失败'
            ];
        }
        if (!\Yii::$app->user->isGuest && \Yii::$app->user->identity->mch_id) {
            $mch_id = \Yii::$app->user->identity->mch_id;
        } else {
            $mch_id = 0;
        }
        $query = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mch_id,
            'is_delete' => 0
        ])
            //可换为自定义
            ->select(['*', "(st_distance(point(longitude, latitude), point($this->longitude, $this->latitude)) * 111195) as distance"])
            ->orderBy('distance DESC');
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->page($pagination, $this->limit)->orderBy("distance ASC")->asArray()->all();

        array_walk($list, function (&$v) {
            if ($v['distance'] >= 1000) {
                $v['distance'] = round($v['distance'] / 1000, 2) . 'km';
            } else {
                $v['distance'] = round($v['distance'], 2) . 'm';
            }
        });
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $list = Store::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ]);
        if (!$list) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '店铺不存在',
            ];
        }
        $list->pic_url = json_decode($list->pic_url, true);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }
}
