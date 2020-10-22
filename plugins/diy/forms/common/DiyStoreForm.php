<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\models\Model;
use app\models\Store;

class DiyStoreForm extends Model
{
    public function getStoreIds($data)
    {
        $storeIds = [];
        foreach ($data['list'] as $item) {
            $storeIds[] = $item['id'];
        }

        return $storeIds;
    }

    public function getStoreById($storeIds)
    {
        if (!$storeIds) {
            return [];
        }

        $list = Store::find()->where(['id' => $storeIds, 'is_delete' => 0])->all();
        $newList = [];
        /** @var Store $item */
        foreach ($list as $item) {
            $arr['id'] = $item->id;
            $arr['mobile'] = $item->mobile;
            $arr['name'] = $item->name;
            $arr['pic_url'] = $item->cover_url;
            $arr['score'] = $item->score;
            $arr['longitude'] = $item->longitude;
            $arr['latitude'] = $item->latitude;
            $newList[] = $arr;
        }

        return $newList;
    }

    public function getNewStore($data, $diyStore, $longitude, $latitude)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($diyStore as $sItem) {
                if ($sItem['id'] == $item['id']) {
                    $item = $sItem;
                    if ($latitude && $longitude) {
                        $distance = $this->getDistance($latitude, $longitude, $item['latitude'], $item['longitude']);
                        $item['distance'] = $distance . 'km';
                    }
                    $newArr[] = $item;
                    break;
                }
            }
        }
        $data['list'] = $newArr;

        return $data;
    }

    /**
     * 根据经纬度算距离，返回结果单位是公里，先纬度，后经度
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;

        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 10000) / 10000;

        return $s;
    }

    private function rad($d)
    {
        return $d * M_PI / 180.0;
    }
}
