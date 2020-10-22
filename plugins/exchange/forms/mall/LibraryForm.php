<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\models\ExchangeLibrary;
use app\plugins\exchange\models\Goods;

class LibraryForm extends Model
{
    public $id;
    public $name;
    public $rewards_s;
    public $created_at;
    public $recycle_at;
    public $page;

    public $is_recycle = 0;

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['page', 'id', 'is_recycle'], 'integer'],
            [['rewards_s', 'created_at', 'recycle_at'], 'trim'],
        ];
    }

    //礼品卡使用
    public function getAll()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $where = [
            'AND',
            ['is_delete' => 0],
            ['mall_id' => \Yii::$app->mall->id],
            ['is_recycle' => 0],
        ];
        //过滤
        empty($this->name) || array_push($where, ['like', 'name', $this->name]);

        $list = ExchangeLibrary::find()->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $where = [
                'AND',
                ['is_delete' => 0],
                ['mall_id' => \Yii::$app->mall->id],
                ['is_recycle' => $this->is_recycle],
            ];
            empty($this->name) || array_push($where, ['like', 'name', $this->name]);
            empty($this->created_at) || array_push($where, ['>=', 'created_at', current($this->created_at)], ['<=', 'created_at', next($this->created_at)]);
            $query = ExchangeLibrary::find()->where($where);
            if (!empty($this->rewards_s) && is_array($this->rewards_s)) {
                $andWhere = ['OR'];
                for ($i = 0; $i < count($this->rewards_s); $i++) {
                    array_push($andWhere, ['like', 'rewards_s', $this->rewards_s[$i]]);
                }
                $query->andWhere($andWhere);
            }
            $list = $query
                ->orderBy(['id' => SORT_DESC])
                ->page($pagination)
                ->asArray()
                ->all();

            $codeModel = ExchangeCode::find()->where(['mall_id' => \Yii::$app->mall->id]);
            $list = array_map(function ($item) use ($codeModel) {
                $count = intval((clone $codeModel)->andWhere(['library_id' => $item['id']])->count());//总数

                //已兑换
                $item['record_num'] = intval(
                    (clone $codeModel)
                        ->andWhere([
                            'AND',
                            ['in', 'status', [2, 3]],
                            ['library_id' => $item['id']]
                        ])->count()
                );
                //可用
                $item['can_use_num'] = intval((clone $codeModel)
                    ->andWhere(['status' => 1, 'library_id' => $item['id']])
                    ->keyword($item['expire_type'] !== 'all', [
                        'AND',
                        //['<=', 'valid_start_time', date('Y-m-d H:i:s')],
                        ['>=', 'valid_end_time', date('Y-m-d H:i:s')]
                    ])
                    ->count());
                //不可用
                $item['not_can_use_num'] = $count - $item['record_num'] - $item['can_use_num'];

                $rewards_text_arr = array_map(function ($r) {
                    return ExchangeLibrary::defaultType()[$r]['name'] ?? $r;
                }, explode(',', $item['rewards_s']));

                $item['rewards_text'] = implode(',', $rewards_text_arr);
                return $item;
            }, $list);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'type' => array_values(ExchangeLibrary::defaultType()),
                    'pagination' => $pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $library = CommonModel::getLibrary($this->id);
            if (!$library) {
                throw new \Exception('数据不存在');
            }
            $library['rewards'] = CommonModel::getformatRewards($library['rewards']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => $library,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function recycle()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->is_recycle == 0) {
                $model = CommonModel::getLibrary($this->id, \Yii::$app->mall->id, ['is_recycle' => 1]);
            }
            if ($this->is_recycle == 1) {
                $model = CommonModel::getLibrary($this->id, \Yii::$app->mall->id, ['is_recycle' => 0]);

                $ids = ExchangeGoods::find()->select('goods_id')->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'library_id' => $model->id,
                ])->column();
                if (!empty($ids)) {
                    Goods::updateAll(['status' => 0], [
                        'AND',
                        ['mall_id' => \Yii::$app->mall->id],
                        ['is_delete' => 0],
                        ['sign' => 'exchange'],
                        ['status' => 1],
                        ['in', 'id', $ids],
                    ]);
                }
            }
            if (!$model) {
                throw new \Exception('数据不存在');
            }
            $model->is_recycle = $this->is_recycle;
            $model->recycle_at = date('Y-m-d H:i:s');
            $model->save();
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destory()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = CommonModel::getLibrary($this->id, \Yii::$app->mall->id, ['is_recycle' => 1]);
            if (!$model) {
                throw new \Exception('数据不存在');
            }

            $ids = ExchangeGoods::find()->select('goods_id')->where([
                'mall_id' => \Yii::$app->mall->id,
                'library_id' => $model->id,
            ])->column();
            if (!empty($ids)) {
                Goods::updateAll(['status' => 0], [
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['is_delete' => 0],
                    ['sign' => 'exchange'],
                    ['status' => 1],
                    ['in', 'id', $ids],
                ]);
            }

            $model->is_delete = 1;
            $model->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
