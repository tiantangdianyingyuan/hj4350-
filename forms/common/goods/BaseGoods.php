<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/30
 * Time: 19:38
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;


use app\models\Goods;
use app\models\Model;

/**
 * @property Goods $goods
 */
class BaseGoods extends Model
{
    public $id;
    public $mall_id;
    public $goods_warehouse_id;
    public $status;
    public $use_attr;
    public $attr_groups;
    public $virtual_sales;
    public $unlimited_confine_count;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    public $is_delete;
    public $sort;
    public $confine_count;
    public $pieces;
    public $forehead;
    public $freight_id;
    public $give_integral;
    public $give_integral_type;
    public $forehead_integral;
    public $forehead_integral_type;
    public $accumulative;
    public $individual_share;
    public $attr_setting_type;
    public $is_level;
    public $is_level_alone;
    public $share_type;
    public $sign;
    public $app_share_pic;
    public $app_share_title;
    public $is_default_services;

    public $name;
    public $subtitle;
    public $price;
    public $original_price;
    public $cost_price;
    public $detail;
    public $cover_pic;
    public $pic_url;
    public $video_url;
    public $unit;

    private static $instance;
    protected $relations = [];
    public $goods;
    public $where = [];
    public $with = ['goodsWarehouse'];
    public $oldAttribute;

    /**
     * @return array
     * 需要被json解析的属性
     */
    private function needDecode()
    {
        return ['attr_groups', 'pic_url'];
    }

    /**
     * @param $array
     * @throws \Exception
     * 设置属性
     */
    protected function setProperty($array)
    {
        if (!is_array($array)) {
            throw new \Exception('setProperty的参数必须是一个数组');
        }
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                if (in_array($key, $this->needDecode())) {
                    $this->$key = \Yii::$app->serializer->decode($item);
                } else {
                    $this->$key = $item;
                }
            }
        }
    }

    /**
     * @param array $relations
     * 设置关联关系数据
     */
    public function setRelation($relations = [])
    {
        if (!is_array($relations)) {
            return ;
        }
        if (count($relations) <= 0) {
            return ;
        }
        foreach ($relations as $key => $item) {
            $this->relations[$item] = $this->goods->$item;
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     * 获取关联关系
     */
    public function getRelation($name)
    {
        $relation = $this->relations;
        if (!is_array($relation)) {
            throw new \Exception(get_class($this) . '未设置任何关联关系');
        }
        if (!isset($relation[$name])) {
            throw new \Exception(get_class($this) . '::' . $name . '不存在');
        }
        return $relation[$name];
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     * 重写get方法
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (\Exception $exception) {
            return $this->getRelation($name);
        }
    }

    /**
     * @return BaseGoods
     * 创建单例
     */
    public static function find()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function where($condition, $params = [])
    {
        $where = [];
        array_push($where, ['condition' => $condition, 'params' => $params]);
        $this->where = $where;
        return $this;
    }

    public function with($params)
    {
        if (is_string($params)) {
            array_push($this->with, $params);
        } elseif (is_array($params)) {
            $this->with = array_merge($this->with, $params);
        } else {
            throw new \Exception('参数格式不正确');
        }
        return $this;
    }

    /**
     * @return $this|null
     * @throws \Exception
     * 获取指定id的商品
     */
    public function one()
    {
        /* @var Goods $goods */
        $query = Goods::find()->with($this->with)
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        foreach ($this->where as $item) {
            $query->andWhere($item['condition'], $item['params']);
        }
        $goods = $query->one();
        if (!$goods) {
            return null;
        }
        $this->goods = $goods;
        $this->setProperty($goods->goodsWarehouse->attributes);
        $this->setProperty($goods->attributes);
        $this->setRelation($this->with);
        $this->oldAttribute = $this;
        return $this;
    }
}
