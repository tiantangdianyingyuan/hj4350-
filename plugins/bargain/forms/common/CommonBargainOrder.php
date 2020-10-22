<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 11:21
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\common;


use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\BargainUserOrder;

/**
 * @property Mall $mall
 */
class CommonBargainOrder extends Model
{
    public $mall;

    /**
     * @param null $mall
     * @return CommonBargainOrder
     */
    public static function getCommonBargainOrder($mall = null)
    {
        $model = new CommonBargainOrder();
        $model->mall = $mall ? $mall : \Yii::$app->mall;
        return $model;
    }

    /**
     * @param $bargainGoodsId
     * @param $userId
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定用户指定砍价商品进行中的订单
     */
    public function getUserOrder($bargainGoodsId, $userId = null)
    {
        $bargainOrder = BargainOrder::find()->where(['mall_id' => $this->mall->id, 'status' => 0,
            'user_id' => $userId, 'is_delete' => 0, 'bargain_goods_id' => $bargainGoodsId])
            ->orderBy(['id' => SORT_DESC])->one();

        return $bargainOrder;
    }

    /**
     * @param $bargainOrderId
     * @return array|\yii\db\ActiveRecord|null|BargainOrder
     * 获取指定砍价订单的ID
     */
    public function getBargainOrder($bargainOrderId)
    {
        $bargainOrder = BargainOrder::find()->with(['bargainGoods', 'goods.goodsWarehouse', 'user', 'userOrderList'])
            ->where(['id' => $bargainOrderId, 'is_delete' => 0, 'mall_id' => $this->mall->id])->one();

        return $bargainOrder;
    }

    /**
     * @param $bargainGoodsId
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     * 获取指定用户指定砍价商品砍价成功的订单
     */
    public function getBargainOrderSuccess($bargainGoodsId, $userId)
    {
        $bargainOrderList = BargainOrder::find()->where(['mall_id' => $this->mall->id, 'status' => 1,
            'user_id' => $userId, 'is_delete' => 0, 'bargain_goods_id' => $bargainGoodsId])->all();

        return $bargainOrderList;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null
     * 获取砍价订单提交结果
     */
    public function getBargainOrderResult($token)
    {
        $bargainOrderResult = OrderSubmitResult::find()->where(['token' => $token])
            ->orderBy(['id' => SORT_DESC])->one();

        return $bargainOrderResult;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定token的订单
     */
    public function getTokenOrder($token)
    {
        $bargainOrder = BargainOrder::find()->where(['token' => $token])->one();

        return $bargainOrder;
    }

    /**
     * @param $userId
     * @param $bargainOrderId
     * @return array|\yii\db\ActiveRecord|null
     * 获取指定用户指定砍价订单的参与数据
     */
    public function getUserJoinOrder($userId, $bargainOrderId)
    {
        $userJoinOrder = BargainUserOrder::find()->where(['mall_id' => $this->mall->id, 'user_id' => $userId,
            'bargain_order_id' => $bargainOrderId, 'is_delete' => 0])->one();

        return $userJoinOrder;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null
     * 通过token获取用户参与砍价信息
     */
    public function getBargainUserOrderByToken($token)
    {
        $userJoinOrder = BargainUserOrder::find()->with(['user'])
            ->where(['mall_id' => $this->mall->id, 'token' => $token, 'is_delete' => 0])->one();

        return $userJoinOrder;
    }

    /**
     * @param $bargainOrderId
     * @param bool $isAll
     * @param int $page
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * 获取指定砍价订单的所有用户参与数据
     */
    public function getUserJoinOrderAll($bargainOrderId, $isAll = true, $page = 1, $limit = 3)
    {
        $query = BargainUserOrder::find()->with(['user'])
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'bargain_order_id' => $bargainOrderId]);
        if (!$isAll) {
            $query->apiPage($limit, $page);
        }
        $userJoinOrderAll = $query->all();

        return $userJoinOrderAll;
    }

    /**
     * @param $bargainOrderId
     * @param $userId
     * @param $price
     * @param $token
     * @return BargainUserOrder
     * @throws \Exception
     * 用户参与砍价
     */
    public function addBargainUserOrder($bargainOrderId, $userId, $price, $token)
    {
        $bargainUserOrder = new BargainUserOrder();
        $bargainUserOrder->mall_id = $this->mall->id;
        $bargainUserOrder->user_id = $userId;
        $bargainUserOrder->bargain_order_id = $bargainOrderId;
        $bargainUserOrder->price = $price;
        $bargainUserOrder->is_delete = 0;
        $bargainUserOrder->created_at = mysql_timestamp();
        $bargainUserOrder->token = $token;
        $res = $bargainUserOrder->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($bargainUserOrder));
        }
        return $bargainUserOrder;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null
     * 通过token获取队列结果
     */
    public function getBargainUserOrderResult($token)
    {
        $result = OrderSubmitResult::find()->where(['token' => $token])
            ->orderBy(['id' => SORT_DESC])->one();

        return $result;
    }

    /**
     * @param BargainOrder $bargainOrder
     * @param int $page
     * @param int $limit
     * @return array
     * 指定砍价订单的用户参与信息
     */
    public function getBargainInfo($bargainOrder, $page = 1, $limit = 3)
    {
        /* @var BargainUserOrder[] $userJoinOrderAll */
        $userJoinOrderAll = $this->getUserJoinOrderAll($bargainOrder->id, false, $page, $limit);

        $bargainPrice = BargainUserOrder::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'bargain_order_id' => $bargainOrder->id])
            ->sum('price');
        $bargainInfo = [
            'reset_time' => $bargainOrder->resetTime,
            'price' => floatval($bargainOrder->price),
            'min_price' => floatval($bargainOrder->min_price),
            'now_price' => max(price_format($bargainOrder->price - $bargainPrice), floatval($bargainOrder->min_price)),
            'list' => [],
            'bargain_price' => price_format($bargainPrice),
            'bargain_price_per' => 0,
            'finish_at' => $bargainOrder->finishAt,
            'bargain_order_id' => $bargainOrder->id
        ];

        foreach ($userJoinOrderAll as $item) {
            $bargainInfo['list'][] = [
                'nickname' => $item->user->nickname,
                'avatar' => $item->user->userInfo->avatar,
                'price' => price_format($item->price)
            ];
        }
        $resetPrice = $bargainOrder->price - $bargainOrder->min_price;
        if ($resetPrice > 0) {
            $bargainInfo['bargain_price_per'] = price_format(($bargainInfo['bargain_price'] / $resetPrice * 100));
        } else {
            $bargainInfo['bargain_price_per'] = 0;
        }
        return $bargainInfo;
    }

    /**
     * @param $userId
     * @param int $page
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * 获取指定用户所有砍价订单
     */
    public function getBargainOrderByUserId($userId, $page = 1, $limit = 10)
    {
        $bargainOrderList = BargainOrder::find()
            ->with(['userOrderList', 'goods', 'bargainGoods', 'bargainGoods.goodsAttr'])
            ->where(['mall_id' => $this->mall->id, 'user_id' => $userId, 'is_delete' => 0])
            ->apiPage($limit, $page)->orderBy(['status' => SORT_ASC])->all();

        return $bargainOrderList;
    }

    /**
     * @param BargainOrder $bargainOrder
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function changeStatus($bargainOrder, $status)
    {
        $bargainOrder->status = $status;
        if (!$bargainOrder->save()) {
            throw new \Exception($this->getErrorMsg($bargainOrder));
        }
        return true;
    }

    /**
     * @param $params
     * @return array
     * 获取所有砍价订单
     */
    public function getBargainOrderAll($params)
    {
        $page = '';
        $status = '';
        $limit = '';
        $date_start = '';
        $date_end = '';
        $time = false;
        $goodsId = null;
        if (isset($params['page'])) {
            $page = $params['page'];
        }
        if (isset($params['status'])) {
            $status = $params['status'];
        }
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }
        if (isset($params['date_start']) && isset($params['date_end'])) {
            if ($params['date_start'] && $params['date_end']) {
                $time = true;
            }
            $date_start = $params['date_start'];
            $date_end = $params['date_end'];
        }
        $bargainGoods = null;
        if (isset($params['id']) && $params['id']) {
            $bargainGoods = BargainGoods::find()->with('goods')->alias('bg')
                ->where(['bg.mall_id' => $this->mall->id, 'bg.is_delete' => 0, 'goods_id' => $params['id']])
                ->one();
            $goodsId = $bargainGoods->id;
        }

        $condition = [];
        if (isset($params['prop_value']) && $params['prop_value'] !== '' && $params['prop_value'] !== null) {
            switch ($params['prop']) {
                case 'nickname':
                    $userId = User::find()->where(['mall_id' => $this->mall->id])
                        ->andWhere(['like', 'nickname', $params['prop_value']])
                        ->select('id');
                    $condition = ['user_id' => $userId];
                    break;
                case 'user_id':
                    $condition = ['user_id' => $params['prop_value']];
                    break;
                case 'name':
                    $goodsWarehouseId = GoodsWarehouse::find()->alias('gw')
                        ->where(['gw.mall_id' => \Yii::$app->mall->id, 'gw.is_delete' => 0])
                        ->andWhere(['like', 'gw.name', $params['prop_value']])->select('gw.id');
                    $goodsId = BargainGoods::find()->alias('bg')
                        ->where(['bg.mall_id' => $this->mall->id, 'bg.is_delete' => 0])
                        ->leftJoin(['g' => Goods::tableName()], 'g.id = bg.goods_id')
                        ->andWhere(['g.goods_warehouse_id' => $goodsWarehouseId])->select(['bg.id']);
                    break;
                default:
            }
        }
        $pagination = null;

        $bargainOrderAll = BargainOrder::find()->with(['userOrderList', 'user', 'goods', 'userOrderList.user'])
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->keyword($goodsId, ['bargain_goods_id' => $goodsId])
            ->keyword(!empty($condition), $condition)
            ->keyword($status != -1, ['status' => $status])
            ->keyword($time, ['and', ['>=', 'created_at', $date_start], ['<', 'created_at', $date_end]])
            ->page($pagination, $limit, $page)
            ->orderBy(['created_at' => SORT_DESC])->all();

        return [
            'list' => $bargainOrderAll,
            'pagination' => $pagination,
            'bargainGoods' => $bargainGoods
        ];
    }
}
