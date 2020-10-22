<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class ActivityGoodsEditForm extends Model
{
    public $activity_id;
    public $id;
    public $community;
    public $type;

    public function rules()
    {
        return [
            [['activity_id', 'community'], 'required', 'on' => ['activity']],
            [['type'], 'default', 'value' => 'add'],
            [['id'], 'integer'],
            [['community'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'activity_id' => '活动id',
            'community' => '商品',
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

            if (!$this->community || !is_array($this->community) || count($this->community) < 1) {
                throw new \Exception('请选择商品');
            }
//            $activity = CommunityActivity::findOne($this->activity_id);
//            if ($this->type == 'edit' && strtotime($activity->start_at) < time() && strtotime($activity->end_at) > time()) {
//                throw new \Exception('活动进行中，无法编辑商品');
//            }
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
                'line' => $exception->getLine()
            ];
        }
    }

    private function add()
    {
        $nowIds = CommunityGoods::find()
            ->alias('cg')
            ->where([
                'cg.mall_id' => \Yii::$app->mall->id,
                'cg.activity_id' => $this->activity_id,
                'cg.is_delete' => 0
            ])
            ->leftJoin(['g' => Goods::tableName()], 'cg.goods_id = g.id')
            ->select('g.goods_warehouse_id')
            ->column();

        $existIds = GoodsWarehouse::find()
            ->alias('gw')
            ->leftJoin(['g' => Goods::tableName()], 'g.goods_warehouse_id = gw.id')
            ->where(['g.id' => array_column($this->community, 'goods_id')])
            ->select('gw.id')
            ->column();

        $intersection = array_intersect($nowIds, $existIds);
        if (!empty($intersection) && is_array($intersection)) {
            $name = GoodsWarehouse::findOne(['id' => array_values($intersection)[0]]);
            throw new \Exception('商品' . $name['name'] . '重复');
        }

        foreach ($this->community as $item) {
            if (isset($item['sort']) && $item['sort'] > 999999999) {
                throw new \Exception('排序不能大于99999999');
            }
            $goods = new GoodsEditForm();
            $common = CommonGoods::getCommon();
            $goodsDetail = $common->getGoodsDetail($item['goods_id']);
            $goods->attributes = $goodsDetail;
            $goods->status = 0;//添加商品，默认下架状态
            $goods->attrGroups = ArrayHelper::toArray($goodsDetail['attr_groups']);
            $goods->attr = $item['attr'] ?? ArrayHelper::toArray($goodsDetail['attr']);
            $goods->member_price = [];
            $goods->setSign('community');
            $goods_res = $goods->save();
            if ($goods_res['code'] == ApiCode::CODE_ERROR) {
                throw new \Exception($goods_res['msg']);
            }
            $communityGoods = new CommunityGoods();
            $communityGoods->goods_id = $goods->goods_id;
            $communityGoods->mall_id = \Yii::$app->mall->id;
            $communityGoods->sort = $item['sort'] ?? 100;
            $communityGoods->activity_id = $this->activity_id;
            $res = $communityGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($communityGoods));
            }
        }
    }

    private function edit()
    {
        if (count($this->community) > 1) {
            throw new \Exception('无法进行批量操作');
        }

        $item = $this->community[0];

        /** @var CommunityGoods $communityGoods */
        $communityGoods = CommunityGoods::find()->where([
            'id' => $item['id'] ?? 0,
            'mall_id' => \Yii::$app->mall->id,
            'activity_id' => $this->activity_id
        ])->one();

        if (empty($communityGoods)) {
            throw new \Exception('该商品不存在');
        }

        $goods = new GoodsEditForm();
        $common = CommonGoods::getCommon();
        $goodsDetail = $common->getGoodsDetail($communityGoods->goods_id);
        $goods->attributes = $goodsDetail;
        $goods->status = $item['status'] ?? 0;
        $goods->sort = $item['sort'] ?? 100;
        $goods->attrGroups = ArrayHelper::toArray($goodsDetail['attr_groups']);
        $goods->attr = $item['attr'] ?? ArrayHelper::toArray($goodsDetail['attr']);
        $goods->price = $item['price'];
        $goods->id = $communityGoods->goods_id;
        $goods->setSign('community');
        $goods_res = $goods->save();
        if ($goods_res['code'] == ApiCode::CODE_ERROR) {
            throw new \Exception($goods_res['msg']);
        }
        if ($item['use_attr'] == 0) {
            CommunityGoodsAttr::updateAll(['supply_price' => $item['supply_price']], ['goods_id' => $goods->goods_id, 'is_delete' => 0]);//单规格商品更新供货价
        }
        $communityGoods->goods_id = $goods->goods_id;
        $communityGoods->mall_id = \Yii::$app->mall->id;
        $communityGoods->sort = $item['sort'] ?? 100;
        $communityGoods->activity_id = $this->activity_id;
        $res = $communityGoods->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($communityGoods));
        }
    }

    private function del()
    {
        $ids = CommunityGoods::find()
            ->alias('cg')
            ->where([
                'cg.mall_id' => \Yii::$app->mall->id,
                'cg.activity_id' => $this->activity_id,
                'cg.id' => array_column($this->community, 'id'),
                'cg.is_delete' => 0,
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
        CommunityGoods::updateAll(
            [
                'is_delete' => 1,
                'deleted_at' => mysql_timestamp()
            ],
            [
                'activity_id' => $this->activity_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => array_column($this->community, 'id')
            ]
        );

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
            foreach ($detail['attr'] as &$item) {
                $extra_attr = CommunityGoodsAttr::findOne(['goods_id' => $item['goods_id'], 'attr_id' => $item['id'], 'is_delete' => 0]);
                $item['supply_price'] = empty($extra_attr) ? 0 : $extra_attr->supply_price;
            }
            $detail['supply_price'] = ($detail['use_attr'] == 1) ? 0 : $detail['attr'][0]['supply_price'];

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
