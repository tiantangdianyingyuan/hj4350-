<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/8
 * Time: 11:08
 */

namespace app\forms\mall\full_reduce;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\forms\common\activity\Activity;
use app\forms\common\goods\CommonGoodsList;
use app\helpers\PluginHelper;
use app\models\FullReduceActivity;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Store;
use app\plugins\mch\models\Mch;
use yii\helpers\ArrayHelper;

class ActivityForm extends Model
{
    use Activity;

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
        $query = FullReduceActivity::find()->where(
            [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]
        )->select(['id', 'name', 'start_at', 'end_at', 'status']);

        if (isset($this->keyword)) {
            switch ($this->keyword_label) {
                case 'name':
                    $query->andWhere(['like', 'name', $this->keyword]);
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
            $list[$key]['time_status'] = self::timeSlot($item);
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
            /**@var FullReduceActivity $detail**/
            $detail = FullReduceActivity::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->id,
                    'is_delete' => 0
                ]
            )->one();

            if (empty($detail)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '活动不存在',
                ];
            }

            if (!$detail) {
                throw new \Exception('活动不存在');
            }

            $newDetail = ArrayHelper::toArray($detail);
            unset($newDetail['appoint_goods']);
            unset($newDetail['noappoint_goods']);
            $newDetail['time_status'] = self::timeSlot($newDetail);
            $newDetail['discount_rule'] = (object)\Yii::$app->serializer->decode($detail->discount_rule);
            $newDetail['loop_discount_rule'] = (object)\Yii::$app->serializer->decode($detail->loop_discount_rule);
            if ($detail->appoint_type == 3) {
                $newDetail['appoint_goods'] =
                    $this->getAppointGoodsList(\Yii::$app->serializer->decode($detail->appoint_goods));
            } elseif ($detail->appoint_type == 4) {
                $newDetail['noappoint_goods'] =
                    $this->getAppointGoodsList(\Yii::$app->serializer->decode($detail->noappoint_goods));
            }
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
    }

    public function status()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->type) {
                case 'down':
                    $num = FullReduceActivity::updateAll(
                        ['status' => 0],
                        [
                            'id' => $this->ids,
                            'mall_id' => \Yii::$app->mall->id,
                            'is_delete' => 0
                        ]
                    );
                    break;
                case 'up':
                    $num = 0;
                    if (count($this->ids) > 1) {
                        throw new \Exception('无法进行批量操作');
                    }
                    $id = implode(',', $this->ids);
                    $activity = FullReduceActivity::find()
                        ->where(
                            [
                                'id' => $id,
                                'mall_id' => \Yii::$app->mall->id,
                                'is_delete' => 0
                            ]
                        )
                        ->one();

                    if (!$activity) {
                        throw new \Exception('活动不存在');
                    }

                    $check = $this->checkTime($activity, $activity->id, $activity->start_at, $activity->end_at);
                    if ($check['code'] == 1) {
                        throw new \Exception($check['msg']);
                    }
                    $activity->status = 1;
                    $res = $activity->save();
                    if ($res) {
                        $num++;
                    }
                    break;
                case 'del':
                    $num = FullReduceActivity::updateAll(
                        ['is_delete' => 1],
                        [
                            'id' => $this->ids,
                            'mall_id' => \Yii::$app->mall->id,
                            'is_delete' => 0
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
            $activity = FullReduceActivity::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$activity) {
                throw new \Exception('活动不存在');
            }

            $activity->is_delete = 1;
            $activity->deleted_at = date('Y-m-d H:i:s');
            $res = $activity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($activity));
            }

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
     * @param $model
     * @param string $activity_id
     * @param string $start_at
     * @param string $end_at
     * @return array
     */
    public function checkTime($model, $activity_id = '', $start_at = '', $end_at = '')
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

        $check = self::check($model, $activity_id, $start_at, $end_at);
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
        $form->sign = ['', 'mch', 'exchange'];
        $list = $form->search();
        $mchIds = [];
        foreach ($list as $item) {
            if ($item['mch_id'] != 0) {
                $mchIds[] = $item['mch_id'];
            }
        }
        $mchIds = array_unique($mchIds);
        $mchList = Store::findAll(['mch_id' => $mchIds]);
        $mchList = array_column($mchList, 'name', 'mch_id');
        $newList = [];
        /**@var Goods $item **/
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray(
                $item->goodsWarehouse
            ) : [];
            $newItem['attr'] = $form->setAttr($item, false);
            if ($item['mch_id'] == 0) {
                $newItem['shop_name'] = '自营';
            } else {
                $newItem['shop_name'] = $mchList[$item['mch_id']];
            }
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

    private function getAppointGoodsList($goods)
    {
        $list = Goods::find()
            ->select(['g.id', 'g.goods_warehouse_id', 'g.goods_stock', 'g.mch_id', 'g.price'])
            ->alias('g')
            ->where([
                'g.mall_id' => \Yii::$app->mall->id,
                'g.is_delete' => 0,
                'g.sign' => ['', 'mch']
            ])->joinWith(['goodsWarehouse gw' => function ($query) use ($goods) {
                $query->andWhere(['gw.id' => $goods]);
            }])
            ->all();

        $mchIds = [];
        $mchList = [];
        foreach ($list as $item) {
            if ($item['mch_id'] != 0) {
                $mchIds[] = $item['mch_id'];
            }
        }
        if (!empty($mchIds)) {
            $mchList = Store::findAll(['mch_id' => array_unique($mchIds)]);
            $mchList = array_column($mchList, 'name', 'mch_id');
        }

        $newList = [];
        foreach ($list as $item) {
            /**@var Goods $item**/
            $newItem = ArrayHelper::toArray($item);
            $newItem['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newItem['name'] = $item->goodsWarehouse->name;
            if ($item['mch_id'] != 0) {
                $newItem['shop_name'] = $mchList[$item['mch_id']];
            } else {
                $newItem['shop_name'] = '自营';
            }
            $newList[] = $newItem;
        }
        return $newList;
    }
}
