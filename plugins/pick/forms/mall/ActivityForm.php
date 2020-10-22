<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/12
 * Time: 15:57
 */

namespace app\plugins\pick\forms\mall;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\pick\forms\common\CommonForm;
use app\plugins\pick\models\Goods;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickGoods;
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
    public $start_at;
    public $end_at;
    public $status;
    public $keyword_label;
    public $type;
    public $ids;
    public $limit;
    public $sort_type;

    public function rules()
    {
        return [
            [['keyword', 'keyword_label'], 'trim'],
            [['page', 'id', 'status'], 'integer'],
            [['start_at', 'end_at', 'type'], 'string'],
            [['start_at', 'end_at',], 'required', 'on' => ['check']],
            [['ids'], 'safe'],
            [['limit'], 'default', 'value' => 10],
            [['sort_type'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = PickActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->with(['pickGoods.goods']);

        if (isset($this->keyword)) {
            switch ($this->keyword_label) {
                case 'title':
                    $query->andWhere(['like', 'title', $this->keyword]);
                    break;
                default:
                    break;
            }
        }

        if ($this->start_at && $this->end_at) {
            $query->andWhere([
                'or',
                ['between', 'start_at', $this->start_at, $this->end_at],
                ['between', 'end_at', $this->start_at, $this->end_at],
                [
                    'and',
                    [
                        '<=',
                        'start_at',
                        $this->start_at
                    ],
                    [
                        '>=',
                        'end_at',
                        $this->end_at
                    ]
                ]
            ]);
        }

        if (isset($this->status)) {
            switch ($this->status) {
                // 全部
                case -1:
                    break;
                // 未开始
                case 0:
                    $query->andWhere(['>', 'start_at', mysql_timestamp()]);
                    $query->andWhere(['status' => 1]);
                    break;
                // 进行中
                case 1:
                    $query->andWhere(['<=', 'start_at', mysql_timestamp()]);
                    $query->andWhere(['>=', 'end_at', mysql_timestamp()]);
                    $query->andWhere(['status' => 1]);
                    break;
                // 已结束
                case 2:
                    $query->andWhere(['<=', 'end_at', mysql_timestamp()]);
                    $query->andWhere(['status' => 1]);
                    break;
                // 下架中
                case 3:
                    $query->andWhere(['status' => 0]);
                    break;
                default:
                    break;
            }
        }

        $list = $query
            ->orderBy('start_at DESC')
            ->page($pagination, $this->limit)->asArray()->all();

        foreach ($list as $key => $item) {
            $list[$key]['time_status'] = CommonForm::timeSlot($item);
            $list[$key]['kind'] = count($item['pickGoods']);

            foreach ($item['pickGoods'] as $k => $good) {
                $list[$key]['pickGoods'][$k]['now_stock'] = $good['goods']['goods_stock'];
            }
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
        try {
            $detail = PickActivity::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ])->with(['pickGoods'])->page($pagination)->one();

            if (!$detail) {
                throw new \Exception('活动不存在');
            }

            $newDetail = ArrayHelper::toArray($detail);
            $newDetail['time_status'] = CommonForm::timeSlot($newDetail);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                ]
            ];
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

    public function getGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $ids = PickGoods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'pick_activity_id' => $this->id])
            ->select('goods_id')
            ->column();

        if (empty($ids)) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => [],
                    'goods' => [],
                    'pagination' => new Pagination()
                ]
            ];
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\pick\models\Goods';
        $form->relations = ['goodsWarehouse', 'attr', 'pickGoods'];
        $form->keyword = $this->keyword;
        $form->page = $this->page;
        $form->limit = $this->limit;
        $form->sign = 'pick';
        $form->goods_id = $ids;
        if ($this->sort_type == 1) {
            //按照库存正序
            $form->sort = 6;
            $form->sort_type = 1;
            $form->sort_prop = 'goods_stock';
        } elseif ($this->sort_type == 2) {
            //按照创建时间倒序
            $form->sort = 6;
            $form->sort_type = 0;
            $form->sort_prop = 'goods_stock';
        } elseif ($this->sort_type == 3) {
            //按照创建时间倒序
            $form->sort = 6;
            $form->sort_type = 0;
            $form->sort_prop = 'created_at';
        } elseif ($this->sort_type == 4) {
            //按照创建时间倒序
            $form->sort = 6;
            $form->sort_type = 1;
            $form->sort_prop = 'created_at';
        }
        $list = $form->search();
        $newList = [];
        /**@var Goods $item * */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['attr'] = $form->setAttr($item, false);
            $newItem['name'] = $item->goodsWarehouse->name;
            $newItem['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newItem['pickGoods'] = $item->pickGoods;
            $newList[] = $newItem;
        }

        $goods = \app\models\Goods::find()
            ->alias('g')
            ->where([
                'g.mall_id' => \Yii::$app->mall->id,
                'g.is_delete' => 0,
                'g.id' => $ids
            ])
            ->innerJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
            ->select(['gw.id'])
            ->column();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'goods' => $goods,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function status()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->type) {
                case 'down':
                    $num = PickActivity::updateAll(['status' => 0], [
                        'id' => $this->ids,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);
                    break;
                case 'up':
                    if (count($this->ids) > 1) {
                        throw new \Exception('无法进行批量操作');
                    }
                    $id = implode(',', $this->ids);
                    $activity = PickActivity::findOne([
                        'id' => $id,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);
                    if (!$activity) {
                        throw new \Exception('活动不存在');
                    }
                    if ($activity->status == PickActivity::ACTIVITY_UP) {
                        throw new \Exception('活动已经上架');
                    }
                    $this->start_at = $activity->start_at;
                    $this->end_at = $activity->end_at;
                    $check = $this->check();
                    if ($check['code'] == 1) {
                        throw new \Exception($check['msg']);
                    }
                    $num = PickActivity::updateAll(['status' => 1], [
                        'id' => $id,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);
                    break;
                case 'del':
                    $num = PickActivity::updateAll(['is_delete' => 1], [
                        'id' => $this->ids,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);

                    $ids = PickGoods::find()
                        ->alias('pg')
                        ->where([
                            'pg.mall_id' => \Yii::$app->mall->id,
                            'pg.pick_activity_id' => $this->ids,
                            'pg.is_delete' => 0,
                        ])
                        ->select(['goods_id'])
                        ->column();

                    Goods::updateAll([
                        'is_delete' => 1
                    ], [
                        'mall_id' => \Yii::$app->mall->id,
                        'id' => $ids,
                        'is_delete' => 0,
                    ]);

                    // 删除商品关联
                    PickGoods::updateAll([
                        'is_delete' => 1,
                    ], [
                        'mall_id' => \Yii::$app->mall->id,
                        'pick_activity_id' => $this->ids,
                        'is_delete' => 0,
                    ]);

                    break;
                default:
                    $num = 0;
                    break;
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
                'num' => $num
            ];
        } catch (\Exception $e) {
            $t->rollBack();
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
            $pickActivity = PickActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$pickActivity) {
                throw new \Exception('活动不存在');
            }

            $pickActivity->is_delete = 1;
            $pickActivity->deleted_at = date('Y-m-d H:i:s');
            $res = $pickActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($pickActivity));
            }

            // 删除商品关联
            PickGoods::updateAll([
                'is_delete' => 1,
            ], [
                'pick_activity_id' => $pickActivity->id,
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

    /**
     * 检测是活动时间是否冲突
     * @throws \Exception
     */
    public function check()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $check = PickActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => PickActivity::ACTIVITY_UP,
            'is_delete' => 0,
        ])
            ->andWhere([
                'or',
                ['between', 'start_at', $this->start_at, $this->end_at],
                ['between', 'end_at', $this->start_at, $this->end_at],
                [
                    'and',
                    [
                        '<=',
                        'start_at',
                        $this->start_at
                    ],
                    [
                        '>=',
                        'end_at',
                        $this->end_at
                    ]
                ]
            ])
            ->keyword($this->id, ['!=', 'id', $this->id])->one();
        if ($check) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该时间段已有活动,请修改活动时间日期',
                'data' => ''
            ];
        } else {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => ''
            ];
        }
    }

    public function getMallGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->keyword = $this->keyword;
        $form->page = $this->page;
        $form->is_del_ecard = true;
        $list = $form->search();
        $newList = [];
        /**@var Goods $item * */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['attr'] = $form->setAttr($item, false);
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
}
