<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/13
 * Time: 15:57
 */

namespace app\plugins\region\forms\common;

use app\models\DistrictArr;
use app\models\Model;
use app\plugins\region\models\RegionAreaDetail;

class CommonDistrict extends Model
{
    /**
     * 获取可选省份id
     * @return array
     */
    public function districtIds()
    {
        return array_column($this->district(), 'id');
    }

    /**
     * 获取可选省份
     * @return array
     */
    public function district()
    {
        $list = DistrictArr::getArr();
        $district = DistrictArr::getList($list, 'city');
        foreach ($district as $k => $v) {
            if ($v['id'] == 3268) {
                unset($district[$k]);
            }
        }
        $exist = $this->exist();
        foreach ($exist as $value) {
            foreach ($district as $key => $item) {
                if ($item['id'] == $value) {
                    unset($district[$key]);
                }
            }
        }
        return $district;
    }

    public function exist()
    {
        $exits = RegionAreaDetail::find()
            ->select(['province_id'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->column();
        return $exits;
    }
}
