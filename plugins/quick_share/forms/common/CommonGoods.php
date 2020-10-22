<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\common;


use app\models\Model;
use app\plugins\quick_share\models\QuickShareGoods;

class CommonGoods extends Model
{

    public static function getGoods($id = null, $goods_id = null, $status = null, $mall_id = null): QuickShareGoods
    {
        !isset($mall_id) && $mall_id = \Yii::$app->mall->id;

        $where = is_null($id) ? ['goods_id' => $goods_id] : ['id' => $id];
        $query = QuickShareGoods::find()->where(array_merge($where, [
            'mall_id' => $mall_id,
            'is_delete' => 0,
        ]));

        isset($status) && $query->andWhere(['status' => $status]);
        $model = $query->one();

        if (!$model) {
            throw new \Exception('数据不存在');
        }
        return $model;
    }

    /**
     * @return array
     */
    public function getGoodsList()
    {
        $limit = 20;
        $func = func_get_arg(0);
        $query = QuickShareGoods::find()->select('s.*')->alias('s')->where([
            's.mall_id' => \Yii::$app->mall->id,
            's.is_delete' => 0,
        ])->joinWith(['goods g']);
        if (empty($func['type'])) {
            $query->andWhere(['OR', ['g.is_delete' => 0], ['s.goods_id' => 0]]);
        } else {
            $func['type'] === 'goods' ? $query->andWhere(['g.is_delete' => 0]) : $query->andWhere(['s.goods_id' => 0]);
        }

        $top = [];
        isset($func['is_app']) && $top = ['s.is_top' => SORT_DESC];

        isset($func['status']) && $query->andWhere(['s.status' => $func['status']]);
        !empty($func['keyword']) && $query->andWhere(['like', 's.share_text', $func['keyword']]);
        !empty($func['time']) && $query->andWhere(['>=', 's.created_at', $func['time'][0]])->andWhere(['<=', 's.created_at', $func['time'][1]]);

        //1 新商品 3 新素材 5销量
        switch ($func['sort']) {
            case 1:
                $orderBy = ['g.sort' => SORT_ASC, 'g.id' => SORT_ASC];
                break;
            case 2:
                $orderBy = ['g.sort' => SORT_DESC, 'g.id' => SORT_DESC];
                break;
            case 3:
                $orderBy = ['s.material_sort' => SORT_ASC, 's.id' => SORT_ASC];
                break;
            case 4:
                $orderBy = ['s.material_sort' => SORT_DESC, 's.id' => SORT_DESC];
                break;
            case 5:
                $query->addSelect(["total_sales" => "`g`.`sales` + `g`.`virtual_sales`"]);
                $orderBy = ['total_sales' => SORT_ASC, 's.id' => SORT_ASC];
                break;
            case 6:
                $query->addSelect(["total_sales" => "`g`.`sales` + `g`.`virtual_sales`"]);
                $orderBy = ['total_sales' => SORT_DESC, 's.id' => SORT_DESC];
                break;
            default:
                $orderBy = ['s.id' => SORT_DESC];
                break;
        }

        isset($func['limit']) && $limit = $func['limit'];
        $list = $query
            ->page($pagination, $limit)
            ->with(['goods.goodsWarehouse'])
            ->groupBy('s.id')
            ->orderBy(array_merge($top, $orderBy))
            ->asArray()
            ->all();

        $newData = [];
        foreach ($list as $item) {
            $newItem = $item['goods'] ?: [];
            $newData[] = array_merge($newItem, [
                'id' => (int)$item['id'],
                'material_sort' => (int)$item['material_sort'],
                'goodsWarehouse' => $item['goods']['goodsWarehouse'] ?? [],
                'name' => $item['goods']['goodsWarehouse']['name'] ?? '',
                'mall_name' => \Yii::$app->mall->name,
                'goods_id' => (int)$item['goods_id'],
                'is_top' => (int)$item['is_top'],
                'share_text' => $item['share_text'],
                'share_pic' => \yii\helpers\BaseJson::decode($item['share_pic']),
                'status' => (int)$item['status'],
                'material_cover_url' => $item['material_cover_url'],
                'material_video_url' => $item['material_video_url'],
                'format_time' => date('Y-m-d', strtotime($item['created_at'])),
                'plugins' => [
                    'created_at' => $item['created_at']
                ],
                'app_share_pic' => $item['goods']['app_share_pic'] ?? '',
                'app_share_title' => $item['goods']['app_share_title'] ?? '',
            ]);
        }
        return [$newData, $pagination];
    }
}