<?php

namespace app\plugins\pond\forms\mall;

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

        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->setGoods();
            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->setCoupon();
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
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
