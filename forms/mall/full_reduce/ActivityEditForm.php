<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/8
 * Time: 10:49
 */

namespace app\forms\mall\full_reduce;

use app\core\response\ApiCode;
use app\forms\common\activity\Activity;
use app\models\FullReduceActivity;
use app\models\Model;

class ActivityEditForm extends Model
{
    use Activity;

    public $id;
    public $status;
    public $name;
    public $content;
    public $start_at;
    public $end_at;
    public $appoint_type;
    public $rule_type;
    public $discount_rule;
    public $loop_discount_rule;
    public $appoint_goods;
    public $noappoint_goods;

    private $model;

    public function rules()
    {
        return [
            [['name', 'start_at', 'end_at', 'appoint_type', 'rule_type'], 'required'],
            [['id', 'status', 'appoint_type', 'rule_type'], 'integer', 'max' => 99999999],
            [['status'], 'default', 'value' => 1],
            [['start_at', 'end_at'], 'string'],
            [['discount_rule', 'loop_discount_rule'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['content'], 'string', 'max' => 8192],
            ['start_at', 'compare', 'compareAttribute' => 'end_at', 'operator' => '<', 'message' => '起始时间必须小于结束时间'],
            [['appoint_goods'], function ($attr, $params) {
                if ($this->appoint_type == 3 && empty($this->appoint_goods)) {
                    $this->addError($attr, $this->getAttributeLabel($attr) . '不能为空');
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
            [['noappoint_goods'], function ($attr, $params) {
                if ($this->appoint_type == 4 && empty($this->noappoint_goods)) {
                    $this->addError($attr, $this->getAttributeLabel($attr) . '不能为空');
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
            [['appoint_goods', 'noappoint_goods', 'discount_rule', 'loop_discount_rule'], 'default', 'value' => []]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '活动名称',
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
            'appoint_type' => '指定满减商品类型',
            'rule_type' => '满减类型',
            'discount_rule' => '满减规则',
            'loop_discount_rule' => '满减规则',
            'appoint_goods' => '指定参加商品',
            'noappoint_goods' => '指定不参加商品',
            'content' => '活动规则'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $fullReduceActivity = FullReduceActivity::find()->where(
                    [
                        'id' => $this->id,
                        'is_delete' => 0,
                        'mall_id' => \Yii::$app->mall->id
                    ]
                )->one();

                if (empty($fullReduceActivity)) {
                    throw new \Exception('活动不存在');
                }
            } else {
                $fullReduceActivity = new FullReduceActivity();
                $fullReduceActivity->mall_id = \Yii::$app->mall->id;
            }

            if ($this->end_at > '2038-01-01 00:00:00' || $this->start_at > '2038-01-01 00:00:00') {
                throw new \Exception('活动时间不能大于2038-01-01 00:00:00');
            }
            $this->checkDiscountRules($this->rule_type);
            $fullReduceActivity->status = $this->status;
            $fullReduceActivity->name = $this->name;
            $fullReduceActivity->start_at = $this->start_at;
            $fullReduceActivity->end_at = $this->end_at;
            $fullReduceActivity->appoint_type = $this->appoint_type;
            $fullReduceActivity->rule_type = $this->rule_type;
            $fullReduceActivity->discount_rule = \Yii::$app->serializer->encode($this->discount_rule);
            $fullReduceActivity->loop_discount_rule = \Yii::$app->serializer->encode($this->loop_discount_rule);
            $fullReduceActivity->appoint_goods = \Yii::$app->serializer->encode($this->appoint_goods);
            $fullReduceActivity->noappoint_goods = \Yii::$app->serializer->encode($this->noappoint_goods);
            $fullReduceActivity->content = $this->content;
            $res = $fullReduceActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($fullReduceActivity));
            }

            $this->id = $fullReduceActivity->id;
            $this->model = $fullReduceActivity;
            $this->checkFullReduce();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'id' => $this->id
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * 检测是活动时间是否冲突
     * @throws \Exception
     */
    public function checkFullReduce()
    {
        if ($this->status == 0) {
            return true;
        }
        $check = self::check($this->model, $this->id, $this->start_at, $this->end_at);
        if ($check) {
            throw new \Exception('该时间段已有活动,请修改活动时间日期');
        }
    }

    private function checkDiscountRules($type)
    {
        if ($type == 1) {
            $discountRule = $this->discount_rule;
            if (empty($discountRule) || !is_array($discountRule)) {
                throw new \Exception('请填写满减规则');
            }
            if (count($discountRule) > 5) {
                throw new \Exception('至多添加5条阶梯规则');
            }
            $prevMinMoney = 0;
            $prevCut = 0;
            foreach ($discountRule as $key => $rule) {
                if (empty($rule['min_money'])) {
                    throw new \Exception('请输入优惠门槛');
                }
                if (!is_numeric($rule['min_money'])) {
                    throw new \Exception('优惠门槛必须是数字');
                }
                if ($rule['min_money'] > 99999999) {
                    throw new \Exception('优惠门槛不能大于99999999');
                }
                if ($rule['min_money'] <= $prevMinMoney) {
                    throw new \Exception($this->parseChinese($key) . '级优惠门槛必须大于上级门槛');
                }
                $prevMinMoney = $rule['min_money'];
                if (empty($rule['discount_type'])) {
                    throw new \Exception('请选择优惠类型');
                }
                if (!empty($rule['cut']) && !is_numeric($rule['cut'])) {
                    throw new \Exception('减钱必须是数字');
                }
                if (!empty($rule['discount']) && !is_numeric($rule['discount'])) {
                    throw new \Exception('折扣必须是数字');
                }
                if (
                    ($rule['discount_type'] == 1 && empty($rule['cut'])) ||
                    ($rule['discount_type'] == 2 && empty($rule['discount']))
                ) {
                    throw new \Exception('请输入优惠内容');
                }
                if ($rule['discount_type'] == 1) {
                    if (($rule['cut'] > $rule['min_money'])) {
                        throw new \Exception('优惠金额不能大于门槛');
                    }
                    if ($rule['cut'] > 99999999) {
                        throw new \Exception('优惠金额不能大于99999999');
                    }
                }
                if ($rule['discount_type'] == 2 && !($rule['discount'] >= 0.1 && $rule['discount'] <= 10)) {
                    throw new \Exception('阶梯折扣率不合法，阶梯折扣率必须在0.1折~10折。');
                }
                if ($rule['discount_type'] == 1) {
                    if ($rule['cut'] <= $prevCut) {
                        throw new \Exception($this->parseChinese($key) . '级优惠内容必须大于上级优惠');
                    } else {
                        $prevCut = $rule['cut'];
                    }
                }
            }
        } elseif ($type == 2) {
            $discountRule = $this->loop_discount_rule;
            if (empty($discountRule['min_money'])) {
                throw new \Exception('请输入优惠门槛');
            }
            if (!is_numeric($discountRule['min_money'])) {
                throw new \Exception('优惠门槛必须是数字');
            }
            if (empty($discountRule['cut'])) {
                throw new \Exception('请输入优惠内容');
            }
            if ($discountRule['min_money'] > 99999999) {
                throw new \Exception('优惠门槛不能大于99999999');
            }
            if ($discountRule['cut'] > 99999999) {
                throw new \Exception('优惠金额不能大于99999999');
            }
            if (!is_numeric($discountRule['cut'])) {
                throw new \Exception('优惠内容必须是数字');
            }
            if ($discountRule['cut'] > $discountRule['min_money']) {
                throw new \Exception('优惠金额不能大于门槛');
            }
        }
    }

    private function parseChinese($key)
    {
        switch ($key) {
            case 0:
                return '一';
            case 1:
                return '二';
            case 2:
                return '三';
            case 3:
                return '四';
            case 4:
                return '五';
            default:
                return '默认';
        }
    }
}
