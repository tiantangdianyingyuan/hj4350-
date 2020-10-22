<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\scan_code_pay\jobs\CreateActivityJob;
use app\plugins\scan_code_pay\models\ScanCodePayActivities;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroups;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsLevel;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRules;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRulesCards;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRulesCoupons;

class ActivityEditForm extends Model
{
    public $id;
    public $name;
    public $start_time;
    public $end_time;
    public $send_type;
    public $status;
    public $rules;
    public $groups;


    private $newGroups;

    public function rules()
    {
        return [
            [['name', 'start_time', 'end_time', 'send_type', 'status'], 'required'],
            [['send_type', 'status', 'id'], 'integer'],
            [['rules'], 'string'],
            [['name',], 'trim'],
            [['groups'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '活动名称',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'send_type' => '赠送方式',
            'rules' => '买单规则',
            'status' => '活动状态',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setStatus();
            $activities = ScanCodePayActivities::findOne($this->id);
            if (!$activities) {
                $activities = new ScanCodePayActivities();
                $activities->mall_id = \Yii::$app->mall->id;
            }
            $activities->name = $this->name;
            $activities->start_time = $this->start_time;// TODO 判断日期格式
            $activities->end_time = $this->end_time;
            $activities->send_type = $this->send_type;
            $activities->status = $this->status;
            $activities->rules = $this->rules;
            $res = $activities->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($activities));
            }

            // TODO 添加活动结束定时任务

            $res = ScanCodePayActivitiesGroups::updateAll(['is_delete' => 1], ['activity_id' => $this->id]);
            /** @var ScanCodePayActivitiesGroups[] $dataGroups */
            $dataGroups = ScanCodePayActivitiesGroups::find()->where(['activity_id' => $activities->id])->all();
            foreach ($this->newGroups as $group) {
                $newGroup = $this->saveGroups($group, $dataGroups, $activities->id);

                // 设置会员等级
                $levelIds = [];
                foreach ($group['members'] as $member) {
                    $levelIds[] = $member['level'];
                }
                $res = ScanCodePayActivitiesGroupsLevel::updateAll(['is_delete' => 1], ['group_id' => $newGroup->id]);
                $dataGroupMembers = ScanCodePayActivitiesGroupsLevel::find()->where(['level' => $levelIds, 'group_id' => $newGroup->id])->all();
                $this->saveMembers($group['members'], $dataGroupMembers, $newGroup->id);

                // 设置组规则
                $ruleIds = [];
                foreach ($group['rules'] as $rule) {
                    if (isset($rule['id']) && $rule['id']) {
                        $ruleIds[] = $rule['id'];
                    }
                }
                $res = ScanCodePayActivitiesGroupsRules::updateAll(['is_delete' => 1], ['group_id' => $newGroup->id]);
                $dataGroupRules = ScanCodePayActivitiesGroupsRules::find()->where(['id' => $ruleIds])->all();
                $this->saveGroupsRules($group['rules'], $dataGroupRules, $newGroup->id);
            }

            // 活动时间到自动下架
            $second = strtotime($activities->end_time) - time() ? strtotime($activities->end_time) : 0;
            $queueId = \Yii::$app->queue->delay($second)->push(new CreateActivityJob([
                'activity_id' => $activities->id
            ]));

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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

    /**
     * 同时只能开启一个活动
     * @throws \Exception
     */
    private function setStatus()
    {
        $arr = [0, 1];
        if (!in_array($this->status, $arr)) {
            throw new \Exception('status 状态参数错误->' . $this->status);
        }

        if ($this->status) {
            $res = ScanCodePayActivities::updateAll(['status' => 0], ['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        }
    }

    /**
     * @param array $group 新组信息
     * @param array $dataGroups 数据组信息
     * @param string $activityId 活动ID
     * @return ScanCodePayActivitiesGroups|null
     * @throws \Exception
     */
    private function saveGroups($group, $dataGroups, $activityId)
    {
        /** @var ScanCodePayActivitiesGroups[] $dataGroups */
        $groupModel = null;
        foreach ($dataGroups as $dataGroup) {
            if (isset($group['id']) && $dataGroup->id == $group['id'] && $dataGroup->activity_id == $activityId) {
                $groupModel = $dataGroup;
                break;
            }
        }

        if (!$groupModel) {
            // 新增活动组
            $groupModel = new ScanCodePayActivitiesGroups();
        }
        $groupModel->is_delete = 0;
        $groupModel->activity_id = $activityId;
        $groupModel->name = $group['name'];
        $res = $groupModel->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($groupModel));
        }

        return $groupModel;
    }

    /**
     * @param array $members
     * @param int $groupId
     * @param array $dataGroupMembers
     * @return ScanCodePayActivitiesGroups|ScanCodePayActivitiesGroupsLevel|null
     * @throws \Exception
     */
    private function saveMembers($members, $dataGroupMembers, $groupId)
    {
        /** @var ScanCodePayActivitiesGroupsLevel[] $dataGroupMembers */
        foreach ($members as $member) {
            $groupMemberModel = null;
            foreach ($dataGroupMembers as $dataGroupMember) {
                if ($dataGroupMember->level == $member['level'] && $dataGroupMember->group_id == $groupId) {
                    $groupMemberModel = $dataGroupMember;
                    break;
                }
            }

            if (!$groupMemberModel) {
                // 新增活动组会员
                $groupMemberModel = new ScanCodePayActivitiesGroupsLevel();
            }
            $groupMemberModel->level = $member['level'];
            $groupMemberModel->group_id = $groupId;
            $groupMemberModel->is_delete = 0;
            $res = $groupMemberModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($groupMemberModel));
            }
        }
    }

    /**
     * @param $rules
     * @param $dataGroupRules
     * @param $groupId
     * @throws \Exception
     */
    private function saveGroupsRules($rules, $dataGroupRules, $groupId)
    {
        foreach ($rules as $rule) {
            $groupRulesModel = null;
            /** @var ScanCodePayActivitiesGroupsRules $dataGroupRule */
            foreach ($dataGroupRules as $dataGroupRule) {
                if (isset($rule['id']) && $dataGroupRule->id == $rule['id'] && $dataGroupRule->group_id == $groupId) {
                    $groupRulesModel = $dataGroupRule;
                    break;
                }
            }

            if (!$groupRulesModel) {
                // 新增活动组 规则
                $groupRulesModel = new ScanCodePayActivitiesGroupsRules();
            }
            $groupRulesModel->group_id = $groupId;
            $groupRulesModel->is_delete = 0;
            $groupRulesModel->rules_type = $rule['rules_type'];
            $groupRulesModel->consume_money = $rule['consume_money'];
            $groupRulesModel->send_integral_num = $rule['send_integral_num'];
            $groupRulesModel->send_integral_type = $rule['send_integral_type'];
            $groupRulesModel->send_money = $rule['send_money'];
            $groupRulesModel->preferential_money = $rule['preferential_money'];
            $groupRulesModel->integral_deduction = $rule['integral_deduction'];
            $groupRulesModel->integral_deduction_type = $rule['integral_deduction_type'];
            $groupRulesModel->is_coupon = $rule['is_coupon'];
            $res = $groupRulesModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($groupRulesModel));
            }

            $cardIds = [];
            foreach ($rule['cards'] as $card) {
                $cardIds[] = $card['card_id'];
            }
            $res = ScanCodePayActivitiesGroupsRulesCards::updateAll(['is_delete' => 1], ['group_rule_id' => $groupRulesModel->id]);
            $dataRuleCards = ScanCodePayActivitiesGroupsRulesCards::find()->where(['card_id' => $cardIds, 'group_rule_id' => $groupRulesModel->id])->all();
            $this->saveCards($rule['cards'], $dataRuleCards, $groupRulesModel->id);


            $couponIds = [];
            foreach ($rule['coupons'] as $coupon) {
                $couponIds[] = $coupon['coupon_id'];
            }
            $res = ScanCodePayActivitiesGroupsRulesCoupons::updateAll(['is_delete' => 1], ['group_rule_id' => $groupRulesModel->id]);
            $dataRuleCoupons = ScanCodePayActivitiesGroupsRulesCoupons::find()->where(['coupon_id' => $couponIds, 'group_rule_id' => $groupRulesModel->id])->all();
            $this->saveCoupons($rule['coupons'], $dataRuleCoupons, $groupRulesModel->id);
        }
    }

    /**
     * @param $cards
     * @param $dataRuleCards
     * @param $ruleId
     * @throws \Exception
     */
    private function saveCards($cards, $dataRuleCards, $ruleId)
    {
        foreach ($cards as $card) {
            /** @var ScanCodePayActivitiesGroupsRulesCards $ruleCardsModel */
            $ruleCardsModel = null;
            foreach ($dataRuleCards as $dataRuleCard) {
                if (isset($card['id']) && $dataRuleCard->id == $card['id'] && $dataRuleCard->group_rule_id == $ruleId) {
                    $ruleCardsModel = $dataRuleCard;
                    break;
                }
            }

            if (!$ruleCardsModel) {
                // 新增活动规则 卡券
                $ruleCardsModel = new ScanCodePayActivitiesGroupsRulesCards();
            }
            $ruleCardsModel->group_rule_id = $ruleId;
            $ruleCardsModel->is_delete = 0;
            $ruleCardsModel->card_id = $card['card_id'];
            $ruleCardsModel->send_num = $card['send_num'];
            $res = $ruleCardsModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($ruleCardsModel));
            }
        }
    }

    /**
     * @param $coupons
     * @param $dataRuleCoupons
     * @param $ruleId
     * @throws \Exception
     */
    private function saveCoupons($coupons, $dataRuleCoupons, $ruleId)
    {
        foreach ($coupons as $coupon) {
            /** @var ScanCodePayActivitiesGroupsRulesCoupons $ruleCouponModel */
            $ruleCouponModel = null;
            foreach ($dataRuleCoupons as $dataRuleCoupon) {
                if (isset($coupon['id']) && $dataRuleCoupon->id == $coupon['id'] && $dataRuleCoupon->group_rule_id == $ruleId) {
                    $ruleCouponModel = $dataRuleCoupon;
                    break;
                }
            }

            if (!$ruleCouponModel) {
                // 新增活动规则 卡券
                $ruleCouponModel = new ScanCodePayActivitiesGroupsRulesCoupons();
            }
            $ruleCouponModel->group_rule_id = $ruleId;
            $ruleCouponModel->is_delete = 0;
            $ruleCouponModel->coupon_id = $coupon['coupon_id'];
            $ruleCouponModel->send_num = $coupon['send_num'];
            $res = $ruleCouponModel->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($ruleCouponModel));
            }
        }
    }

    private function checkData()
    {
        if (!$this->groups || !is_string($this->groups)) {
            throw new \Exception('会员设置不能为空');
        }

        $this->newGroups = json_decode($this->groups, true);
        if (!is_array($this->newGroups)) {
            throw new \Exception('会员设置数据异常');
        }

        $levelArr = [];
        foreach ($this->newGroups as &$newGroup) {
            if (!isset($newGroup['name']) || !$newGroup['name']) {
                throw new \Exception('用户组名称不能为空');
            }

            if (!isset($newGroup['members']) || !is_array($newGroup['members']) || !$newGroup['members']) {
                throw new \Exception($newGroup['name'] . '用户组成员不能为空');
            }

            foreach ($newGroup['members'] as $member) {
                if (!isset($member['name']) || !isset($member['level'])) {
                    throw new \Exception('用户组会员信息不完整');
                }
                if (in_array($member['level'], $levelArr)) {
                    throw new \Exception('用户组会员不能重复->' . $member['name']);
                }
                $levelArr[] = $member['level'];
            }

            if (!isset($newGroup['send_rules']) || !is_array($newGroup['send_rules'])) {
//                || !$newGroup['send_rules']
                throw new \Exception($newGroup['name'] . '用户组赠送规则数据不能为空');
            }
            if (!isset($newGroup['preferential_rules']) || !is_array($newGroup['preferential_rules'])) {
//                || !$newGroup['preferential_rules']
                throw new \Exception($newGroup['name'] . '用户组优惠规则数据不能为空');
            }
            // 将规则数组合并 数据表是同一张
            $newGroup['rules'] = array_merge($newGroup['send_rules'], $newGroup['preferential_rules']);

            $consumeMoneyArr = [];
            $sendConsumeMoneyArr = [];
            foreach ($newGroup['rules'] as $rule) {
                $params = [
                    'rules_type' => '规则类型',
                    'consume_money' => '单次消费金额',
                    'send_integral_num' => '赠送积分数量',
                    'send_integral_type' => '赠送积分类型',
                    'send_money' => '赠送余额',
                    'preferential_money' => '优惠金额',
                    'integral_deduction' => '积分抵扣',
                    'integral_deduction_type' => '积分抵扣类型',
                    'is_coupon' => '是否使用优惠券'
                ];


                if (strstr($rule['integral_deduction'], '.')) {
                    throw new \Exception($params['integral_deduction'] . '必须为整数');
                }

                foreach ($params as $paramKey => $param) {
                    if (!isset($rule[$paramKey])) {
                        throw new \Exception('请添加' . '规则组 ' . $newGroup['name'] . '信息');
                    }
                    if (!is_numeric($rule[$paramKey])) {
                        throw new \Exception('规则组 ' . $newGroup['name'] . $param . '需为数值');
                    }
                    if ($rule[$paramKey] < 0) {
                        throw new \Exception('规则组 ' . $newGroup['name'] . $param . '不能小于0');
                    }
                }

                if ($rule['rules_type'] == 1) {
                    if (in_array($rule['consume_money'], $sendConsumeMoneyArr)) {
                        throw new \Exception('同一个赠送规则组,消费金额值不能相同');
                    }
                    $sendConsumeMoneyArr[] = $rule['consume_money'];
                } else {
                    if (in_array($rule['consume_money'], $consumeMoneyArr)) {
                        throw new \Exception('同一个优惠规则组,消费金额值不能相同');
                    }
                    $consumeMoneyArr[] = $rule['consume_money'];
                }

                if (isset($rule['cards'])) {
                    if (!is_array($rule['cards'])) {
                        throw new \Exception('用户组规则 卡券数据格式不正确');
                    }
                    foreach ($rule['cards'] as $card) {
                        $params1 = 'send_num';
                        $params2 = 'card_id';
                        if (!isset($card[$params1]) || !is_numeric($card[$params1])) {
                            throw new \Exception('卡券数据错误,缺少参数或格式错误' . $params1);
                        }

                        if (!isset($card[$params2]) || !is_numeric($card[$params2])) {
                            throw new \Exception('卡券数据错误,缺少参数或格式错误' . $params2);
                        }
                    }
                }

                if (isset($rule['coupons'])) {
                    if (!is_array($rule['coupons'])) {
                        throw new \Exception('用户组规则 优惠券数据格式不正确');
                    }

                    foreach ($rule['coupons'] as $coupon) {
                        $params1 = 'send_num';
                        $params2 = 'coupon_id';
                        if (!isset($coupon[$params1]) || !is_numeric($coupon[$params1])) {
                            throw new \Exception('优惠券数据错误,缺少参数或格式错误' . $params1);
                        }

                        if (!isset($coupon[$params2]) || !is_numeric($coupon[$params2])) {
                            throw new \Exception('优惠券数据错误,缺少参数或格式错误' . $params2);
                        }
                    }
                }
            }
        }
        unset($newGroup);
    }
}