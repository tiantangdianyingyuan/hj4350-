<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\lottery\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\lottery\forms\common\CommonEcard;
use app\plugins\lottery\jobs\LotteryJob;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\models\LotteryDefault;
use app\plugins\lottery\models\LotteryLog;

class LotteryForm extends Model
{
    public $attr_id;
    public $status;
    public $start_at;
    public $end_at;
    public $stock;
    public $sort;
    public $join_min_num;
    public $keyword;
    public $id;
    public $user_id;
    public $lottery_id;
    public $page;

    public function rules()
    {
        return [
            [['attr_id', 'status', 'stock', 'sort', 'join_min_num', 'id', 'user_id', 'lottery_id', 'page'], 'integer'],
            [['end_at', 'start_at'], 'safe'],
            [['keyword'], 'string'],
            [['keyword'], 'default', 'value' => ''],
            [['sort', 'join_min_num', 'page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'attr_id' => '规格',
            'status' => '0关闭 1开启',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'stock' => '库存',
            'join_min_num'=> '最小参与人数',
            'sort' => '排序',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\lottery\models\Goods';
        $form->is_array = 1;
        $form->page = $this->page;
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse', 'lotteryGoods', 'attr'];
        $form->keyword = $this->keyword;
        $list = $form->search();

        foreach($list as $k => $item) {
            $attr_groups = \Yii::$app->serializer->decode($item['attr_groups']);
            $attr_str = '';
            foreach($attr_groups as $item1) {
                $attr_str .= $item1['attr_group_name'] . ':' . $item1['attr_list'][0]['attr_name'] . ';';
            }
            $list[$k]['attr_str'] = $attr_str;
            $list[$k]['goods_name'] = $item['goodsWarehouse']['name'];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $attr = GoodsAttr::find()->where([
                    'id' => $this->attr_id,
                    'is_delete' => 0
                ])
                ->with('goods')
                ->one();

            if (!$attr->goods) {
                throw new \Exception('商品不存在');
            }

            $goodsAttr = new GoodsAttr();
            $goodsAttr->updateStock($this->stock, 'sub', $this->attr_id);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }

        $model = new Lottery();
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        $model->type = 0;
        $model->attr_groups = $attr->goods->attr_groups;
        if ($model->save()) {
            $diff = strtotime($model->end_at) - time();
            $time = $diff > 0 ? $diff : 0;
            $id = \Yii::$app->queue->delay($time)->push(new LotteryJob([
                'model' => $model,
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }


    //商品搜索
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->keyword = $this->keyword;
        $form->limit = 10;
        $form->is_array = true;
        $form->is_attr = true;
        $list = $form->search();

        array_walk($list, function (&$item) {
            foreach ($item['attr'] as $k => $v) {
                $attr_list = (new Goods())->signToAttr($v['sign_id'], $item['attr_groups']);
                $attr_str = '';
                foreach ($attr_list as $v2) {
                    $attr_str .= $v2['attr_group_name'] . ':' . $v2['attr_name'] . ';';
                }
                $item['attr'][$k]['attr_str'] = $attr_str;
            }
        });
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $list
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Lottery::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->status = $this->status;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }

    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Lottery::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->sort = $this->sort;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Lottery::find()->where([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ])->with('goods')->one();

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->goods->is_delete = 1;
        $model->goods->save();
        $model->is_delete = 1;
        $model->save();
        // 返还幸运抽占用的卡密数据
        CommonEcard::getCommon()->refundEcard([
            'type' => 'occupy',
            'sign' => 'lottery',
            'num' => $model->stock,
            'goods_id' => $model->goods_id,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function info()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = LotteryLog::find()->select(["l.*", "max(ls.status) lottery_status"])->alias('l')->where([
                'AND',
                ['l.mall_id' => \Yii::$app->mall->id],
                ['l.child_id' => 0],
                ['l.lottery_id' => $this->lottery_id],
            ])
            ->joinWith(['user' => function ($query) {
                $query->keyword($this->keyword, ['like', 'nickname', $this->keyword]);
            }])
            ->leftJoin([
                    'ls' => LotteryLog::find()
                          ->where([
                                'AND',
                                ['mall_id' => \Yii::$app->mall->id],
                                ['in', 'status', [2,3,4]],
                            ])
            ], 'ls.lottery_id = l.lottery_id and ls.user_id = l.user_id');
        $list = $query->groupBy("l.user_id")
                ->orderBy("lottery_status DESC, id DESC, l.id DESC")
                ->with('child.user')
                ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$v) {
            $lottery_num = LotteryLog::find()->select('id')
                ->where([
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['not', 'child_id = 0'],
                    ['not', 'status = 0'],
                    ['lottery_id' => $v['lottery_id']],
                    ['user_id' => $v['user_id']],
                ])->count();
            $v['lottery_num'] = $lottery_num + 1;

            $default = LotteryDefault::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'lottery_id' => $v['lottery_id'],
                'user_id' => $v['user_id'],
            ]);
            $v['lottery_default'] = $default ? '1' : '0';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getChild()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = LotteryLog::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['lottery_id' => $this->lottery_id],
                ['user_id' => $this->user_id],
                ['not', 'child_id = 0'],
            ['not', 'status = 0'],
            ]);

        $list = $query->page($pagination)
            ->orderBy('status desc,id desc')
            ->with('childUser')
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function default()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $lottery = Lottery::find()->where([
            'AND',
            ['is_delete' => 0],
            ['id' => $this->lottery_id],
            ['mall_id' => \Yii::$app->mall->id],
            ['>', 'end_at', date('Y-m-d H:i:s')]
        ]);
        if (!$lottery) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '抽奖商品数据异常',
            ];
        };

        $model = LotteryDefault::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'lottery_id' => $this->lottery_id,
            'user_id' => $this->user_id,
        ]);

        if ($this->status) {
            if (!$model) {
                $model  = new LotteryDefault();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = $this->user_id;
                $model->lottery_id = $this->lottery_id;
                $model->save();
            }
        } elseif ($model) {
            $model->is_delete = 1;
            $model->save();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }
}
