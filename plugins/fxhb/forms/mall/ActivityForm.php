<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\fxhb\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonCats;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbActivityCatRelation;
use app\plugins\fxhb\models\FxhbActivityGoodsRelation;
use app\plugins\mch\models\Goods;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class ActivityForm extends Model
{
    public $mall;
    public $page;
    public $keyword;
    public $id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['page', 'id'], 'integer'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = FxhbActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->with(['cats', 'goods'])
            ->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->orderBy('created_at DESC')
            ->page($pagination)->asArray()->all();


        foreach ($list as $key => $item) {
            $catIds = [];
            foreach ($item['cats'] as $cat) {
                $catIds[] = $cat['id'];
            }
            $list[$key]['cat_id_list'] = $catIds;

            $goodsIds = [];
            foreach ($item['goods'] as $good) {
                $goodsIds[] = $good['id'];
            }
            $list[$key]['goods_id_list'] = $goodsIds;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = FxhbActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ])->with(['cats', 'goods.goodsWarehouse'])->page($pagination)->asArray()->one();


        $catIds = [];
        foreach ($detail['cats'] as $cat) {
            $catIds[] = $cat['id'];
        }
        $detail['cat_id_list'] = $catIds;

        $goodsIds = [];
        foreach ($detail['goods'] as $index => $goods) {
            $goodsIds[] = (int)$goods['id'];
            $detail['goods'][$index]['name'] = $goods['goodsWarehouse']['name'];
            $detail['goods'][$index]['cover_pic'] = $goods['goodsWarehouse']['cover_pic'];
        }
        $detail['goods_id_list'] = $goodsIds;

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail,
            ]
        ];
    }

    public function getCats()
    {
        $list = CommonCats::getAllCats();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'cats' => $list
            ]
        ];
    }

    public function getGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->relations = ['goodsWarehouse'];
        $form->keyword = $this->keyword;
        $form->page= $this->page;
        $list = $form->search();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['name'] = $item->goodsWarehouse->name;
            $newItem['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function status()
    {
        try {
            while (FxhbActivity::checkLock()) {
                // 判断是否有活动正在编辑
                \Yii::error('有活动正在编辑中');
            }
            // 活动编辑时 用缓存锁住
            FxhbActivity::lock(true);
            $fxhbActivity = FxhbActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$fxhbActivity) {
                throw new \Exception('活动不存在');
            }

            if (!$fxhbActivity->status) {
                $check = FxhbActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1]);
                if ($check) {
                    throw new \Exception('已有一场活动在进行中,活动无法开启');
                }
            }

            $fxhbActivity->status = $fxhbActivity->status ? 0 : 1;
            $res = $fxhbActivity->save();

            if ($res) {
                // 解锁
                FxhbActivity::lock(false);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功',
                ];
            } else {
                throw new \Exception($this->getErrorMsg($fxhbActivity));
            }
        } catch (\Exception $e) {
            // 解锁
            FxhbActivity::lock(false);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function destroy()
    {
        try {
            $fxhbActivity = FxhbActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$fxhbActivity) {
                throw new \Exception('活动不存在');
            }

            $fxhbActivity->is_delete = 1;
            $fxhbActivity->deleted_at = date('Y-m-d H:i:s');
            $res = $fxhbActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($fxhbActivity));
            }

            // 删除商品关联
            FxhbActivityGoodsRelation::updateAll([
                'is_delete' => 1,
            ], [
                'activity_id' => $fxhbActivity->id,
                'is_delete' => 0,
            ]);

            // 删除分类关联
            FxhbActivityCatRelation::updateAll([
                'is_delete' => 1,
            ], [
                'activity_id' => $fxhbActivity->id,
                'is_delete' => 0,
            ]);

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功',
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
