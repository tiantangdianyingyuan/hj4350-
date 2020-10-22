<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\city_service;

use app\models\Model;

class BaseCityService extends Model
{
    private $sf = 1; // 顺丰
    private $ss = 2; // 闪送
    private $mt = 3; // 美团
    private $dd = 4; // 达达

    private $delivery_sf = 'SFTC';
    private $delivery_ss = 'SS';
    private $delivery_mtps = 'MTPS';
    private $delivery_dada = 'DADA';

    protected $platform;

    protected function getCorporationValueList()
    {
        return [
            $this->sf,
            $this->ss,
            $this->mt,
            $this->dd,
        ];
    }
    /**
     * 获取配送公司名称
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getCorporationName($value)
    {
        foreach ($this->getCorporationList() as $key => $item) {
            if ($value == $item['value']) {
                return $item['name'];
            }
        }
    }

    /**
     * 获取配送公司ID
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getDeliveryId($value)
    {
        foreach ($this->getCorporationList() as $key => $item) {
            if ($value == $item['value']) {
                return $item['delivery_id'];
            }
        }
    }

    /**
     * 获取配送公司信息
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getCorporation($value)
    {
        foreach ($this->getCorporationList() as $key => $item) {
            if ($value == $item['value']) {
                return $item;
            }
        }
    }

    protected function getCorporationList()
    {
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
        return [
            [
                'name' => '顺丰同城急送',
                'icon' => $url . '/statics/img/mall/city_service/sf.png',
                'value' => $this->sf,
                'delivery_id' => $this->delivery_sf,
            ],
            [
                'name' => '闪送',
                'icon' => $url . '/statics/img/mall/city_service/ss.png',
                'value' => $this->ss,
                'delivery_id' => $this->delivery_ss,
            ],
            [
                'name' => '美团配送',
                'icon' => $url . '/statics/img/mall/city_service/mt.png',
                'value' => $this->mt,
                'delivery_id' => $this->delivery_mtps,
            ],
            [
                'name' => '达达',
                'icon' => $url . '/statics/img/mall/city_service/dd.png',
                'value' => $this->dd,
                'delivery_id' => $this->delivery_dada,
            ],
        ];
    }
}
