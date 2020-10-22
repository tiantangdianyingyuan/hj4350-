<?php


namespace app\forms\common\data_importing;

/**
 * Class DemoImporting
 * @package app\forms\common\data_importing
 */
class StepGoodsImporting extends BaseImporting
{
    public function import()
    {
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }
        foreach ($this->v3Data as $datum) {
            $this->save($datum);
        }
        return true;
    }

    /**
     * @param $datum
     * @return bool
     * 单条数据添加
     * @throws \Exception
     */
    protected function save($datum)
    {
        $index = new StepGoodsModel($this->mall,[
            'goodsWareHouse',
            'mallGoods',
            'goods',
            'goodsAttr',
            'goodsCat',
            'goodsCatRelation',
            'goodsServices',
            'goodsServicesRelation',
        ]);
        $index->set($datum);
        return true;
    }
}
