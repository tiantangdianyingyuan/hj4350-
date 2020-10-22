<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\Mall;
use app\plugins\integral_mall\models\IntegralMallGoods;
use app\plugins\integral_mall\models\IntegralMallGoodsAttr;
use app\plugins\integral_mall\Plugin;

/**
 * @property Mall $mall;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $integral_num;
    public $is_home;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['integral_num', 'is_home'], 'integer']
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'integral_num' => '积分'
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setGoods();
            // 将所有商品规格先删除
            IntegralMallGoodsAttr::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();
            $this->setGoodsService();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    protected function checkData()
    {
        if ($this->use_attr == 1) {
            foreach ($this->attr as $item) {
                if (!isset($item['integral_num']) || $item['integral_num'] <= 0) {
                    throw new \Exception('请填写规格积分价');
                }
            }
        } else {
            if ($this->integral_num <= 0) {
                throw new \Exception('请填写积分价');
            }
        }
    }

    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        $attr = IntegralMallGoodsAttr::findOne([
            'goods_attr_id' => $goodsAttr->id,
            'is_delete' => 0,
        ]);
        if (!$attr) {
            $attr = new IntegralMallGoodsAttr();
        }
        $integralNum = $this->use_attr ? $newAttr['integral_num'] : $this->integral_num;
        $attr->goods_attr_id = $goodsAttr->id;
        $attr->goods_id = $goodsAttr->goods_id;
        $attr->integral_num = $integralNum;
        $res = $attr->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($attr));
        }
    }

    public function setExtraGoods($goods)
    {
        $integralMallGoods = IntegralMallGoods::findOne([
            'goods_id' => $goods->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$integralMallGoods) {
            $integralMallGoods = new IntegralMallGoods();
            $integralMallGoods->mall_id = \Yii::$app->mall->id;
            $integralMallGoods->goods_id = $goods->id;
        }
        $integralMallGoods->is_home = $this->is_home;
        $integralMallGoods->integral_num = $this->integral_num;
        $res = $integralMallGoods->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($integralMallGoods));
        }
    }
}
