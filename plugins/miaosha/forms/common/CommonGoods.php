<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/11
 * Time: 9:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\miaosha\forms\common;


use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;
use yii\db\Query;

class CommonGoods extends Model
{
    public static function getCommon()
    {
        $model = new self();
        return $model;
    }

    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }

        /* @var MiaoshaGoods[] $miaoshaGoodsList */
        $goodsIdList = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $miaoshaGoodsList = MiaoshaGoods::find()->with('goods.goodsWarehouse')
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'goods_id' => $goodsIdList, 'activity_id' => 0])
            ->andWhere([
                'or',
                ['>', 'open_date', date('Y-m-d')],
                [
                    'and',
                    ['open_date' => date('Y-m-d')],
                    ['>=', 'open_time', date('H')]
                ]
            ])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($miaoshaGoodsList as $miaoshaGoods) {
            $newItem = $common->getDiyBack($miaoshaGoods->goods);
            $newItem = array_merge($newItem, [
                'open_time' => $miaoshaGoods->open_time,
                'open_date' => $miaoshaGoods->open_date,
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|MiaoshaGoods[]
     */
    public function getList($goodsIdList)
    {
        $miaoshaGoods = MiaoshaGoods::find()
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'goods_id' => $goodsIdList])
            ->andWhere([
                'or',
                ['>', 'open_date', date('Y-m-d')],
                [
                    'and',
                    ['open_date' => date('Y-m-d')],
                    ['>', 'open_time', date('H')]
                ]
            ])
            ->all();
        return $miaoshaGoods;
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '秒杀',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            $query = Goods::find()->alias('g')->where([
                'g.is_delete' => 0, 'g.mall_id' => \Yii::$app->mall->id, 'g.status' => 1
            ])->leftJoin(['mg' => MiaoshaGoods::tableName()], 'mg.goods_id = g.id')
                ->andWhere(['mg.is_delete' => 0, 'mg.activity_id' => 0])
                ->orderBy(['mg.open_date' => SORT_ASC, 'mg.open_time' => SORT_ASC, 'g.sort' => SORT_ASC])
                ->andWhere([
                    'or',
                    ['>', 'mg.open_date', date('Y-m-d')],
                    [
                        'and',
                        ['mg.open_date' => date('Y-m-d')],
                        ['>=', 'mg.open_time', date('H')]
                    ],
                ])
                ->select('g.*,mg.open_date,mg.open_time');

            $timeCount = (new Query())->from(['t' => $query])->select('sum(1) as count, t.open_date, t.open_time')
                ->groupBy(['t.open_date', 't.open_time'])->limit(1)->one();

            $list = $query->with(['miaoshaGoods', 'goodsWarehouse'])->limit($timeCount['count'])->all();
            $newList = [];
            /* @var Goods[] $list */
            foreach ($list as $k => $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $newList[$k] = $apiGoods->getDetail();
            }
            $is_current = 1;
            if (date('Y-m-d') != $timeCount['open_date'] && date('H') != $timeCount['open_time']) {
                $is_current = 0;
            }
            return [
                'open_date' => $timeCount['open_date'],
                'open_time' => $timeCount['open_time'],
                'date_time' => strtotime($timeCount['open_date'] . ' ' . $timeCount['open_time'] . ':00:00') + 3600,
                'is_current' => $is_current,
                'list' => $newList
            ];
        } else {
            throw new \Exception('无效的数据');
        }
    }
}
