<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\Mall;
use app\plugins\advance\events\GoodsEvent;
use app\plugins\advance\jobs\FavoriteAutoDelJob;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;
use app\plugins\advance\Plugin;

/**
 * @property Mall $mall;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $deposit;
    public $swell_deposit;
    public $open_date;
    public $pay_limit;
    public $ladder_rules;

    public $advanceGoods;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['deposit', 'swell_deposit', 'pay_limit'], 'number'],
            [['open_date', 'ladder_rules'], 'safe'],
            [['deposit', 'swell_deposit'], 'default', 'value' => 0],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'deposit' => '定金',
            'swell_deposit' => '定金膨胀金',
            'open_date' => '预售时间',
            'ladder_rules' => '阶梯规则'
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->attrValidator();
            $this->checkData();
            $this->setGoods();
            // 将所有商品规格先删除
            AdvanceGoodsAttr::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();
            $this->setGoodsService();
            $this->setListener();

            // 触发商品编辑事件
            \Yii::$app->trigger(AdvanceGoods::EVENT_EDIT, new GoodsEvent([
                'advanceGoods' => $this->advanceGoods,
            ]));

            //自动删除到期的商品收藏
            $class = new FavoriteAutoDelJob(['goods_id' => $this->goods->id]);
            $time = strtotime($this->open_date[1]) - time() > 0 ? strtotime($this->open_date[1]) - time() : 0;
            \Yii::$app->queue->delay($time)->push($class);

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
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    protected function checkData()
    {
        if ($this->use_attr == 1) {
            foreach ($this->attr as $item) {
                if (!isset($item['deposit']) || $item['deposit'] < 0) {
                    throw new \Exception('请填写规格定金');
                }

                if (!isset($item['swell_deposit']) || $item['swell_deposit'] < 0) {
                    throw new \Exception('请填写规格膨胀金');
                }

                if ($item['swell_deposit'] < $item['deposit']) {
                    throw new \Exception('膨胀金不能小于定金x1');
                }

                // 售价不能小于等于定金膨胀优惠金额
                if ((doubleval($item['swell_deposit'])) > doubleval($item['price'])) {
                    throw new \Exception('膨胀金不能大于商品售价');
                }

                // 没有会员价时、不需要验证会员价
                if (isset($item['member_price']) && $this->is_level_alone == 1) {
                    foreach ($item['member_price'] as $memberItem) {
                        if (doubleval($item['swell_deposit']) > (doubleval($memberItem))) {
                            throw new \Exception('膨胀金不能大于会员价x1');
                        }
                    }
                }
            }
        } else {
            if ($this->deposit < 0) {
                throw new \Exception('请填写定金');
            }

            if ($this->swell_deposit < 0) {
                throw new \Exception('请填写膨胀金');
            }

            if ($this->deposit > $this->swell_deposit) {
                throw new \Exception('膨胀金不能小于定金x2');
            }
            // 售价不能小于等于定金膨胀优惠金额
            if ((doubleval($this->swell_deposit)) > doubleval($this->price)) {
                throw new \Exception('膨胀金不能大于商品售价');
            }
            // 默认规格下会员价检查
            if ((int)$this->is_level === 1 && (int)$this->is_level_alone === 1) {
                foreach ($this->member_price as $key => $item) {
                    if (doubleval($this->swell_deposit) > doubleval($item)) {
                        throw new \Exception('膨胀金不能大于会员价x2');
                    }
                }
            }
        }

        if (!$this->open_date) {
            throw new \Exception('请填写预付时间');
        }

        if ($this->pay_limit <= 0 && $this->pay_limit != -1) {
            throw new \Exception('尾款支付时间必须大于0');
        }
    }

    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        $attr = AdvanceGoodsAttr::findOne([
            'goods_attr_id' => $goodsAttr->id,
            'is_delete' => 0,
        ]);
        if (!$attr) {
            $attr = new AdvanceGoodsAttr();
        }
        $deposit = $this->use_attr ? $newAttr['deposit'] : $this->deposit;
        $swell_deposit = $this->use_attr ? $newAttr['swell_deposit'] : $this->swell_deposit;
        $attr->goods_attr_id = $goodsAttr->id;
        $attr->goods_id = $goodsAttr->goods_id;
        $attr->deposit = $deposit;
        $attr->swell_deposit = $swell_deposit;
        $res = $attr->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($attr));
        }
    }

    public function setExtraGoods($goods)
    {
        $this->checkLadderRules();
        $advanceGoods = AdvanceGoods::findOne([
            'goods_id' => $goods->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$advanceGoods) {
            $advanceGoods = new AdvanceGoods();
            $advanceGoods->mall_id = \Yii::$app->mall->id;
            $advanceGoods->goods_id = $goods->id;
        }
        $advanceGoods->deposit = $this->deposit;
        $advanceGoods->swell_deposit = $this->swell_deposit;
        $advanceGoods->start_prepayment_at = $this->open_date[0];
        $advanceGoods->end_prepayment_at = $this->open_date[1];
        $advanceGoods->pay_limit = $this->pay_limit;
        $advanceGoods->ladder_rules = \Yii::$app->serializer->encode($this->ladder_rules);
        $this->advanceGoods = $advanceGoods;
        $res = $advanceGoods->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($advanceGoods));
        }
    }

    private function checkLadderRules()
    {
        foreach ($this->ladder_rules as $rule) {
            if ($rule['num'] <= 0) {
                throw new \Exception('件数必须大于0');
            }
            if (!($rule['discount'] >= 0.1 && $rule['discount'] <= 10)) {
                throw new \Exception('阶梯折扣率不合法，阶梯折扣率必须在0.1折~10折。');
            }
        }
    }
}
