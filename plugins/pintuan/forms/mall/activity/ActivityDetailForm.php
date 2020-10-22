<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use yii\helpers\ArrayHelper;

class ActivityDetailForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '活动ID'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $common = CommonGoods::getCommon();
        $goods = $common->getGoodsDetail($this->id);
        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        /** @var PintuanGoods $ptGoods */
        $ptGoods = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'goods_id' => $this->id,
            'pintuan_goods_id' => 0
        ])->one();
        if (!$ptGoods) {
            throw new \Exception('拼团商品不存在');
        }

        $goodsIds = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'pintuan_goods_id' => $ptGoods->id
        ])->select('goods_id');
        $groupGoods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $goodsIds
        ])->with('attr', 'oneGroups')->all();
        $groupList = [];
        /**  @var Goods $gItem */
        foreach ($groupGoods as $key => $gItem) {
            $newGoods = $common->transformAttr($gItem);
            $newItem = $gItem->oneGroups ? ArrayHelper::toArray($gItem->oneGroups) : [];
            $newItem['attr'] = $newGoods['attr'];
            $newItem['shareLevelList'] = $common->getGoodsShare($gItem->id, true);
            if ($gItem->use_attr != 1) {
               $newItem['member_price'] = $newItem['attr'][0]['member_price'];
            }
            $groupList[] = $newItem;
            if ($key == 0) {
                $groupMinPrice = 0;
                $groupMaxPrice = 0;
                foreach ($gItem->attr as $attr) {
                    $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $attr->price) : $attr->price;
                    $groupMaxPrice = $groupMaxPrice ? max($groupMaxPrice, $attr->price) : $attr->price;
                }
                $goods['group_min_price'] = $groupMinPrice;
                $goods['group_max_price'] = $groupMaxPrice;
            }
        }

        $goods['plugin'] = [
            'is_alone_buy' => $ptGoods->is_alone_buy,
            'goods_id' => $ptGoods->goods_id,
            'start_time' => $ptGoods->start_time,
            'end_time' => $ptGoods->end_time,
            'groups_restrictions' => $ptGoods->groups_restrictions,
            'is_sell_well' => $ptGoods->is_sell_well,
            'goods_groups_count' => count($groupList),
            'is_auto_add_robot' => $ptGoods->is_auto_add_robot,
            'add_robot_time' => $ptGoods->add_robot_time,
        ];
        $goods['group_list'] = $groupList;

        $newGoods = Goods::find()->where(['id' => $this->id])->with('pintuanGoods')->one();
        $goods['status_cn'] = (new Goods())->getActivityStatus($newGoods);


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'detail' => $goods
            ]
        ];
    }
}