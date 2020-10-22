<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\forms\mall;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\shopping\models\ShoppingLikes;
use app\plugins\shopping\models\ShoppingLikeUsers;

class LikeGoodsForm extends Model
{
    public $id;
    public $keyword;

    public function rules()
    {
        return [
            [['is_open', 'id'], 'integer'],
            [['keyword'], 'string']
        ];
    }

    /**
     * 可加入好物圈的商品列表
     * @return array
     */
    public function getList()
    {
        $goodsIds = ShoppingLikes::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->select('goods_id');

        $query = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => ''
        ]);

        if ($this->keyword) {
            $goodsWareHouseIds = GoodsWarehouse::find()->where([
                'like', 'name', $this->keyword,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->select('id');
            $query->andWhere(['goods_warehouse_id' => $goodsWareHouseIds]);
        }

        $list = $query->andWhere(['not in', 'id', $goodsIds])
            ->with(['attr', 'goodsWarehouse.cats'])
            ->page($pagination)
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 已加入好物圈的商品列表
     * @return array
     */
    public function getAddList()
    {
        $query = ShoppingLikes::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $goodsWarehouseIds = GoodsWarehouse::find()->where(['like', 'name', $this->keyword])->select('id');
            $goodsIds = Goods::find()->where(['goods_warehouse_id' => $goodsWarehouseIds])->select('id');
            $query->andWhere(['goods_id' => $goodsIds]);
        }
        $list = $query->with(['goods.attr', 'goods.goodsWarehouse.cats'])
            ->page($pagination)
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $count = ShoppingLikeUsers::find()->where([
                'like_id' => $item['id'],
                'is_delete' => 0
            ])->count();
            $item['like_user_count'] = $count;
            //插件名称
            if ($item['goods']['sign'] == '' && $item['goods']['mch_id'] == 0) {
                $item['plugin_name'] = '商城';
            } elseif ($item['goods']['mch_id'] > 0) {
                $item['plugin_name'] = '多商户';
            } else {
                $item['plugin_name'] = \Yii::$app->plugin->getPlugin($item['goods']['sign'])->getDisplayName();
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function destroy()
    {
        try {
            $model = ShoppingLikes::findOne($this->id);
            $model->is_delete = 1;
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getUsers()
    {
        $query = User::find();

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'id', $this->keyword],
                ['like', 'nickname', $this->keyword],
            ]);
        }
        $userIds = UserIdentity::find()->where([
            'is_super_admin' => 0,
            'is_admin' => 0,
            'is_operator' => 0
        ])->select('user_id');

        $sUserIds = ShoppingLikeUsers::find()->where([
            'like_id' => $this->id,
        ])->select('user_id');

        $list = $query->andWhere([
            'id' => $userIds,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0
        ])
            ->andWhere(['not in', 'id', $sUserIds])
            ->with(['userInfo'])
            ->page($pagination, 10)
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
