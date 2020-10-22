<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\GoodsAttr;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;

class GoodsEditForm extends BaseGoodsEdit
{
    public $buy_limit;
    public $virtual_miaosha_num;
    public $price;
    public $id;


    public function rules()
    {
        return array_merge(parent::rules(), [
            [['price'], 'safe'],
            [['buy_limit', 'virtual_miaosha_num'], 'integer'],
            [['id'], 'required'],
            [['virtual_miaosha_num'], 'integer', 'max' => 999999],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => '场次ID',
            'virtual_miaosha_num' => '已秒杀量',
            'price' => '秒杀价'
        ]);
    }

    public function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $this->executeSave();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function checkData()
    {
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先拉取商城商品');
        }

        if ($this->virtual_miaosha_num < 0) {
            throw new \Exception('已秒杀数不能小于0');
        }

        if ($this->use_attr == 1) {
            $goodsStock = 0;
            foreach ($this->attr as $item) {
                if (!isset($item['price']) || $item['price'] < 0) {
                    throw new \Exception('请填写规格价格');
                }
                $goodsStock += $item['stock'];
            }
            if ($goodsStock === '') {
                throw new \Exception('请填写规格库存');
            }
        } else {
            if ($this->price < 0 || $this->price === '') {
                throw new \Exception('请填写秒杀价格');
            }
        }
    }

    public function executeSave()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            // TODO 其时前端传商品ID就好
            $miaoshaGoods = MiaoshaGoods::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
            if (!$miaoshaGoods) {
                throw new \Exception('秒杀商品不存在');
            }
            $this->id = $miaoshaGoods->goods_id;

            $this->setGoods();
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();
            $this->setGoodsService();
            $this->setListener();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function setExtraGoods($goods)
    {
        $miaoshaGoods = MiaoshaGoods::findOne(['goods_id' => $goods->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        if (!$miaoshaGoods) {
            throw new \Exception('秒杀商品不存在');
        }
        $miaoshaGoods->buy_limit = $this->buy_limit;
        $miaoshaGoods->virtual_miaosha_num = $this->virtual_miaosha_num;
        $miaoshaGoods->goods_warehouse_id = $goods->goods_warehouse_id;
        $miaoshaGoods->goods_id = $goods->id;

        $res = $miaoshaGoods->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($miaoshaGoods));
        }
    }

    /**
     * @param GoodsAttr $goodsAttr
     * @param $newAttr
     * @throws \Exception
     */
    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        if (!$this->use_attr) {
            $goodsAttr->price = $this->price;
            $res = $goodsAttr->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goodsAttr));
            }
        }
    }
}
