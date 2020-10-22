<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 16:51
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Favorite;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\User;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class FavoriteListForm extends Model
{
    public $page;
    public $limit;
    public $status;
    public $cat_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'cat_id', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }

    /**
     * @return array
     * @deprecated
     */
    public function goods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = Favorite::find()
                ->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->select('goods_id');
            $list = Goods::find()->with('goodsWarehouse')
                ->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id])
                ->andWhere(['id' => $query])->apiPage($this->limit, $this->page)->all();
            $oldList = [];
            $newList = [];
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $goods = $apiGoods->getDetail();
                $goods['is_sales'] = 0;
                $newItem = [];
                $newItem['goods'] = $goods;

                $oldList[] = $newItem;
                $newList[] = $goods;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => $oldList,
                    'new_list' => $newList,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function newGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $favorite = $query = Favorite::find()
                ->select(['goods_id', 'mirror_price'])
                ->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->all();
            $favorite = array_column(ArrayHelper::toArray($favorite), null, 'goods_id');
            $model = Goods::find()
                ->alias('g')
                ->with(['attr'])
                ->rightJoin(['f' => Favorite::tableName()], 'g.id = f.goods_id')
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'g.goods_warehouse_id = gw.id')
                ->leftJoin(['gcr' => GoodsCatRelation::tableName()], 'gw.id = gcr.goods_warehouse_id')
                ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id = gc.id')
                ->where(['g.mall_id' => \Yii::$app->mall->id])
                ->andWhere([
                    'f.user_id' => \Yii::$app->user->id,
                    'f.is_delete' => 0,
                    'f.mall_id' => \Yii::$app->mall->id
                ])
                ->groupBy(['g.id'])
                ->apiPage($this->limit, $this->page);
            if ($this->cat_id) {
                $catSecond = GoodsCats::find()->select('id')->andWhere([
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id,
                    'status' => 1,
                    'parent_id' => $this->cat_id,
                ]);
                $model = $model->andWhere([
                    'or',
                    ['gc.id' => $this->cat_id],
                    ['in', 'gc.parent_id', $catSecond],
                    ['in', 'gc.id', $catSecond],
                ]);
            }
            if ($this->status) {
                switch ($this->status) {
                    case 1:
                        $model = $model->andWhere(['<', 'g.goods_stock', 50]);
                        break;
                    case 2:
                        $model = $model->andWhere(
                            [
                                'OR',
                                ['!=', 'g.is_delete', 0],
                                ['!=', 'g.status', 1]
                            ]
                        );
                        break;
                    case 3:
                        $model = $model->andWhere(['<', 'g.price', new Expression('f.mirror_price')]);
                        break;
                        break;
                    default:
                        break;
                }
            }
            $list = $model->all();
            $newList = [];
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 1;
                $goods = $apiGoods->getDetail();
                $newItem = $goods;
                $newItem['show'] = false;
                $newItem['touch'] = false;
                $newItem['status_type'] = 0;
                if ($favorite[$item->id]['mirror_price'] > $item->price) {
                    $newItem['status_type'] = 1;
                    $newItem['low_price'] = price_format(
                        $favorite[$item->id]['mirror_price'] - $item->price,
                        PRICE_FORMAT_FLOAT
                    );
                }
                if ($goods['is_negotiable'] == 1) {
                    $newItem['status_type'] = 0;
                }
                if ($item->goods_stock < 50) {
                    $newItem['status_type'] = 2;
                }
                if ($item->is_delete != 0 || $item->status != 1) {
                    $newItem['status_type'] = 3;
                }
                $newItem['status_type_text'] = $this->parseStatusType($newItem['status_type']);
                $newList[] = $newItem;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => $newList,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    private function parseStatusType($type)
    {
        switch ($type) {
            case 1:
                return '降价';
            case 2:
                return '库存紧张';
            case 3:
                return '失效';
            default:
                return '';
        }
    }

    public function cats()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = Favorite::find()
                ->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->select('goods_id');
            $list = Goods::find()->with([
                'goodsWarehouse.cats' => function ($query) {
                    $query->select(['id', 'name', 'parent_id']);
                },
                'goodsWarehouse.cats.parent.parent'
            ])
                ->where(['mall_id' => \Yii::$app->mall->id])
                ->andWhere(['id' => $query])->all();
            $newList[] = [
                'id' => 0,
                'name' => '全部类目'
            ];
            foreach ($list as $item) {
                foreach ($item->goodsWarehouse->cats as $cat) {
                    if (empty($cat)) {
                        continue;
                    }
                    if ($cat->parent_id == 0) {
                        $newCat = [
                            'id' => $cat->id,
                            'name' => $cat->name
                        ];
                        $newList[] = $newCat;
                    } elseif (!is_null($cat->parent) && $cat->parent->parent_id == 0) {
                        $newCat = [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name
                        ];
                        $newList[] = $newCat;
                    } elseif (!is_null($cat->parent->parent) && $cat->parent->parent->parent_id == 0) {
                        $newCat = [
                            'id' => $cat->parent->parent->id,
                            'name' => $cat->parent->parent->name
                        ];
                        $newList[] = $newCat;
                    }
                }
            }
            $newList = array_unique($newList, SORT_REGULAR);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'list' => array_merge($newList)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function topic()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mall = \Yii::$app->mall;
        /* @var User $user */
        $user = \Yii::$app->user->identity;
        $favoriteQuery = TopicFavorite::find()->where([
            'mall_id' => $mall->id,
            'user_id' => $user->id,
            'is_delete' => 0
        ])
            ->select('topic_id');
        $list = Topic::find()->where(['is_delete' => 0, 'mall_id' => $mall->id, 'id' => $favoriteQuery])
            ->apiPage($this->limit, $this->page)->asArray()
            ->orderBy(['sort' => SORT_ASC])->all();

        foreach ($list as &$item) {
            $readCount = intval($item['read_count'] + $item['virtual_read_count']);
            $item['read_count'] = $readCount < 10000 ? $readCount . '人浏览' : intval($readCount / 10000) . '万+人浏览';
            $goodsClass = 'class="goods-link"';
            $goodsCount = mb_substr_count($item['content'], $goodsClass);
            $item['goods_count'] = $goodsCount ? $goodsCount . '件宝贝' : 0;
            $item['pic_list'] = json_decode($item['pic_list'], true);
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
