<?php

/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\common;

use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityGoods;

class CommonForm extends Model
{
    /**
     * 判断活动所处时间段
     * @param $activity
     * @return int
     * 0:未开始
     * 1:进行中
     * 2:已结束
     * 3:下架中
     */
    public static function timeSlot($activity)
    {
        $status = -1;
        $now = time();
        $start = strtotime($activity['start_at']);
        $end = strtotime($activity['end_at']);
        if ($now < $start) {
            $status = 0;
        } elseif ($now >= $start && $now <= $end) {
            $status = 1;
        } elseif ($now > $end) {
            $status = 2;
        }

        if ($activity['status'] == 0 && $status != 2) {
            $status = 3;
        }
        return $status;
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
        $activity = CommunityActivity::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['<=', 'start_at', date('y-m-d H:i:s')])
            ->andWhere(['>=', 'end_at', date('y-m-d H:i:s')])
            ->one();
        if (empty($activity)) {
            return [];
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\community\models\Goods';
        // $form->page = $pagination['page'];
        $form->sign = 'community';
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->status = 1;
        $form->keyword = $keyword;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;
        if ($goods_id) {
            $query->andWhere(['<>', 'g.id', $goods_id]);
        }
        $query->leftJoin(['pg' => CommunityGoods::tableName()], 'pg.goods_id = g.id')
            ->andWhere(['pg.activity_id' => $activity->id, 'pg.is_delete' => 0])
            ->addSelect('pg.stock as goods_stock,pg.activity_id');

        $pagination = null;
        $list = $query->orderBy('sort ASC')->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            // $item['goods_stock'] = $goodsStock;
            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];
            $item['sales'] = '已售：' . ($item['sales'] + $item['virtual_sales']) . '件';
            $item['page_url'] = '/plugins/community/detail/detail?goods_id=' . $item['id'];
        }

        return ['list' => $list, 'activity' => $activity, 'pagination' => $pagination];
    }

    /**
     * @param $num
     * @return string
     */
    public static function setNum($num)
    {
        switch ($num) {
            case $num > 0 && $num < 10:
                $num = '#00' . $num;
                break;
            case $num >= 10 && $num < 100:
                $num = '#0' . $num;
                break;
            case $num >= 100:
                $num = '#' . $num;
                break;
        }

        return $num;
    }
}
