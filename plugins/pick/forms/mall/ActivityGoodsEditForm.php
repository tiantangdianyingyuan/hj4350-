<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/2
 * Time: 9:30
 */

namespace app\plugins\pick\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\pick\forms\common\CommonForm;
use app\plugins\pick\models\PickCart;
use app\plugins\pick\models\PickGoods;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class ActivityGoodsEditForm extends Model
{
    public $pick_activity_id;
    public $id;
    public $pick;
    public $type;

    public function rules()
    {
        return [
            [['pick_activity_id', 'pick'], 'required', 'on' => ['activity']],
            [['type'], 'default', 'value' => 'add'],
            [['id'], 'integer'],
            [['pick'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'pick_activity_id' => '活动id',
            'pick' => '商品',
        ];
    }

    public function editGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!method_exists($this, $this->type) || !in_array($this->type, ['add', 'edit', 'del'])) {
                throw new \Exception('未知操作');
            }

            if (!$this->pick || !is_array($this->pick) || count($this->pick) < 1) {
                throw new \Exception('请选择商品');
            }

            $func = $this->type;
            $this->$func();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function add()
    {
        $nowIds = PickGoods::find()
            ->alias('pg')
            ->where([
                'pg.mall_id' => \Yii::$app->mall->id,
                'pg.pick_activity_id' => $this->pick_activity_id,
                'pg.is_delete' => 0
            ])
            ->leftJoin(['g' => Goods::tableName()], 'pg.goods_id = g.id')
            ->select('g.goods_warehouse_id')
            ->column();

        $existIds = GoodsWarehouse::find()
            ->alias('gw')
            ->leftJoin(['g' => Goods::tableName()], 'g.goods_warehouse_id = gw.id')
            ->where(['g.id' => array_column($this->pick, 'goods_id')])
            ->select('gw.id')
            ->column();

        $intersection = array_intersect($nowIds, $existIds);
        if (!empty($intersection) && is_array($intersection)) {
            $name = GoodsWarehouse::findOne(['id' => array_values($intersection)[0]]);
            throw new \Exception('商品' . $name['name'] . '重复');
        }

        foreach ($this->pick as $item) {
            if (isset($item['sort']) && $item['sort'] > 999999999) {
                throw new \Exception('排序不能大于99999999');
            }
            $goods = new GoodsEditForm();
            $common = CommonGoods::getCommon();
            $goodsDeteil = $common->getGoodsDetail($item['goods_id']);
            $goods->attributes = $goodsDeteil;
            $goods->attrGroups = ArrayHelper::toArray($goodsDeteil['attr_groups']);
            $goods->attr = $item['attr'] ?? ArrayHelper::toArray($goodsDeteil['attr']);
            $goods->member_price = [];

            $pickGoods = new PickGoods();
            $stock = CommonForm::getStock($goods);
            $pickGoods->stock = $stock;

            $goods->status = 1;
            $goods->setSign('pick');
            $goods->save();
            $pickGoods->goods_id = $goods->goods_id;
            $pickGoods->mall_id = \Yii::$app->mall->id;
            $pickGoods->sort = $item['sort'] ?? 100;
            $pickGoods->pick_activity_id = $this->pick_activity_id;
            $pickGoods->status = 1;
            $res = $pickGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($pickGoods));
            }
        }
    }

    private function edit()
    {
        if (count($this->pick) > 1) {
            throw new \Exception('无法进行批量操作');
        }

        $item = $this->pick[0];

        /** @var PickGoods $pickGoods */
        $pickGoods = PickGoods::find()->where([
            'id' => $item['id'] ?? 0,
            'mall_id' => \Yii::$app->mall->id,
            'pick_activity_id' => $this->pick_activity_id
        ])->one();

        if (empty($pickGoods)) {
            throw new \Exception('该商品不存在');
        }

        $goods = new GoodsEditForm();
        $common = CommonGoods::getCommon();
        $goodsDeteil = $common->getGoodsDetail($pickGoods->goods_id);
        $goods->attributes = $goodsDeteil;
        $goods->sort = $item['sort'] ?? 100;
        $goods->attrGroups = ArrayHelper::toArray($goodsDeteil['attr_groups']);
        $goods->attr = $item['attr'] ?? ArrayHelper::toArray($goodsDeteil['attr']);

        $stock = CommonForm::getStock($goods);
        $pickGoods->stock = $stock > $pickGoods->stock ? $stock : $pickGoods->stock;
        $goods->id = $pickGoods->goods_id;
        $goods->status = 1;
        $goods->setSign('pick');
        $goods->save();
        $pickGoods->goods_id = $goods->goods_id;
        $pickGoods->mall_id = \Yii::$app->mall->id;
        $pickGoods->sort = $item['sort'] ?? 100;
        $pickGoods->pick_activity_id = $this->pick_activity_id;
        $pickGoods->status = 1;
        $res = $pickGoods->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($pickGoods));
        }
    }

    private function del()
    {
        $ids = PickGoods::find()
            ->alias('pg')
            ->where([
                'pg.mall_id' => \Yii::$app->mall->id,
                'pg.pick_activity_id' => $this->pick_activity_id,
                'pg.id' => array_column($this->pick, 'id'),
                'pg.is_delete' => 0,
            ])
            ->select(['goods_id'])
            ->column();

        Goods::updateAll([
            'is_delete' => 1,
            'deleted_at' => mysql_timestamp()
        ], [
            'mall_id' => \Yii::$app->mall->id,
            'id' => $ids,
            'is_delete' => 0,
        ]);

        // 删除商品关联
        PickGoods::updateAll(
            [
                'is_delete' => 1,
                'deleted_at' => mysql_timestamp()
            ],
            [
                'pick_activity_id' => $this->pick_activity_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => array_column($this->pick, 'id')
            ]
        );

        //删除凑单池商品
        PickCart::updateAll([
            'is_delete' => 1
        ], [
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $ids,
            'pick_activity_id' => $this->pick_activity_id,
            'is_delete' => 0,
        ]);
    }

    public function getGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = CommonGoods::getCommon();
            $detail = $common->getGoodsDetail($this->id);
            $detail['status'] = intval($detail['status']);

            if ($detail) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'detail' => $detail
                    ]
                ];
            }

            throw new \Exception('请求失败');
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
