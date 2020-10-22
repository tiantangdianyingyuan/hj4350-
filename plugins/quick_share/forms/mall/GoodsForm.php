<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\mall;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\quick_share\forms\common\CommonGoods;
use app\plugins\quick_share\models\QuickShareGoods;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $id;
    public $page;
    public $search;
    public $material_sort;

    public $is_top;
    public $sort;
    public $batch_ids;
    public $status;
    public $share_text;

    public function rules()
    {
        return [
            [['page', 'id', 'material_sort', 'status', 'sort', 'is_top'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['search', 'batch_ids'], 'trim'],
            [['share_text'], 'string'],
            [['share_text', 'is_top'], 'default', 'value' => 0],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $search = \Yii::$app->serializer->decode($this->search) ?? null;

        $data = (new CommonGoods)->getGoodsList([
            'keyword' => $search['keyword'],
            'time' => $search['time'],
            'type' => $search['type'],
            'sort' => $search['sort']
        ]);

        list($list, $pagination) = $data;

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = CommonGoods::getGoods($this->id);

            isset($form->share_pic) && $form->share_pic = \yii\helpers\BaseJson::decode($form->share_pic);
            $goods = $form->goods_id ? (\app\forms\common\goods\CommonGoods::getCommon())->getGoodsDetail($form->goods_id) : [];

            $detail = array_merge([
                'attr_groups' => [],
                'goods_warehouse' => (Object)[],
                'plugin' => [
                    'share_text' => $form->share_text,
                    'share_pic' => $form->share_pic,
                    'status' => $form->status,
                    'material_sort' => $form->material_sort,
                    'material_video_url' => $form->material_video_url,
                    'material_cover_url' => $form->material_cover_url,
                    'is_top' => $form->is_top,
                    'tabs' => $form->goods_id ? QuickShareGoods::GOODSTYPE : QuickShareGoods::DYNAMICTYPE,
                ]
            ], $goods);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getSearch()
    {
        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }

        $query = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->user->identity->mch_id > 0 ? 'mch' : '',
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);

        //select all
        $ids = QuickShareGoods::find()->select('goods_id')->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['is_delete' => 0],
            ['not', 'goods_id = 0']
        ])->column();
        $query->andWhere(['not in', 'id', $ids]);

        // 商品名称搜索
        if (isset($search['keyword']) && $search['keyword']) {
            $keyword = trim($search['keyword']);
            $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                ->keyword($keyword, ['LIKE', 'name', $keyword])->select('id');
            $query->andWhere([
                'or',
                ['like', 'id', $search['keyword']],
                ['goods_warehouse_id' => $goodsIds]
            ]);
        }

        $list = $query->with('attr', 'mallGoods')->page($pagination)->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['mallGoods'] = isset($item->mallGoods) ? ArrayHelper::toArray($item->mallGoods) : [];
            $newItem['name'] = isset($item->goodsWarehouse->name) ? $item->goodsWarehouse->name : '';
            $goodsStock = 0;
            foreach ($item->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            $newItem['goods_stock'] = $goodsStock;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function destroy()
    {
        try {
            $form = CommonGoods::getGoods($this->id);
            $form->is_delete = 1;
            $form->save();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            QuickShareGoods::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
                'is_delete' => 0,
                'id' => $this->batch_ids,
                'mall_id' => \Yii::$app->mall->id,
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function editAlone()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = CommonGoods::getGoods($this->id);
            $form->status = $this->status;
            $form->material_sort = $this->material_sort;
            $form->share_text = $this->share_text;
            if ($this->is_top) {
                QuickShareGoods::updateAll(['is_top' => 0, 'updated_at' => mysql_timestamp()], [
                    'AND',
                    ['is_delete' => 0],
                    ['is_top' => 1],
                    ['mall_id' => \Yii::$app->mall->id],
                    $form->goods_id ? ['<>', 'goods_id', 0] : ['goods_id' => 0]
                ]);
                $form->is_top = $this->is_top;
            } else {
                $form->is_top = 0;
            }
            $form->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
