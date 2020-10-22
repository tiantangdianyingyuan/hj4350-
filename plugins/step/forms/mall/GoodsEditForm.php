<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 14:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\step\forms\common\CommonStepGoods;
use app\plugins\step\models\StepGoods;
use app\plugins\step\models\StepGoodsAttr;

class GoodsEditForm extends BaseGoodsEdit
{
    public $step_currency;
    public $id;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['step_currency'], 'required'],
            [['step_currency'], 'number'],
            [['id'], 'integer'],
            [['step_currency'], 'number', 'min' => 0, 'max' => 999999999],
            [['step_currency'], 'default', 'value' => 0],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributes(), [
            'step_currency' => '活力币'
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setGoods();
            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->setCoupon();
            $this->setStep();
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

    protected function setGoodsSign()
    {
        return \Yii::$app->plugin->getCurrentPlugin()->getName();
    }

    private function setStep()
    {
        $model = CommonStepGoods::getGoods($this->id);

        if (!$model) {
            $model = new StepGoods();
            $model->mall_id = \Yii::$app->mall->id;
            $model->goods_id = $this->goods->id;
            $model->is_delete = 0;
        };
        $model->currency = $this->step_currency;

        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        return $model;
    }

    public function setExtraAttr($goodsAttr, $newAttr){
        $model = CommonStepGoods::getAttr($goodsAttr->goods_id, $goodsAttr->id);
        if(!$model) {
            $model = new StepGoodsAttr();
            $model->mall_id = \Yii::$app->mall->id;
            $model->goods_id = $goodsAttr->goods_id;
            $model->attr_id = $goodsAttr->id;
        }
        $model->currency = $this->use_attr ? $newAttr['step_currency'] : $this->step_currency;
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }

    }

    protected function checkData()
    {
        if ($this->use_attr == 1) {
            foreach ($this->attr as $item) {
                if (!isset($item['step_currency']) || $item['step_currency'] <= 0) {
                    throw new \Exception('请填写规格活力币');
                }
            }
        } else {
            if ($this->step_currency <= 0) {
                throw new \Exception('请填写活力币');
            }
        }
    }


}
