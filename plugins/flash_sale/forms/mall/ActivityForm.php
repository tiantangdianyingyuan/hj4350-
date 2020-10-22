<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/6
 * Time: 17:21
 */

namespace app\plugins\flash_sale\forms\mall;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\helpers\PluginHelper;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Model;
use app\plugins\flash_sale\forms\common\CommonActivity;
use app\plugins\flash_sale\forms\common\CommonStatics;
use app\plugins\flash_sale\models\FlashSaleActivity;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\Goods;
use Exception;
use Yii;
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
            [['sort_type'], 'default', 'value' => 3],
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
        $query = FlashSaleActivity::find()->where(
            [
                'mall_id' => Yii::$app->mall->id,
                'is_delete' => 0
            ]
        )->with(
            [
                'flashSaleGoods' => function ($query) {
                    $query->alias('fg')->with('goods')
                        ->innerJoin(['g' => Goods::tableName()], 'g.id = fg.goods_id')
                        ->where(['g.is_delete' => 0]);
                }
            ]
        );

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
            $query->andWhere(
                [
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
                ]
            );
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
            $list[$key]['time_status'] = CommonActivity::timeSlot($item);
            $list[$key]['kind'] = count($item['flashSaleGoods']);
            $list[$key]['statics'] = CommonStatics::getStatics($item['id']);

            foreach ($item['flashSaleGoods'] as $k => $good) {
                $list[$key]['flashSaleGoods'][$k]['now_stock'] = $good['goods']['goods_stock'];
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
            $detail = FlashSaleActivity::find()->where(
                [
                    'mall_id' => Yii::$app->mall->id,
                    'id' => $this->id,
                    'is_delete' => 0
                ]
            )->with(['flashSaleGoods'])->page($pagination)->one();

            if (!$detail) {
                throw new Exception('活动不存在');
            }

            $newDetail = ArrayHelper::toArray($detail);
            $newDetail['time_status'] = CommonActivity::timeSlot($newDetail);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                ],
                'example_url' => PluginHelper::getPluginBaseAssetsUrl('flash_sale') . '/img/example.png'
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ],
                'example_url' => PluginHelper::getPluginBaseAssetsUrl('flash_sale') . '/img/example.png'
            ];
        }
    }

    public function getGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $ids = FlashSaleGoods::find()
            ->where(['mall_id' => Yii::$app->mall->id, 'is_delete' => 0, 'activity_id' => $this->id])
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
        $form->model = 'app\plugins\flash_sale\models\Goods';
        $form->relations = ['goodsWarehouse' => function ($query) {
            $query->select(['id', 'mall_id', 'name', 'original_price', 'cost_price', 'cover_pic', 'pic_url', 'video_url', 'unit', 'type', 'ecard_id']);
        }, 'attr.attr', 'flashSaleGoods'];
        $form->keyword = $this->keyword;
        $form->page = $this->page;
        $form->limit = $this->limit;
        $form->sign = 'flash_sale';
        $form->goods_id = $ids;
        if ($this->sort_type == 1) {
            //按照库存正序
            $form->sort = 6;
            $form->sort_type = 1;
            $form->sort_prop = 'goods_stock';
        } elseif ($this->sort_type == 2) {
            //按照库存倒序
            $form->sort = 6;
            $form->sort_type = 0;
            $form->sort_prop = 'goods_stock';
        } elseif ($this->sort_type == 3) {
            //按照创建时间正序
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
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray(
                $item->goodsWarehouse
            ) : [];
            $newItem['attr'] = $this->setAttr($item);
            $newItem['name'] = $item->goodsWarehouse->name;
            $newItem['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newItem['flashSaleGoods'] = $item->flashSaleGoods;
            $newItem['discount'] = 10;
            $newItem['cut'] = 0.00;
            $newList[] = $newItem;
        }

        $goods = \app\models\Goods::find()
            ->alias('g')
            ->where(
                [
                    'g.mall_id' => Yii::$app->mall->id,
                    'g.is_delete' => 0,
                    'g.id' => $ids
                ]
            )
            ->select(['g.goods_warehouse_id'])
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

    private function setAttr($goods)
    {
        $newAttr = [];
        $attrGroup = Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = $goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($goods->attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = isset($attrList[$item['sign_id']]) ? $attrList[$item['sign_id']] : [];
            $newItem['price_member'] = 0;
            $newItem['member_price_list'] = $item->memberPrice;
            $newItem['extra'] = $item->attr;
            $newItem['extra']['discount'] = price_format($item->attr->discount, 'string', 1);
            $newItem['extra']['cut'] = price_format($item->attr->cut, 'string', 2);
            $newAttr[] = $newItem;
        }
        return $newAttr;
    }

    public function status()
    {
        $t = Yii::$app->db->beginTransaction();
        try {
            switch ($this->type) {
                case 'down':
                    $num = FlashSaleActivity::updateAll(
                        ['status' => 0],
                        [
                            'id' => $this->ids,
                            'mall_id' => Yii::$app->mall->id,
                            'is_delete' => 0
                        ]
                    );
                    break;
                case 'up':
                    $num = 0;
                    if (count($this->ids) > 1) {
                        throw new Exception('无法进行批量操作');
                    }
                    $id = implode(',', $this->ids);
                    $activity = FlashSaleActivity::find()
                        ->where(
                            [
                                'id' => $id,
                                'mall_id' => Yii::$app->mall->id,
                                'is_delete' => 0
                            ]
                        )
                        ->one();

                    if (!$activity) {
                        throw new Exception('活动不存在');
                    }

                    $check = $this->check($activity->id, $activity->start_at, $activity->end_at);
                    if ($check['code'] == 1) {
                        throw new Exception($check['msg']);
                    }
                    $activity->status = 1;
                    $res = $activity->save();
                    if ($res) {
                        $num++;
                    }
                    break;
                case 'del':
                    $num = FlashSaleActivity::updateAll(
                        ['is_delete' => 1],
                        [
                            'id' => $this->ids,
                            'mall_id' => Yii::$app->mall->id,
                            'is_delete' => 0
                        ]
                    );

                    $ids = FlashSaleGoods::find()
                        ->alias('pg')
                        ->where(
                            [
                                'pg.mall_id' => Yii::$app->mall->id,
                                'pg.activity_id' => $this->ids,
                                'pg.is_delete' => 0,
                            ]
                        )
                        ->select(['goods_id'])
                        ->column();

                    Goods::updateAll(
                        [
                            'is_delete' => 1
                        ],
                        [
                            'mall_id' => Yii::$app->mall->id,
                            'id' => $ids,
                            'is_delete' => 0,
                        ]
                    );

                    // 删除商品关联
                    FlashSaleGoods::updateAll(
                        [
                            'is_delete' => 1,
                        ],
                        [
                            'mall_id' => Yii::$app->mall->id,
                            'activity_id' => $this->ids,
                            'is_delete' => 0,
                        ]
                    );

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
        } catch (Exception $e) {
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
            $activity = FlashSaleActivity::findOne(['mall_id' => Yii::$app->mall->id, 'id' => $this->id]);

            if (!$activity) {
                throw new Exception('活动不存在');
            }

            $activity->is_delete = 1;
            $activity->deleted_at = date('Y-m-d H:i:s');
            $res = $activity->save();
            if (!$res) {
                throw new Exception($this->getErrorMsg($activity));
            }

            // 删除商品关联
            FlashSaleGoods::updateAll(
                [
                    'is_delete' => 1,
                ],
                [
                    'activity_id' => $activity->id,
                    'is_delete' => 0,
                ]
            );

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功',
                ];
            }
        } catch (Exception $e) {
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
     * @param string $activity_id
     * @param string $start_at
     * @param string $end_at
     * @return array
     */
    public function check($activity_id = '', $start_at = '', $end_at = '')
    {
        if (empty($activity_id)) {
            $activity_id = $this->id;
        }
        if (empty($start_at) || empty($end_at)) {
            $start_at = $this->start_at;
            $end_at = $this->end_at;
        }
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $check = CommonActivity::check($activity_id, $start_at, $end_at);
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
        $list = $form->search();
        $newList = [];
        /**@var Goods $item * */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray(
                $item->goodsWarehouse
            ) : [];
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
