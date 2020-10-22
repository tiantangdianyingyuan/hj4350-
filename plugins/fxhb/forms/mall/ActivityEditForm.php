<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\fxhb\forms\mall;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbActivityCatRelation;
use app\plugins\fxhb\models\FxhbActivityGoodsRelation;

/**
 * @property Mall $mall
 */
class ActivityEditForm extends Model
{
    public $mall;
    public $id;
    public $page;
    public $keyword;
    public $status;
    public $type;
    public $number;
    public $count_price;
    public $least_price;
    public $effective_time;
    public $open_effective_time;
    public $coupon_type;
    public $sponsor_num;
    public $help_num;
    public $sponsor_count;
    public $sponsor_count_type;
    public $start_end_time;
    public $remark;
    public $pic_url;
    public $share_title;
    public $share_pic_url;
    public $cat_id_list;
    public $goods_id_list;
    public $name;
    public $is_home_model;

    private $activity;

    public function rules()
    {
        return [
            [['status', 'type', 'number', 'effective_time', 'open_effective_time',
                'coupon_type', 'sponsor_num', 'help_num', 'sponsor_count',
                'sponsor_count_type', 'page', 'id'], 'integer'],
            [['number', 'effective_time', 'open_effective_time', 'coupon_type',
                'sponsor_count', 'remark', 'share_title', 'name', 'share_title'], 'required'],
            [['count_price', 'least_price', 'is_home_model'], 'number'],
            [['remark', 'keyword', 'share_title'], 'string'],
            [['remark', 'keyword', 'share_title'], 'trim'],
            [['pic_url', 'share_pic_url'], 'string', 'max' => 255],
            [['start_end_time', 'goods_id_list', 'cat_id_list'], 'safe'],
        ];
    }

    //GET
    public function edit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            while (FxhbActivity::checkLock()) {
                // 判断是否有活动正在编辑
                \Yii::error('有活动正在编辑中');
            }
            // 活动编辑时 用缓存锁住
            FxhbActivity::lock(true);
            if ($this->status == 1) {
                $check = FxhbActivity::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'status' => 1
                ])->keyword($this->id, ['!=', 'id', $this->id])->one();
                if ($check) {
                    throw new \Exception('已有一场活动在进行中,请设置活动状态为->关闭');
                }
            }
            $startTime = $this->start_end_time[0];
            $endTime = $this->start_end_time[1];
            if (strlen($this->start_end_time[0]) != 19) {
                $startTime = $startTime . ' 00:00:00';
                $endTime = $endTime . ' 23:59:59';
            }

            // TODO 时间判断有问题
            $check = FxhbActivity::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])
                ->andWhere([
                    'or',
                    ['between', 'start_time', $startTime, $endTime],
                    ['between', 'end_time', $startTime, $endTime],
                    ['and',
                        [
                            '<=', 'start_time', $startTime
                        ],
                        [
                            '>=', 'end_time', $endTime
                        ]
                    ]
                ])
                ->keyword($this->id, ['!=', 'id', $this->id])->one();
            if ($check) {
                throw new \Exception('该时间段已有活动,请修改活动时间日期');
            }


            if ($this->id) {
                $activity = FxhbActivity::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id,]);
                if (!$activity) {
                    throw new \Exception('活动不存在');
                }
            } else {
                $activity = new FxhbActivity();
                $activity->mall_id = \Yii::$app->mall->id;
                $activity->created_at = mysql_timestamp();
            }
            $this->activity = $activity;

            $activity->status = $this->status;
            $activity->type = $this->type;
            $activity->number = $this->number;
            $activity->count_price = $this->count_price;
            $activity->least_price = $this->least_price;
            $activity->effective_time = $this->effective_time;
            $activity->open_effective_time = $this->open_effective_time;
            $activity->coupon_type = $this->coupon_type;
            $activity->sponsor_num = $this->sponsor_num;
            $activity->help_num = $this->help_num;
            $activity->sponsor_count = $this->sponsor_count;
            $activity->sponsor_count_type = $this->sponsor_count_type;
            $activity->start_time = $startTime;
            $activity->end_time = $endTime;
            $activity->remark = $this->remark;
            $activity->pic_url = $this->pic_url;
            $activity->share_title = $this->share_title;
            $activity->share_pic_url = $this->share_pic_url;
            $activity->name = $this->name;
            $activity->is_home_model = $this->is_home_model;
            $activity->updated_at = date('Y-m-d H:i:s');
            $res = $activity->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($activity));
            }

            $this->setCats();
            $this->setGoods();

            // 解锁
            FxhbActivity::lock(false);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            // 解锁
            FxhbActivity::lock(false);
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
     * 设置代金券指定分类
     * @throws \Exception
     */
    private function setCats()
    {
        if ($this->cat_id_list && is_array($this->cat_id_list)) {
            if (!$this->activity->isNewRecord) {
                FxhbActivityCatRelation::updateAll(['is_delete' => 1,], [
                    'activity_id' => $this->activity->id,
                    'is_delete' => 0
                ]);
            }
            foreach ($this->cat_id_list as $k => $item) {
                $model = FxhbActivityCatRelation::findOne(['activity_id' => $this->activity->id, 'cat_id' => $item]);
                if ($model) {
                    $model->is_delete = 0;
                } else {
                    $model = new FxhbActivityCatRelation();
                    $model->activity_id = $this->activity->id;
                    $model->cat_id = $item;
                }
                $res = $model->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }
            }
        }
    }

    /**
     * 设置代金券指定商品
     * @throws \Exception
     */
    private function setGoods()
    {
        if ($this->goods_id_list && is_array($this->goods_id_list)) {
            if (!$this->activity->isNewRecord) {
                FxhbActivityGoodsRelation::updateAll(['is_delete' => 1,], [
                    'activity_id' => $this->activity->id,
                    'is_delete' => 0
                ]);
            }
            foreach ($this->goods_id_list as $k => $item) {
                $model = FxhbActivityGoodsRelation::findOne([
                    'activity_id' => $this->activity->id,
                    'goods_warehouse_id' => $item
                ]);
                if ($model) {
                    $model->is_delete = 0;
                } else {
                    $model = new FxhbActivityGoodsRelation();
                    $model->activity_id = $this->activity->id;
                    $model->goods_warehouse_id = $item;
                }
                $res = $model->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }
            }
        }
    }
}
