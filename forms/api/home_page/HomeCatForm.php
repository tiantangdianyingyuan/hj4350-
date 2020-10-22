<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\home_page;

use app\forms\common\CommonAppConfig;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use Yii;

class HomeCatForm extends Model
{
    public static $limit;

    public function getCatGoods($catIds, $isAllCat)
    {
        $mallSetting = CommonAppConfig::getAppCatStyle();
        self::$limit = $mallSetting['cat_goods_count'];
        $query = GoodsCats::find()->where([
            'is_delete' => 0,
            'status' => 1,
            'is_show' => 1,
            'mch_id' => 0,
            'mall_id' => Yii::$app->mall->id,
            'parent_id' => 0,
        ])->with(['child' => function ($query) {
            $query->andWhere(['status' => 1, 'is_show' => 1])
                ->with(['child' => function ($query) {
                    $query->andWhere(['status' => 1, 'is_show' => 1])->orderBy('sort ASC');
                }])->orderBy('sort ASC');
        }]);
        if ($isAllCat) {
            $list = $query->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])->all();
        } else {
            $list = $query->andWhere(['id' => $catIds])->all();
        }
        $catList = $this->getCatList($list);
        $form = new CommonGoodsList();
        $newList = [];
        /** @var GoodsCats[] $list */
        foreach ($list as $item) {
            $goodsWarehouseId = GoodsCatRelation::find()->where(['cat_id' => $catList[$item->id], 'is_delete' => 0])
                ->select('goods_warehouse_id');
            /* @var Goods[] $goodsList */
            $goodsList = Goods::find()->with(['goodsWarehouse.goodsCatRelation', 'mallGoods', 'attr'])
                ->where([
                    'mall_id' => Yii::$app->mall->id, 'is_delete' => 0, 'sign' => ['mch', ''], 'status' => 1,
                    'goods_warehouse_id' => $goodsWarehouseId
                ])->limit(self::$limit)->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all();
            $arr = [];
            $arr['key'] = 'cat';
            $arr['name'] = $item->name;
            $arr['relation_id'] = $item->id;
            $arr['cat_pic_url'] = $item->pic_url;
            $arr['list_style'] = intval($mallSetting['cat_goods_cols']);
            $newGoods = [];
            foreach ($goodsList as $gItem) {
                $goods = $form->getGoodsData($gItem);
                unset($goods['attr']);
                unset($goods['attr_groups']);
                $newGoods[] = $goods;
            }
            $arr['goods'] = $newGoods;
            $newList[] = $arr;
        }
        return $newList;
    }

    public function getNewCatGoods($data, $catGoods)
    {
        if ($data['relation_id'] == 0) {
            return $catGoods;
        } else {
            foreach ($catGoods as $catGood) {
                if ($catGood['relation_id'] == $data['relation_id']) {
                    return [$catGood];
                }
            }
        }

        return [];
    }

    /**
     * @param $list
     * @param $index
     * @return array
     * 获取特定的分类数据结构
     */
    private function getCatList($list, $index = 1)
    {
        $res = [];
        if (is_array($list)) {
            foreach ($list as $item) {
                $result = [];
                $result[] = intval($item->id);
                if (isset($item->child)) {
                    $index++;
                    $result = array_merge($result, $this->getCatList($item->child, $index));
                    $index--;
                }
                if ($index == 1) {
                    $res[$item->id] = $result;
                } else {
                    $res = array_merge($res, $result);
                }
            }
        }
        return $res;
    }
}
