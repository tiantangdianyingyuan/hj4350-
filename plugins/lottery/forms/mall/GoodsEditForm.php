<?php

namespace app\plugins\lottery\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\lottery\forms\common\CommonEcard;
use app\plugins\lottery\forms\common\CommonLotteryGoods;
use app\plugins\lottery\jobs\LotteryJob;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\Plugin;

/**
 * @property
 * @property
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $time;
    public $stock;
    public $status;
    public $join_min_num;
    public $sort;

    public $tmp_status;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['time'], 'trim'],
            [['stock', 'status', 'sort', 'join_min_num'], 'default', 'value' => 0],
            [['stock', 'status', 'sort', 'join_min_num'], 'integer'],
            [['stock'], 'integer', 'min' => 1, 'max' => 999999999]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributes(), [
            'stock' => '奖品数量',
            'price' => '售价',
            'time' => '抽奖时间',
            'join_min_num' => '开奖最低限制',
            'status' => '状态',
            'sort' => '排序',
        ]);
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->tmp_status = $this->status;
            $this->status = 1;
            $this->setGoods();
            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->setStep();
            $this->setCoupon();
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        };
    }


    private function setStep()
    {
        $model = CommonLotteryGoods::getGoods($this->goods->id);
        $this->status = $this->tmp_status;
        if (!$model) {
            $model = new Lottery();
            $model->goods_id = $this->goods->id;
            $model->buy_goods_id = $this->attr[0]['goods_id'];
            $model->mall_id = \Yii::$app->mall->id;
            $model->status = $this->status;
            $model->type = 0;
            $model->stock = $this->stock;
            $model->start_at = $this->time[0];
            $model->end_at = $this->time[1];
            $model->join_min_num = $this->join_min_num;
            $model->is_delete = 0;

            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $diff = strtotime($model->end_at) - time();
            $time = $diff > 0 ? $diff : 0;
            $id = \Yii::$app->queue->delay($time + 15)->push(new LotteryJob([
                'model' => $model,
            ]));
            CommonEcard::getCommon()->occupy($this->goods, $this->stock);
        }

        return $model;
    }
}
