<?php

namespace app\plugins\pick\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;

class GoodsEditForm extends BaseGoodsEdit
{
    public $goods_id;

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
            throw new \Exception($e);
        }
    }

    public function setExtraGoods($goods)
    {
        $this->goods_id = $goods->id;

    }

    protected function setGoodsSign()
    {
        return \Yii::$app->plugin->getCurrentPlugin()->getName();
    }
}
