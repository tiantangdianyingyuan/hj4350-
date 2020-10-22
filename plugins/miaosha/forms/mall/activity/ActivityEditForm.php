<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall\activity;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\GoodsAttr;
use app\plugins\miaosha\job\MiaoshaActivityJob;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;

class ActivityEditForm extends BaseGoodsEdit
{
    public $open_date;
    public $open_time;
    public $virtual_miaosha_num;
    public $activity_status;

    public $activity_date = [];
    public $activity_id = null;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['open_date', 'open_time'], 'safe'],
            [['virtual_miaosha_num', 'activity_status'], 'integer'],
            [['activity_status'], 'required'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'open_date' => '活动开始日期',
            'open_time' => '活动开始时间',
            'activity_status' => '活动状态',
            'virtual_miaosha_num' => '已秒杀数',
            'price' => '秒杀价'
        ]);
    }

    public function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $activity = new MiaoshaActivitys();
            $activity->mall_id = \Yii::$app->mall->id;
            $activity->open_date = $this->open_date[0];
            $activity->end_date = $this->open_date[1];
            $activity->status = $this->activity_status;
            $res = $activity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($activity));
            }
            $this->activity_id = $activity->id;

            $dateArr = $this->diffTime($this->open_date[0], $this->open_date[1]);
            foreach ($dateArr as $item) {
                foreach ($this->open_time as $tItem) {
                    $date = $item . ' ' . $tItem . ':59:59';

                    if (strtotime($date) >= time()) {
                        $this->activity_date['open_time'] = $tItem;
                        $this->activity_date['open_date'] = $item;
                        $queueId = \Yii::$app->queue3->delay(0)->push(new MiaoshaActivityJob([
                            'open_date' => $item,
                            'open_time' => $tItem,
                            'mall' => \Yii::$app->mall,
                            'miaoshaGoods' => $this,
                            'user' => \Yii::$app->user->identity
                        ]));
//                        $this->executeSave();
                    }
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'list' => $e->getLine()
                ]
            ];
        }
    }

    private function checkData()
    {
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先拉取商城商品');
        }
        if (!is_array($this->open_date)) {
            throw new \Exception('开放日期需为数组：[2019-11-30,2019-11-30]');
        }
        if (count($this->open_date) != 2) {
            throw new \Exception('请选择秒杀开放日期');
        }

        if (!is_array($this->open_time)) {
            throw new \Exception('秒杀开放时间段需为数组：[1,2,3,4]');
        }

        if (!count($this->open_time)) {
            throw new \Exception('请选择秒杀开放时间段');
        }

        if ($this->virtual_miaosha_num < 0) {
            throw new \Exception('已秒杀数不能小于0');
        }

        if ($this->use_attr == 1) {
            $goodsStock = 0;
            foreach ($this->attr as $item) {
                if (!isset($item['price']) || $item['price'] < 0) {
                    throw new \Exception('请填写规格价格');
                }
                $goodsStock += $item['stock'];
            }
            if ($goodsStock <= 0) {
                throw new \Exception('请填写规格库存');
            }
        } else {
            if ($this->price < 0) {
                throw new \Exception('秒杀价不能小于0');
            }
        }

        $maxTime = max($this->open_time);
        if (isset($this->open_date[1]) && $this->open_date[1] <= date('Y-m-d', time()) && $maxTime < date('H', time())) {
            throw new \Exception('请添加有效的秒杀活动日期时间,必须为 ' . date('Y-m-d H', time()) . ':00 点之后');
        }
    }

    private function diffTime($date1, $date2)
    {
        $time1 = strtotime($date1);
        $time2 = strtotime($date2);

        $diff = intval(($time2 - $time1) / 86400);

        $arr = [$date1];
        for ($i = 1; $i <= $diff; $i++) {
            $arr[] = date('Y-m-d', strtotime($date1) + 86400 * $i);
        }

        return $arr;
    }

    /**
     * @param GoodsAttr $goodsAttr
     * @param $newAttr
     * @throws \Exception
     */
    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        if (!$this->use_attr) {
            $goodsAttr->price = $this->price;
            $res = $goodsAttr->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goodsAttr));
            }
        }
    }

    public function executeSave()
    {
        \Yii::warning('开始创建秒杀商品');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->setGoods();
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();
            $this->setGoodsService();
            $this->setListener();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
        }
    }

    /**
     * @param Goods $goods
     * @throws \Exception
     * @return null;
     */
    public function setExtraGoods($goods)
    {
        $miaoshaGoods = MiaoshaGoods::findOne([
            'goods_id' => $goods->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$miaoshaGoods) {
            $miaoshaGoods = new MiaoshaGoods();
            $miaoshaGoods->mall_id = \Yii::$app->mall->id;
            $miaoshaGoods->goods_id = $goods->id;
            $miaoshaGoods->open_time = $this->activity_date['open_time'];
            $miaoshaGoods->open_date = $this->activity_date['open_date'];
        }
        $miaoshaGoods->virtual_miaosha_num = $this->virtual_miaosha_num;
        $miaoshaGoods->goods_warehouse_id = $goods->goods_warehouse_id;
        $miaoshaGoods->activity_id = $this->activity_id;
        $res = $miaoshaGoods->save();

        if (!$res) {
            \Yii::error($this->getErrorMsg($miaoshaGoods));
        }
    }
}
