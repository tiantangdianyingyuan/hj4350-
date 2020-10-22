<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/26
 * Time: 17:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common;


use app\forms\common\map\Map;
use app\models\CityDeliveryman;
use app\models\CityDeliverySetting;
use app\models\Mall;
use app\models\Model;

/**
 * Class DeliveryForm
 * @package app\forms\common
 * @property Mall $mall
 */
class CommonDelivery extends Model
{
    public static $instance;
    public $mall;
    protected $config;

    public static function getInstance($mall = null)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $instance = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance->mall = $mall;
        self::$instance = $instance;
        return self::$instance;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        /* @var CityDeliverySetting[] $res */
        $res = CityDeliverySetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->all();
        $config = [];
        foreach ($res as $value) {
            $config[$value->key] = json_decode($value->value, true);
            if ($value->key == 'range' && (!$config[$value->key] || empty($config[$value->key]))) {
                throw new \Exception('未配置配送范围');
            }
        }
        $this->config = $config;
        return $config;
    }

    /**
     * @param $destination
     * @return float
     * @throws \Exception
     */
    public function getDistance($destination)
    {
        $map = Map::getInstance();
        $map->destination = $destination;
        return $map->distance();
    }

    /**
     * @param float $distance
     * @param integer $num
     * @return int|string
     * @throws \Exception
     */
    public function getPrice($distance, $num)
    {
        $config = $this->getConfig();
        $superposition = $config['is_superposition'] == 1;//叠加开关
        $price = 0;
        $priceMode = $config['price_mode'];
        //开启同城配送时，计算费用
        if (!empty($priceMode)) {
            //是否超过固定费用距离
            if ($distance > ($priceMode['start_distance'] + $priceMode['add_distance'])) {
                $price = $priceMode['fixed'];
            } elseif ($distance < $priceMode['start_distance']) { //是否超过启始距离
                $price = $priceMode['start_price'];
            } else {
                $price = bcadd($priceMode['start_price'], bcmul(bcsub($distance, $priceMode['start_distance']), $priceMode['add_price']));
            }
        }
        if ($superposition) {
            $price *= $num;
        }
        return price_format($price);
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null|CityDeliveryman
     */
    public function getManOne($id)
    {
        $model = CityDeliveryman::find()->where(['mall_id' => $this->mall->id, 'id' => $id, 'is_delete' => 0])->one();
        return $model;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]|CityDeliveryman[]
     */
    public function getManList()
    {
        $list = CityDeliveryman::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])->all();
        return $list;
    }
}
