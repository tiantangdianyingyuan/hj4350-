<?php

namespace app\plugins\community\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use app\plugins\community\Plugin;
use yii\db\Exception;

class GoodsEditForm extends BaseGoodsEdit
{
    public $goods_id;
    public $supply_price;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['supply_price'], 'number', 'min' => 0],
            [['supply_price'], 'number', 'max' => 9999999]
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->setGoods();
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => $e->getTraceAsString()
            ];
        }
    }

    public function setExtraGoods($goods)
    {
        $this->goods_id = $goods->id;

    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    /**
     * 供货价表数据比对操作
     * @throws Exception
     */
    protected function setAttr()
    {
        //判断供货价是否大于售价
        if ((int)$this->use_attr === 0) {
            if ($this->price < $this->supply_price) {
                throw new Exception('供货价不能大于购买价');
            }
        }

        // 是否为新增，增加判断条件，排除上下架和排序调整操作
        if (!$this->isNewRecord && isset($this->attr[0]['supply_price'])) {
            CommunityGoodsAttr::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        }
        parent::setAttr(); // TODO: Change the autogenerated stub
    }

    protected function setDefaultAttr()
    {
        parent::setDefaultAttr(); // TODO: Change the autogenerated stub
        //判断供货价是否大于售价
        if ($this->price < $this->supply_price) {
            $this->supply_price = $this->price;
//            throw new Exception('供货价不能大于售价');
        }
        $this->newAttrs[0]['supply_price'] = empty($this->supply_price) ? $this->price : $this->supply_price;
    }

    /**
     * 添加/修改供货价
     * @param $goodsAttr
     * @param $newAttr
     * @return array|bool
     */

    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        try {
            if (!$this->isNewRecord && !isset($newAttr['supply_price'])) {
                return true;
            }
            //判断供货价是否大于售价
            if ((!$this->isNewRecord && $newAttr['price'] < $newAttr['supply_price']) || ($this->isNewRecord && !isset($newAttr['supply_price']))) {
                $newAttr['supply_price'] = $newAttr['price'];
//                    throw new Exception('供货价不能大于售价');
            }

            // 判断规格是需要新增还是更新
            $attr_id = $goodsAttr->id;
            if ($this->goods->id) {
                $goodsAttr = CommunityGoodsAttr::findOne([
                    'attr_id' => $attr_id,
                    'goods_id' => $this->goods->id
                ]);
            }
            if ($goodsAttr) {
                $goodsAttr->is_delete = 0;
            } else {
                $goodsAttr = new CommunityGoodsAttr();
            }
            $goodsAttr->goods_id = $this->goods->id;
            $goodsAttr->attr_id = $attr_id;
            $goodsAttr->supply_price = $newAttr['supply_price'];

            $res = $goodsAttr->save();
            if (!$res) {
                throw new Exception($this->getErrorMsg($goodsAttr));
            }
            return true;
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}