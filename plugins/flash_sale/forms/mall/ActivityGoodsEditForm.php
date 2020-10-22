<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/11
 * Time: 15:35
 */

namespace app\plugins\flash_sale\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\models\ModelActiveRecord;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\FlashSaleGoodsAttr;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class ActivityGoodsEditForm extends Model
{
    public $activity_id;
    public $id;
    public $add;
    public $edit;
    public $del;

    public function rules()
    {
        return [
            [['activity_id'], 'required', 'on' => ['activity']],
            [['id'], 'integer'],
            [['add', 'edit', 'del'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'activity_id' => '活动id',
        ];
    }

    public function editGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 关闭日志存储
            ModelActiveRecord::$log = false;
            $this->handle();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function handle()
    {
        // 删除的
        if ($this->del) {
            $this->del();
        }

        if ($this->add) {
            // 活动现有的商品
            $nowIds = FlashSaleGoods::find()
                ->alias('fsg')
                ->where(
                    [
                        'fsg.mall_id' => Yii::$app->mall->id,
                        'fsg.activity_id' => $this->activity_id,
                        'fsg.is_delete' => 0
                    ]
                )
                ->leftJoin(['g' => Goods::tableName()], 'fsg.goods_id = g.id')
                ->select('g.goods_warehouse_id')
                ->column();

            // 前端上传上来的商品
            $existIds = GoodsWarehouse::find()
                ->alias('gw')
                ->leftJoin(['g' => Goods::tableName()], 'g.goods_warehouse_id = gw.id')
                ->where(['g.id' => array_column($this->add, 'id')])
                ->select('gw.id')
                ->column();

            $intersection = array_intersect($nowIds, $existIds);
            if (!empty($intersection) && is_array($intersection)) {
                $name = GoodsWarehouse::findOne(['id' => array_values($intersection)[0]]);
                throw new Exception('商品' . $name['name'] . '重复');
            }
            foreach ($this->add as $item) {
                //新增
                $this->add($item);
            }
            unset($item);
        }

        if ($this->edit) {
            foreach ($this->edit as $item) {
                //新增
                $this->edit($item);
            }
            unset($item);
        }
    }

    private function add($item)
    {
        $goods = new GoodsEditForm();
        $common = CommonGoods::getCommon();
        $goodsDeteil = $common->getGoodsDetail($item['id']);
        $goods->attributes = $goodsDeteil;
        $goods->discount = 10;
        $goods->cut = 0;
        $goods->attrGroups = ArrayHelper::toArray($goodsDeteil['attr_groups']);

        $flashSaleGoods = new FlashSaleGoods();
        $goods->status = 1;
        $goods->setSign('flash_sale');
        $goods->save();
        $flashSaleGoods->goods_id = $goods->goods_id;
        $flashSaleGoods->mall_id = Yii::$app->mall->id;
        $flashSaleGoods->sort = $item['sort'] ?? 100;
        $flashSaleGoods->activity_id = $this->activity_id;
        $flashSaleGoods->status = 1;
        $flashSaleGoods->type = $item['type'] ?? 1;
        $res = $flashSaleGoods->save();
        if (!$res) {
            throw new Exception($this->getErrorMsg($flashSaleGoods));
        }
    }

    private function edit($item)
    {
        /** @var FlashSaleGoods $flashSaleGoods */
        $flashSaleGoods = FlashSaleGoods::find()->where(
            [
                'goods_id' => $item['id'] ?? 0,
                'mall_id' => Yii::$app->mall->id,
                'activity_id' => $this->activity_id
            ]
        )->one();

        if (empty($flashSaleGoods)) {
            throw new Exception('该商品不存在');
        }

        $goods = new GoodsEditForm();
        $common = CommonGoods::getCommon();
        $goodsDeteil = $common->getGoodsDetail($flashSaleGoods->goods_id);
        $goods->attributes = $goodsDeteil;
        $goods->attrGroups = $goodsDeteil['attr_groups'];
        $goods->discount = 10;
        $goods->cut = 0;
        $goods->sort = $item['sort'] ?? 100;
        $attr = $item['attr'];
        if (isset($attr) && !empty($attr)) {
            $priceList = array_column($goodsDeteil['attr'], 'member_price', 'id');
            foreach ($attr as &$v) {
                $v['member_price'] = $priceList[$v['id']];
            }
            unset($v);
        } else {
            $attr = ArrayHelper::toArray($goodsDeteil['attr']);
        }
        $goods->attr = $attr;
        $goods->id = $flashSaleGoods->goods_id;
        $goods->status = 1;
        $goods->setSign('flash_sale');
        if (!$goods->save()) {
            throw new Exception($this->getErrorMsg($flashSaleGoods));
        }
        $flashSaleGoods->goods_id = $goods->goods_id;
        $flashSaleGoods->mall_id = Yii::$app->mall->id;
        $flashSaleGoods->sort = $item['sort'] ?? 100;
        $flashSaleGoods->activity_id = $this->activity_id;
        $flashSaleGoods->status = 1;
        $res = $flashSaleGoods->save();
        if (!$res) {
            throw new Exception($this->getErrorMsg($flashSaleGoods));
        }
    }

    private function del()
    {
        $ids = FlashSaleGoods::find()
            ->alias('fsg')
            ->where(
                [
                    'fsg.mall_id' => Yii::$app->mall->id,
                    'fsg.activity_id' => $this->activity_id,
                    'fsg.id' => array_column($this->del, 'id'),
                    'fsg.is_delete' => 0,
                ]
            )
            ->select(['goods_id'])
            ->column();

        Goods::updateAll(
            [
                'is_delete' => 1,
                'deleted_at' => mysql_timestamp()
            ],
            [
                'mall_id' => Yii::$app->mall->id,
                'id' => $ids,
                'is_delete' => 0,
            ]
        );

        // 删除商品关联
        FlashSaleGoods::updateAll(
            [
                'is_delete' => 1,
                'deleted_at' => mysql_timestamp()
            ],
            [
                'activity_id' => $this->activity_id,
                'is_delete' => 0,
                'mall_id' => Yii::$app->mall->id,
                'id' => array_column($this->del, 'id')
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
            //多规数据
            $attrList = array_column($detail['attr'], 'id');
            $extra = FlashSaleGoodsAttr::find()->where(['goods_attr_id' => $attrList])->all();
            foreach ($detail['attr'] as &$item) {
                foreach ($extra as $value) {
                    if ($item['id'] == $value['goods_attr_id']) {
                        $item['extra'] = $value;
                    }
                }
            }

            if ($detail) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'detail' => $detail
                    ]
                ];
            }

            throw new Exception('请求失败');
        } catch (Exception $e) {
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
