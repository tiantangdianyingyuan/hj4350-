<?php


namespace app\forms\common\data_importing;


use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsServiceRelation;
use app\models\GoodsServices;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\models\Model;
use Exception;

class GoodsBaseModel extends Model
{
    public $tables = ['goodsWareHouse', 'goods', 'goodsAttr', 'mallGoods', 'goodsServices', 'goodsServicesRelation'];
    public $table;
    public $mall;

    protected $goods_id;
    protected $cat_id;
    protected $goods_warehouse_id;
    protected $goods_services = [];

    public function __construct($mall, $table = [])
    {
        $this->mall = $mall;
        $this->table = $table;
        parent::__construct();
    }


    protected function goodsWareHouse($param)
    {
        $value = [
            'mall_id' => $this->mall->id,
            'name' => $param['name'],
            'original_price' => $param['original_price'],
            'cost_price' => $param['cost_price'] ?? 0,
            'detail' => $param['detail'],
            'cover_pic' => $param['cover_pic'] ?: '',
            'pic_url' => \Yii::$app->serializer->encode($param['goodsPicList']),
            'video_url' => $param['video_url'] ?: '',
            'unit' => $param['unit'] ?? '',
            'created_at' => mysql_timestamp($param['addtime']),
            'deleted_at' => '0000-00-00 00:00:00',
            'updated_at' => '0000-00-00 00:00:00',
            'is_delete' => $param['is_delete'],
        ];
        $model = new GoodsWarehouse();
        $model->attributes = $value;
        if ($model->save()) {
            $this->goods_warehouse_id = $model->attributes['id'];
            return true;
        } else {
            throw new Exception($this->getErrorMsg($model));
        }
    }

    protected function goods($params)
    {
        if (!$this->goods_warehouse_id) {
            throw new Exception('ERROR goods_warehouse_id');
        }

        //积分处理
        if (isset($params['integral']) && $params['integral']) {
            $integral = \Yii::$app->serializer->decode($params['integral']);
            if (preg_match('/^([0-9]+)%$/', $integral['give'], $matches)) {
                $newList['give_integral_type'] = 2;
                $newList['give_integral'] = $matches[1];
            } else {
                $newList['give_integral_type'] = 1;
                $newList['give_integral'] = $integral['give'] ?: 0;
            }
            if (preg_match('/^([0-9]+)%$/', $integral['forehead'], $matches)) {
                $newList['forehead_integral_type'] = 2;
                $newList['forehead_integral'] = $matches[1];
            } else {
                $newList['forehead_integral_type'] = 1;
                $newList['forehead_integral'] = $integral['forehead'] ?: 0;
            }
            $newList['accumulative'] = $integral['more'] ? 1 : 0;
        } else {
            $newList['give_integral_type'] = 1;
            $newList['give_integral'] = 0;
            $newList['forehead_integral_type'] = 1;
            $newList['forehead_integral'] = 0;
            $newList['forehead_integral_type'] = 1;
            $newList['forehead_integral'] = 0;
            $newList['accumulative'] = 0;
        }

        if (isset($params['full_cut']) && $params['full_cut']) {
            $full_cut = \Yii::$app->serializer->decode($params['full_cut']);
            $newList['pieces'] = $full_cut['pieces'] ?: 0;
            $newList['forehead'] = $full_cut['pieces'] ?: 0;
        } else {
            $newList['pieces'] = 0;
            $newList['forehead'] = 0;
        }


        //规格
        $newList['attr_groups'] = [];
        foreach ($params['attr'][0]['attr_list'] as $k1 => $v1) {
            $ac = [];
            foreach ($params['attr'] as $k2 => $v2) {
                foreach ($v2['attr_list'] as $k3 => $v3) {
                    array_push($ac, [
                        'attr_id' => $v3['attr_id'],
                        'attr_name' => $v3['attr_name'],
                    ]);
                }
            }
            array_push($newList['attr_groups'], [
                'attr_group_id' => $v1['attr_group_id'],
                'attr_group_name' => $v1['attr_group_name'],
                'attr_list' => $ac
            ]);
        }

        $value = [
            'mall_id' => $this->mall->id,
            'mch_id' => 0,
            'goods_warehouse_id' => $this->goods_warehouse_id,
            'status' => $params['status'],
            'price' => $params['price'],
            'use_attr' => $params['use_attr'],
            'attr_groups' => \Yii::$app->serializer->encode($newList['attr_groups']),
            'goods_stock' => $params['goods_num'],
            'virtual_sales' => $params['virtual_sales'],
            'confine_count' => $params['confine_count'] ?? 0,
            'pieces' => $newList['pieces'],
            'forehead' => $newList['forehead'],
            'freight_id' => $params['freight'] ?? 0,
            'give_integral' => $newList['give_integral'],
            'give_integral_type' => $newList['give_integral_type'],
            'forehead_integral' => $newList['forehead_integral'],
            'forehead_integral_type' => $newList['forehead_integral_type'],
            'accumulative' => $newList['accumulative'],
            'individual_share' => $params['individual_share'] ?? 0,
            'attr_setting_type' => $params['attr_setting_type'] ?? 0,
            'is_level' => $params['is_level'],
            'is_level_alone' => 0,
            'share_type' => $params['share_type'] ?? 1 == 1 ? 0 : 1,
            'sign' => '',
            'app_share_pic' => '',
            'app_share_title' => '',
            'is_default_services' => 0,
            'sort' => $params['sort'],
            'created_at' => mysql_timestamp($params['addtime']),
            'deleted_at' => '0000-00-00 00:00:00',
            'updated_at' => '0000-00-00 00:00:00',
            'is_delete' => $params['is_delete'],
            'payment_people' => 0,
            'payment_num' => 0,
            'payment_amount' => 0,
            'payment_order' => 0,
        ];

        $model = new Goods();
        $model->attributes = $value;

        if ($model->save()) {
            $this->goods_id = $model->attributes['id'];
            return true;
        } else {
            throw new Exception($this->getErrorMsg($model));
        }
    }

    protected function goodsAttr($params)
    {
        if (!$this->goods_id) {
            throw new Exception('ERROR goods_id');
        }

        $values = [];
        foreach ($params['attr'] as $v) {
            $sign_id = array_column($v['attr_list'], 'attr_id');
            $sign_id = implode(':', $sign_id);
            $value = [
                'goods_id' => $this->goods_id,
                'sign_id' => $sign_id,
                'stock' => $v['num'],
                'price' => $v['price'] ?: $params['price'],
                'no' => $v['no'] ?? '',
                'weight' => $params['weight'] ?? 0,
                'pic_url' => $v['pic'] ?? '',
                'is_delete' => $params['is_delete'],
            ];
            $values[] = $value;
        }

        \Yii::$app->db->createCommand()->batchInsert(
            GoodsAttr::tableName(),
            ['goods_id', 'sign_id', 'stock', 'price', 'no', 'weight', 'pic_url', 'is_delete'],
            $values
        )->execute();
    }

    protected function mallGoods($params)
    {
        if (!$this->goods_id) {
            throw new Exception('ERROR goods_id');
        }

        $value = [
            'mall_id' => $this->mall->id,
            'goods_id' => $this->goods_id,
            'is_quick_shop' => $params['quick_purchase'] ?? 0,
            'is_sell_well' => 0,
            'is_negotiable' => $params['is_negotiable'] ?? 0,
            'created_at' => mysql_timestamp($params['addtime']),
            'deleted_at' => '0000-00-00 00:00:00',
            'updated_at' => '0000-00-00 00:00:00',
            'is_delete' => $params['is_delete'],
        ];
        $model = new MallGoods();
        $model->attributes = $value;
        if ($model->save()) {
            return true;
        } else {
            throw new Exception($this->getErrorMsg($model));
        }
    }

    protected function goodsServices($params)
    {
        if (!$this->goods_id) {
            throw new Exception('ERROR goods_id');
        }
        if (!$params['service']) {
            return true;
        }

        $service = explode(',', $params['service']);

        foreach ($service as $k => $v) {
            $value = [
                'mall_id' => $this->mall->id,
                'mch_id' => 0,
                'name' => $v,
                'remark' => '',
                'sort' => $k + 1,
                'is_default' => 0,
                'created_at' => mysql_timestamp($params['addtime']),
                'deleted_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
                'is_delete' => 0,
            ];

            $model = new GoodsServices();
            $model->attributes = $value;
            if ($model->save()) {
                array_push($this->goods_services, $model->attributes['id']);
            } else {
                throw new Exception($this->getErrorMsg($model));
            }
        }
        return true;
    }

    protected function goodsServicesRelation($params)
    {
        if (!$this->goods_id) {
            throw new Exception('ERROR goods_id');
        }

        if (!isset($this->goods_services)) {
            throw new Exception('ERROR goods_services');
        }

        foreach ($this->goods_services as $k => $v) {
            $value = [
                'service_id' => $v,
                'goods_id' => $this->goods_id,
                'is_delete' => 0,
            ];

            $model = new GoodsServiceRelation();
            $model->attributes = $value;
            if (!$model->save()) {
                throw new Exception($this->getErrorMsg($model));
            }
        }
        return true;
    }
}