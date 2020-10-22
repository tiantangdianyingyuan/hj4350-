<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 17:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\common\goods;


use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\events\BargainGoodsEvent;
use app\plugins\bargain\handlers\HandlerRegister;
use app\plugins\bargain\models\BargainGoods;

/**
 * @property Mall $mall
 */
class CommonBargainGoods extends Model
{
    public $mall;

    /**
     * @param null $mall
     * @return CommonBargainGoods
     */
    public static function getCommonGoods($mall = null)
    {
        $model = new CommonBargainGoods();
        $model->mall = $mall ? $mall : \Yii::$app->mall;
        return $model;
    }

    /**
     * @param integer $goodsId
     * @return array|\yii\db\ActiveRecord|null
     * 获取某个ID的砍价商品信息
     */
    public function getGoods($goodsId)
    {
        $bargainGoods = BargainGoods::find()->with(['goods', 'goodsWarehouse'])
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0,
                'goods_id' => $goodsId])->one();

        return $bargainGoods;
    }

    /**
     * @param $page
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * 获取小程序端商品列表
     */
    public function getApiGoodsList($page, $limit = 10)
    {
        $nowDate = mysql_timestamp();
        $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->select('id');
        $goods = Goods::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'goods_warehouse_id' => $goodsWarehouseId]);
        $bargainGoodsList = BargainGoods::find()->alias('bg')->with(['goods', 'userOrderList'])
            ->where(['bg.mall_id' => $this->mall->id, 'bg.is_delete' => 0])
            ->andWhere(['>=', 'bg.end_time', $nowDate])
            ->andWhere(['<=', 'bg.begin_time', $nowDate])
            ->leftJoin(['g' => $goods], 'g.id=bg.goods_id')
            ->andWhere(['g.is_delete' => 0, 'g.status' => 1])
            ->orderBy(['g.sort' => SORT_ASC, 'g.created_at' => SORT_DESC])
            ->apiPage($limit, $page)->all();

        return $bargainGoodsList;
    }

    public function getBargainNum()
    {
        return null;
    }

    /**
     * @param BargainGoods $bargainGoods
     * @return GoodsAttr|null
     */
    public function getBargainAttr($bargainGoods)
    {
        $goodsAttr = GoodsAttr::findOne(['goods_id' => $bargainGoods->goods_id]);

        return $goodsAttr;
    }

    /**
     * @param $pagination
     * @param int $page
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * 后台获取砍价商品列表
     */
    public function getGoodsList(&$pagination, $page = 1, $limit = 20)
    {
        $goodsList = Goods::find()->alias('g')->with('goodsWarehouse')
            ->where(['g.is_delete' => 0, 'g.mall_id' => $this->mall->id, 'g.sign' => 'bargain'])
            ->page($pagination, $limit, $page)
            ->orderBy(['g.sort' => SORT_ASC, 'g.created_at' => SORT_DESC])->all();
        return $goodsList;
    }

    /**
     * @param $goodsId
     * @param $status
     * @return bool
     * @throws \Exception
     * 改变砍价商品活动状态
     */
    public function setSwitchStatus($goodsId, $status)
    {
        /* @var BargainGoods $bargainGoods */
        $bargainGoods = $this->getGoods($goodsId);
        if (!$bargainGoods) {
            throw new \Exception('商品不存在');
        }

        $bargainGoods->goods->status = $status;
        if (!$bargainGoods->goods->save()) {
            throw new \Exception($this->getErrorMsg($bargainGoods));
        }
        \Yii::$app->trigger(HandlerRegister::BARGAIN_TIMER, new BargainGoodsEvent([
            'bargainGoods' => $bargainGoods
        ]));
        return true;
    }

    /**
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|BargainGoods[]
     */
    public function getList($goodsIdList)
    {
        $list = BargainGoods::find()->with('goodsWarehouse')
            ->where(['goods_id' => $goodsIdList, 'mall_id' => $this->mall->id, 'is_delete' => 0])
            ->all();
        return $list;
    }

    /**
     * @param array $array
     * @return array
     * 获取diy商品列表信息
     */
    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword(isset($array['keyword']) && $array['keyword'], ['like', 'name', $array['keyword']])
            ->select('id');
        $goodsId = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        /* @var BargainGoods[] $bargainGoodsList */
        $bargainGoodsList = BargainGoods::find()->with(['goods.goodsWarehouse'])
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goodsId])
            ->andWhere(['>', 'end_time', mysql_timestamp()])->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($bargainGoodsList as $bargainGoods) {
            $newItem = $common->getDiyBack($bargainGoods->goods);
            $newItem = array_merge($newItem, [
                'begin_time' => $bargainGoods->begin_time,
                'end_time' => $bargainGoods->end_time,
                'min_price' => $bargainGoods->min_price,
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * @param $goodsId
     * @param $sort
     * @return bool
     * @throws \Exception
     * 改变商品排序
     */
    public function setSort($goodsId, $sort)
    {
        /* @var BargainGoods $bargainGoods */
        $bargainGoods = $this->getGoods($goodsId);
        if (!$bargainGoods) {
            throw new \Exception('商品不存在');
        }

        $bargainGoods->goods->sort = $sort;
        if (!$bargainGoods->goods->save()) {
            throw new \Exception($this->getErrorMsg($bargainGoods));
        }
        return true;
    }

    /**
     * @param Goods $goods
     * @param int $page
     * @return Goods[]
     */
    public function hasVideoGoodsList($goods, $page, $limit)
    {
        $nowDate = mysql_timestamp();
        $list = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr'])->where([
            'g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => $this->mall->id,
        ])->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->leftJoin(['bg' => BargainGoods::tableName()], 'bg.goods_id=g.id')
            ->andWhere(['>=', 'bg.end_time', $nowDate])
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($limit, $page)
            ->all();
        return $list;
    }
}
