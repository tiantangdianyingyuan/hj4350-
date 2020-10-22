<?php

namespace app\forms\common\data_importing;

class StepGoodsModel extends GoodsBaseModel
{
    public function set(array $arr)
    {
        try {
            $transaction = \Yii::$app->db->beginTransaction();
            foreach ($this->tables as $k => $v) {
                if (array_search($v, $this->table) !== false) {
                    call_user_func(array($this, $v), $arr);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
        }
    }
}