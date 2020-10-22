<?php

namespace app\forms\common\data_importing;

use app\models\GoodsCatRelation;

class YyGoodsModel extends GoodsBaseModel
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

    protected function goodsCatRelation($params)
    {
        if (!$this->goods_warehouse_id) {
            throw new \Exception('ERROR goods_warehouse_id');
        }
        if (!isset(YyCatImporting::$yyCatIds[$params['cat_id']])) {
            return true;
        }

        $goodsCatRelation = new GoodsCatRelation();
        $goodsCatRelation->goods_warehouse_id = $this->goods_warehouse_id;
        $goodsCatRelation->cat_id = YyCatImporting::$yyCatIds[$params['cat_id']];
        $goodsCatRelation->is_delete = 0;
        $res = $goodsCatRelation->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($goodsCatRelation));
        }
        return true;
    }
}