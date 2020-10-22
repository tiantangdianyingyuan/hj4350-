<?php


namespace app\forms\common\data_importing;

/**
 * Class DemoImporting
 * @package app\forms\common\data_importing
 */
class YyGoodsImporting extends BaseImporting
{
    public function import()
    {
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }
        foreach ($this->v3Data as $datum) {
            //格式化
            $datum['goods_num'] = $datum['stock'];
            $datum['confine_count'] = $datum['buy_limit'];
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
        $index = new YyGoodsModel($this->mall,[
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
