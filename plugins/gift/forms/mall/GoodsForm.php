<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\gift\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use yii\db\Query;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $search;
    public $sort;
    public $batch_ids;
    public $status;
    public $page;

    public function rules()
    {
        return [
            [['id', 'sort', 'status', 'page'], 'integer'],
            [['search', 'batch_ids'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
            'id' => '商品ID'
        ];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);
        $form = new CommonGoodsList();
        $form->keyword = $search['keyword'];
        $form->model = 'app\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse.cats', 'attr'];
        $form->is_array = 1;

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $form->sort = 6;
            $form->sort_prop = $search['sort_prop'];
            $form->sort_type = $search['sort_type'];
        } else {
            $form->sort = 2;
        }
        $form->getQuery();
        if (isset($search['status']) && (int)$search['status'] !== -1) {
            if ((int)$search['status'] === 0 || (int)$search['status'] === 1) {
                // 上下架状态
                $form->query->andWhere(['status' => $search['status']]);
            } elseif ((int)$search['status'] === 2) {
                // 售罄
                $form->query->andWhere(['goods_stock' => 0]);
            }
        }
        // 商品名称搜索
        if (isset($search['keyword']) && $search['keyword']) {
            $keyword = trim($search['keyword']);
            $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                ->keyword($keyword, ['LIKE', 'name', $keyword])->select('id');
            $form->query->andWhere([
                'or',
                ['like', 'id', $search['keyword']],
                ['goods_warehouse_id' => $goodsIds]
            ]);
        }
        // 商品排序
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            $form->query->orderBy([$search['sort_prop'] => $sortType]);
        } else {
            $form->query->orderBy('created_at DESC');
        }
        if (isset($search['status']) && (int)$search['status'] !== -1) {
            if ((int)$search['status'] === 0 || (int)$search['status'] === 1) {
                // 上下架状态
                $form->query->andWhere(['status' => $search['status']]);
            } elseif ((int)$search['status'] === 2) {
                // 售罄
                $form->query->andWhere(['goods_stock' => 0]);
            }
        }

        // 分类搜索
        if (isset($search['cats']) && $search['cats']) {
            $form->query = $this->addCatWhere($search['cats'], $form->query);
        }
        if (\Yii::$app->user->identity->mch_id > 0) {
            $form->query->with('mchGoods');
        }

        // 日期搜索
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $form->query->andWhere(['>=', 'created_at', $search['date_start']]);
            $form->query->andWhere(['<=', 'created_at', $search['date_end']]);
        }
        $form->page = $this->page;

        $list = $form->query->page($form->pagination, $form->limit, $form->page)
            ->groupBy($form->group_by_name)
            ->asArray($form->is_array)
            ->all();

        foreach ($list as &$item) {
            $item['status'] = (int)$item['status'];
            $item['cats'] = $item['goodsWarehouse']['cats'];
            $goodsStock = 0;
            foreach ($item['attr'] as $aItem) {
                $goodsStock += $aItem['stock'];
            }
            $item['goods_stock'] = $goodsStock;
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
                'hide_function' => []
            ]
        ];
    }

    public function getDetail()
    {
        $form = new CommonGoods();
        $res = $form->getGoodsDetail($this->id);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $res,
            ]
        ];
    }


    public function editSort()
    {
        /** @var Goods $goods */
        $goods = Goods::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        $goods->sort = $this->sort;
        $res = $goods->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($goods)
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }

    public function switchStatus()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;


            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * @param $catIds
     * @param Query $query
     * @return mixed
     */
    private function addCatWhere($catIds, $query)
    {
        if (!$catIds) {
            return $query;
        }
        $cat = GoodsCats::find()->select('id')
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'status' => 1
            ])
            ->andWhere([
                'OR',
                ['parent_id' => GoodsCats::find()->where([
                    'parent_id' => $catIds
                ])->select('id')],
                ['parent_id' => $catIds],
                ['id' => $catIds],
            ]);
        $goodsCatRelation = GoodsCatRelation::find()->select('goods_warehouse_id')
            ->where(['is_delete' => 0])->andWhere(['in', 'cat_id', $cat]);
        $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere(['id' => $goodsCatRelation])->select('id');
        $query->andWhere(['goods_warehouse_id' => $goodsWarehouseId]);

        return $query;
    }
}
