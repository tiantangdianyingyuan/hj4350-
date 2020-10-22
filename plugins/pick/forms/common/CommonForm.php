<?php

/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/13
 * Time: 9:40
 */

namespace app\plugins\pick\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\pick\models\Goods;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickGoods;
use Yii;

class CommonForm extends Model
{
    /**
     * 判断活动所处时间段
     * @param $activity
     * @return int
     * 1:未开始
     * 2:进行中
     * 3:已结束
     * 0:下架中
     */
    public static function timeSlot($activity)
    {
        if ($activity['status'] == 0) {
            return '0';
        }
        $now = time();
        $start = strtotime($activity['start_at']);
        $end = strtotime($activity['end_at']);
        if ($now < $start) {
            return '1';
        } elseif ($now >= $start && $now <= $end) {
            return '2';
        } elseif ($now > $end) {
            return '3';
        }
    }

    /**
     * @param $goods
     * @return int
     */
    public static function getStock($goods)
    {
        $goodNumCount = 0;
        foreach ($goods['attr'] as $item) {
            $goodNumCount += $item['stock'];
        }
        return (string)$goodNumCount;
    }

    /**
     * 商品列表
     * @param string $keyword
     * @param $goods_id
     * @return array
     */
    public static function getList($keyword = '', $goods_id = '')
    {
        $activity = PickActivity::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['<=', 'start_at', date('y-m-d H:i:s')])
            ->andWhere(['>=', 'end_at', date('y-m-d H:i:s')])
            ->one();
        if (empty($activity)) {
            return [];
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\pick\models\Goods';
        // $form->page = $pagination['page'];
        $form->sign = 'pick';
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->status = 1;
        $form->keyword = $keyword;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;
        if ($goods_id) {
            $query->andWhere(['<>', 'g.id', $goods_id]);
        }
        $query->leftJoin(['pg' => PickGoods::tableName()], 'pg.goods_id = g.id')
            ->andWhere(['pg.pick_activity_id' => $activity->id, 'pg.is_delete' => 0])
            ->addSelect('pg.pick_activity_id');

        $pagination = null;
        $list = $query->orderBy('sort ASC')->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            $item['goods_stock'] = 0;
            foreach ($item['attr'] as $value) {
                $item['goods_stock'] += $value['stock'];
            }
            // $item['goods_stock'] = $goodsStock;
            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];
            $item['sales'] = '已售' . ($item['sales'] + $item['virtual_sales']) . '件';
            $item['page_url'] = '/plugins/pick/detail/detail?goods_id=' . $item['id'];
            if ($item['price'] > 0) {
                $item['price_content'] = '￥' . price_format($item['price']);
            } else {
                $item['price_content'] = '免费';
            }
            $item['video_url'] = Video::getUrl(trim($item['goodsWarehouse']['video_url']));
        }

        return ['list' => $list, 'activity' => $activity, 'pagination' => $pagination];
    }
}
