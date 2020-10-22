<?php

namespace app\plugins\booking\forms\api;

use app\core\response\ApiCode;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\models\BookingCats;
use app\plugins\booking\models\BookingStore;
use app\plugins\booking\models\Goods;

class BookingForm extends Model
{
    public $goods_id;
    public $keyword;
    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['longitude', 'latitude'], 'trim'],
            [['keyword'], 'string']
        ];
    }

    public function cats()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $goodsWarehouseIds = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'sign' => \Yii::$app->plugin->currentPlugin->getName()
            ])->select('goods_warehouse_id');

            $catIds = GoodsCatRelation::find()->where(['is_delete' => 0, 'goods_warehouse_id' => $goodsWarehouseIds])
                ->select('cat_id');

            $catList = GoodsCats::find()->where([
                'id' => $catIds,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => 0,
                'is_delete' => 0,
                'status' => 1,
                'parent_id' => 0,
            ])->orderBy(['sort' => SORT_ASC])->all();

            $newCatList = [];
            /** @var GoodsCats $cat */
            foreach ($catList as $cat) {
                $newCatItem = [];
                $newCatItem['id'] = $cat->id;
                $newCatItem['name'] = $cat->name;
                $newCatList[] = $newCatItem;
            }

            array_unshift($newCatList, ['id' => -1, 'name' => '全部']);

            $setting = CommonBooking::getSetting();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'cat' => $newCatList,
                    'is_show_cat' => $setting['is_cat'],
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    public function store()
    {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }
            $store = BookingStore::find()->alias('b')->where([
                'b.mall_id' => \Yii::$app->mall->id,
                'b.goods_id' => $this->goods_id,
                'b.is_delete' => 0,
                's.is_delete' => 0,
            ])->joinWith(['store s'])
                ->select(['*', "(st_distance(point(longitude, latitude), point($this->longitude, $this->latitude)) * 111195) as distance"])
                ->keyword($this->keyword, ['like', 's.name', $this->keyword])
                ->page($pagination)
                ->orderBy('distance ASC')
                ->asArray()
                ->all();
            $store = array_map(function ($item) {
                $info = $item['store'];

                if ($info['longitude']
                    && $info['latitude']
                    && $this->longitude
                    && $this->latitude) {
                    $distance = get_distance($item['store']['longitude'], $item['store']['latitude'], $this->longitude, $this->latitude);
                    if ($distance > 1000) {
                        $info['distance'] = number_format($distance / 1000, 2) . 'km';
                    } else {
                        $info['distance'] = number_format($distance, 0) . 'm';
                    }
                } else {
                    $info['distance'] = '-m';
                }
                return $info;
            }, $store);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $store,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function setting() {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }

            $setting = (new CommonBooking())->getSetting(\Yii::$app->mall->id);
            if(!$setting) {
                throw new \Exception('预约尚未配置');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'setting' => $setting,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
