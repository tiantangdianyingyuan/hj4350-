<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\goods;

use app\forms\common\goods\CommonGoods;
use app\forms\mall\export\MallGoodsExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsCats;

class GoodsListForm extends BaseGoodsList
{
    public $choose_list;
    public $flag;

    public $is_show_attr;
    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    public function rules()
    {


        return array_merge(parent::rules(),[
            ['is_show_attr', 'integer'],
        ]);
    }

    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign'   => \Yii::$app->user->identity->mch_id > 0 ? 'mch' : '',
            'g.mch_id' => \Yii::$app->user->identity->mch_id,
        ])->with('mallGoods');
        if (\Yii::$app->user->identity->mch_id > 0) {
            $query->with('mchGoods', 'goodsWarehouse.mchCats');
        }

        if ($this->flag == "EXPORT") {
            if ($this->choose_list && count($this->choose_list) > 0) {
                $query->andWhere(['g.id' => $this->choose_list]);
            }
            $new_query = clone $query;

            $exp = new MallGoodsExport();
            $res = $exp->export($new_query);
            return $res;
        }

        return $query;
    }

    public function handleGoodsData($goods)
    {
        $newItem                               = [];
        $newItem['mallGoods']                  = [];
        $newItem['mallGoods']['id']            = $goods->mallGoods->id;
        $newItem['mallGoods']['is_quick_shop'] = $goods->mallGoods->is_quick_shop;
        $newItem['mallGoods']['is_sell_well']  = $goods->mallGoods->is_sell_well;
        $newItem['mallGoods']['is_negotiable'] = $goods->mallGoods->is_negotiable;
        $newItem                               = $this->mchGoodsData($goods, $newItem);

        //todo 兑换中心使用规格 可能有重复查询
        if($this->is_show_attr == 1){
            $common = CommonGoods::getCommon();
            $detail = $common->getGoodsDetail($goods->id,false);
            $newItem['attr'] = $detail['attr'];
            $newItem['attr_groups'] = $detail['attr_groups'];
        }
        return $newItem;
    }

    private function mchGoodsData($goods, $newItem)
    {

        $newItem['mchCats'] = [];
        if ($goods->goodsWarehouse && $goods->goodsWarehouse->mchCats) {
            $newCats = [];
            /** @var GoodsCats $cat */
            foreach ($goods->goodsWarehouse->mchCats as $cat) {
                $newCatItem         = [];
                $newCatItem['id']   = $cat->id;
                $newCatItem['name'] = $cat->name;
                $newCats[]          = $newCatItem;
            }
            $newItem['mchCats'] = $newCats;
        }

        $newItem['mchGoods'] = [];
        if (\Yii::$app->user->identity->mch_id > 0 && $goods->mchGoods) {
            $newItem['mchGoods']['id']     = $goods->mchGoods->id;
            $newItem['mchGoods']['sort']   = $goods->mchGoods->sort;
            $newItem['mchGoods']['status'] = $goods->mchGoods->status;
            $newItem['mchGoods']['remark'] = $goods->mchGoods->remark;
        }

        return $newItem;
    }
}
