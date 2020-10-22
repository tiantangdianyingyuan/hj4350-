<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

declare(strict_types=1);

namespace app\forms\common\goods;

use app\forms\mall\goods\hot\SearchDestroy;
use app\models\Goods;
use app\models\GoodsHotSearch;
use app\models\Model;

class CommonHotSearch extends Model
{
    private const PAGESIZE = 10;

    public static function getHotSearchOne(int $goods_id, string $type = null, int $is_delete = 0): ?GoodsHotSearch
    {
        $where = [
            'goods_id' => $goods_id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => $is_delete,
        ];
        is_null($type) || $where['type'] = $type;
        return GoodsHotSearch::findOne($where);
    }

    public static function getGoodsOne(int $goods_id): ?Goods
    {
        return Goods::findOne([
            'id' => $goods_id,
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ]);
    }

    public function format($arr, $type): array
    {
        return array_map(function ($item, $index) use ($type) {
            if ($type === 'goods') {
                $id = 0;
                $goods_id = $item->id;

                $title = $item->name;
                if ($model = self::getHotSearchOne($goods_id, GoodsHotSearch::TYPE_GOODS)) {
                    $title = $model->title;
                }

                $title = mb_substr($title, 0, 16);
                $sort = $index + 1; //不准确
                $goods_name = $item->name;
                $cover_pic = $item->coverPic;
                $detail_count = $item->detail_count;
            }
            if ($type === 'hot-search') {
                extract(\yii\helpers\ArrayHelper::toArray($item));
                $goods_name = $item->goods->name;
                $cover_pic = $item->goods->coverPic;
                $detail_count = $item->goods->detail_count;
            }
            return [
                'id' => $id,
                'goods_id' => $goods_id,
                'title' => $title,
                'sort' => $sort,
                'url' => sprintf('/pages/goods/goods?id=%s', $goods_id),
                'goods_name' => $goods_name,
                'cover_pic' => $cover_pic,
                'detail_count' => $detail_count,
                'type' => $type,
            ];
        }, $arr, array_keys($arr));
    }

    private function getHotSearch(): array
    {
        $hotSearch = GoodsHotSearch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'type' => GoodsHotSearch::TYPE_HOT_SEARCH,
        ])->with('goods')
            ->orderBy(['sort' => SORT_DESC])
            ->limit(static::PAGESIZE)
            ->all();
        return $this->format($hotSearch, 'hot-search');
    }

    private function getGoods(): array
    {
        $extra = GoodsHotSearch::find()->select('goods_id')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 1,
            'type' => GoodsHotSearch::TYPE_GOODS,
        ])->column();

        $goods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
            'sign' => '',
        ])->andWhere(['not in', 'id', $extra])
            ->orderBy(['detail_count' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(static::PAGESIZE * 2)
            ->all();
        return $this->format($goods, 'goods');
    }

    public function getAll(): array
    {
        $default = array_fill(0, static::PAGESIZE, null);

        $ids = [];
        $hotSearch = $this->getHotSearch();
        for ($i = 0; $i < count($hotSearch); $i++) {
            $index = intval($hotSearch[$i]['sort']);
            $default[$index - 1] = $hotSearch[$i];
            array_push($ids, $hotSearch[$i]['goods_id']);
        }

        if (count($ids) < static::PAGESIZE) {
            $goods = $this->getGoods();
            for ($j = 0; $j < count($default); $j++) {
                if (!is_null($default[$j])) {
                    continue;
                }
                $first = array_shift($goods);
                while ($first && array_search($first['goods_id'], $ids) !== false) {
                    $first = array_shift($goods);
                }
                if ($first) {
                    $first['sort'] = $j + 1;
                    $default[$j] = $first;
                }
            }
        }
        return array_filter($default, function ($item) {
            return !is_null($item);
        });
    }
}
