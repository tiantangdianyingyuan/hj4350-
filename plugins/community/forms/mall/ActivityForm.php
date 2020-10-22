<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\mall;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\DistrictArr;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\community\forms\common\CommonForm;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityActivityLocking;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use app\plugins\community\models\Goods;
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
    public $scene;

    public function rules()
    {
        return [
            [['keyword', 'keyword_label', 'scene'], 'trim'],
            [['page', 'id', 'status'], 'integer'],
            [['start_at', 'end_at', 'type', 'scene'], 'string'],
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
        $query = CommunityActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->with(['communityGoods.goods', 'communityOrder.order', 'communityOrder.detail']);

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
//                    $query->andWhere(['status' => 1]);
                    break;
                // 下架中
                case 3:
                    $query->andWhere(['>', 'end_at', mysql_timestamp()]);
                    $query->andWhere(['status' => 0]);
                    break;
                default:
                    break;
            }
        }

        if ($this->scene === 'import') {
            $id = CommunityGoods::find()->alias('cg')
                ->where(['cg.is_delete' => 0, 'cg.mall_id' => \Yii::$app->mall->id])
                ->leftJoin(['g' => Goods::tableName()], 'g.id=cg.goods_id')
                ->andWhere(['g.is_delete' => 0])
                ->select('cg.activity_id');
            $query->andWhere(['id' => $id]);
        }

        $list = $query
            ->orderBy('id DESC')
            ->page($pagination, $this->limit)->asArray()->all();

        foreach ($list as $key => $item) {
            $list[$key]['activity_status'] = CommonForm::timeSlot($item);
            $list[$key]['kind'] = count($item['communityGoods']);

            foreach ($item['communityGoods'] as $k => $good) {
                $list[$key]['communityGoods'][$k]['now_stock'] = $good['goods']['goods_stock'];
            }
            $list[$key]['goods_num'] = 0;
            $list[$key]['order_price'] = 0;
            $list[$key]['has_success'] = 0;
            $arr = [];
            $nums = [];

            $successId = CommunityActivityLocking::find()->where([
                'activity_id' => $item['id'], 'is_delete' => 0
            ])->select('middleman_id')->column();
            if (!empty($item['communityOrder'])) {
                foreach ($item['communityOrder'] as $order) {
                    $list[$key]['order_price'] = bcadd($list[$key]['order_price'], $order['order']['total_pay_price']);
                    foreach ($order['detail'] as $k => $detail) {
                        $list[$key]['goods_num'] += $detail['num'];
                    }
                    if (!in_array($order['user_id'], $arr)) {
                        $arr[] = $order['user_id'];
                    }
                    if ($list[$key]['activity_status'] == 2 && $list[$key]['has_success'] != 1) {
                        if (in_array($order['middleman_id'], $successId)) {
                            $list[$key]['has_success'] = 1;
                        } else {
                            switch ($item['condition']) {
                                case 0:
                                    $list[$key]['has_success'] = 1;
                                    break;
                                case 1:
                                    if (!isset($nums[$order['middleman_id']])) {
                                        $nums[$order['middleman_id']] = [];
                                    }
                                    if (!in_array($order['user_id'], $nums[$order['middleman_id']])) {
                                        $nums[$order['middleman_id']][] = $order['user_id'];
                                    }
                                    if (count($nums[$order['middleman_id']]) >= $item['num']) {
                                        $list[$key]['has_success'] = 1;
                                    }
                                    break;
                                case 2:
                                    if (!isset($nums[$order['middleman_id']])) {
                                        $nums[$order['middleman_id']] = 0;
                                    }
                                    foreach ($order['detail'] as $k => $detail) {
                                        $nums[$order['middleman_id']] += $detail['num'];
                                    }
                                    if ($nums[$order['middleman_id']] >= $item['num']) {
                                        $list[$key]['has_success'] = 1;
                                    }
                                    break;
                                default:
                            }
                        }
                    }
                }
            }
            $list[$key]['order_num'] = !empty($item['communityOrder']) ? count($item['communityOrder']) : 0;
            $list[$key]['user_num'] = count($arr);

            unset($list[$key]['communityOrder']);
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
            $detail = CommunityActivity::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ])->with(['communityGoods'])->page($pagination)->one();

            if (!$detail) {
                throw new \Exception('活动不存在');
            }

            $newDetail = ArrayHelper::toArray($detail);
            $newDetail['full_price'] = json_decode($newDetail['full_price'], true);
            $area_limit = explode(',', $newDetail['area_limit']);
            $newDetail['area_limit'] = [];
            foreach ($area_limit as $item) {
                if ($item == 0 || $item == null) {
                    continue;
                }
                $newDetail['area_limit'][] = DistrictArr::getDistrict($item);
            }
            $newDetail['activity_status'] = CommonForm::timeSlot($newDetail);

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

        $ids = CommunityGoods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'activity_id' => $this->id])
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
        $form->model = 'app\plugins\community\models\Goods';
        $form->relations = ['goodsWarehouse', 'attr', 'communityGoods'];
        $form->keyword = $this->keyword;
        $form->page = $this->page;
        $form->limit = $this->limit;
        $form->sign = 'community';
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
            //按照创建时间正序
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

            $newItem['supply_price'] = 0;
            switch ($item['use_attr']) {
                case 0:
                    $newItem['supply_price'] = CommunityGoodsAttr::findOne(['attr_id' => $item['attr'][0]['id'], 'is_delete' => 0])->supply_price;
                    break;
                case 1:
                    $attr_info = CommunityGoodsAttr::findAll(['goods_id' => $item['id'], 'is_delete' => 0]);
                    $supply_price_arr = [];
                    foreach ($attr_info as $attr) {
                        $supply_price_arr[] = $attr->supply_price;
                    }
                    if (empty($supply_price_arr)) {
                        $supply_price_arr[] = 0;
                    }
                    $newItem['supply_price_section'] = [
                        'min_price' => price_format(min($supply_price_arr)),
                        'max_price' => price_format(max($supply_price_arr)),
                    ];

                    $price_arr = [];
                    foreach ($item['attr'] as $attr_1) {
                        $price_arr[] = $attr_1['price'];
                    }
                    if (empty($price_arr)) {
                        $price_arr[] = 0;
                    }
                    $newItem['price_section'] = [
                        'min_price' => price_format(min($price_arr)),
                        'max_price' => price_format(max($price_arr)),
                    ];
                    break;
            }


            $newItem['attr'] = $form->setAttr($item, false);
            $newItem['name'] = $item->goodsWarehouse->name;
            $newItem['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newItem['communityGoods'] = $item->communityGoods;
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
                    $num = CommunityActivity::updateAll(['status' => 0], [
                        'id' => $this->ids,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);
                    break;
                case 'up':
                    $error_msg = [];
                    foreach ($this->ids as $id) {
                        $activity = CommunityActivity::findOne([
                            'id' => $id,
                            'mall_id' => \Yii::$app->mall->id,
                            'is_delete' => 0
                        ]);
                        if (!$activity) {
                            throw new \Exception('活动不存在');
                        }
//                        if ($activity->status == CommunityActivity::ACTIVITY_UP) {
//                            throw new \Exception('活动已经上架');
//                        }
                        if (CommunityGoods::find()->where(['activity_id' => $id, 'is_delete' => 0])->count() <= 0) {
                            $error_msg[] = $activity->title;
                        }
                    }
                    if (!empty($error_msg)) {
                        throw new \Exception('活动【' . implode('/', $error_msg) . '】等无商品，无法上架');
                    }

                    $num = CommunityActivity::updateAll(['status' => 1], [
                        'id' => $this->ids,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);
                    break;
                case 'del':
                    $num = CommunityActivity::updateAll(['is_delete' => 1], [
                        'id' => $this->ids,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0
                    ]);

                    $ids = CommunityGoods::find()
                        ->alias('pg')
                        ->where([
                            'pg.mall_id' => \Yii::$app->mall->id,
                            'pg.activity_id' => $this->ids,
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
                    CommunityGoods::updateAll([
                        'is_delete' => 1,
                    ], [
                        'mall_id' => \Yii::$app->mall->id,
                        'activity_id' => $this->ids,
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
            $communityActivity = CommunityActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$communityActivity) {
                throw new \Exception('活动不存在');
            }

            $communityActivity->is_delete = 1;
            $communityActivity->deleted_at = date('Y-m-d H:i:s');
            $res = $communityActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($communityActivity));
            }

            // 删除商品关联
            CommunityGoods::updateAll([
                'is_delete' => 1,
            ], [
                'activity_id' => $communityActivity->id,
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
