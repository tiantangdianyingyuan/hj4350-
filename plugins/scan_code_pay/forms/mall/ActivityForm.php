<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonMallMember;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\MallMembers;
use app\models\Model;
use app\plugins\scan_code_pay\models\ScanCodePayActivities;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroups;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsLevel;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRules;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRulesCards;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRulesCoupons;
use yii\helpers\ArrayHelper;

class ActivityForm extends Model
{
    public $activity_id;
    public $status;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['activity_id', 'page', 'status'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['search'], 'safe'],
            [['keyword'], 'trim']
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = ScanCodePayActivities::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var ScanCodePayActivities $activity */
        $activity = ScanCodePayActivities::find()->where([
            'id' => $this->activity_id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])
            ->with(['groups.members', 'groups.rules', 'groups.rules.scanCards.cards', 'groups.rules.scanCoupons.coupons',
                'groups.scanMembers'])
            ->one();

        if (!$activity) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '活动不存在'
            ];
        }

        $newActivity = ArrayHelper::toArray($activity);
        /** @var ScanCodePayActivitiesGroups $group */
        $newGroups = [];
        foreach ($activity->groups as $group) {
            $newGroup = ArrayHelper::toArray($group);
            /** @var ScanCodePayActivitiesGroupsLevel $scanMember */
            $members = [];
            foreach ($group->scanMembers as $scanMember) {
                if ($scanMember->level == 0) {
                    $arr = [];
                    $arr['name'] = '普通用户';
                    $arr['id'] = $scanMember->id;
                    $arr['group_id'] = $scanMember->group_id;
                    $arr['level'] = $scanMember->level;
                    $arr['is_delete'] = $scanMember->is_delete;
                    $members[] = $arr;
                }
            }
            $newGroup['members'] = array_merge($members, ArrayHelper::toArray($group->members));

            $newRules = [];
            /** @var ScanCodePayActivitiesGroupsRules $rule */
            foreach ($group->rules as $rule) {
                $newRule = ArrayHelper::toArray($rule);
                $newCards = [];
                foreach ($rule->scanCards as $scanCard) {
                    $newCard['id'] = $scanCard->id;
                    $newCard['send_num'] = $scanCard->send_num;
                    $newCard['card_id'] = $scanCard->cards->id;
                    $newCard['name'] = $scanCard->cards->name;
                    $newCards[] = $newCard;
                }
                $newRule['cards'] = $newCards;

                $newCoupons = [];
                foreach ($rule->scanCoupons as $scanCoupon) {
                    $newCoupon['id'] = $scanCoupon->id;
                    $newCoupon['send_num'] = $scanCoupon->send_num;
                    $newCoupon['coupon_id'] = $scanCoupon->coupons->id;
                    $newCoupon['name'] = $scanCoupon->coupons->name;
                    $newCoupons[] = $newCoupon;
                }
                $newRule['coupons'] = $newCoupons;
                $newRules[] = $newRule;
            }

            // 将赠送规则和 优惠规则分成两个数组
            $sendRules = [];
            $preferentialRules = [];
            foreach ($newRules as $newRule) {
                if ($newRule['rules_type'] == 1) {
                    $sendRules[] = $newRule;
                } else {
                    $preferentialRules[] = $newRule;
                }
            }

            $newGroup['send_rules'] = $sendRules;
            $newGroup['preferential_rules'] = $preferentialRules;
            $newGroups[] = $newGroup;
        }
        $newActivity['groups'] = $newGroups;


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'activity' => $newActivity,
            ]
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $arr = [0, 1];
            if (!in_array($this->status, $arr)) {
                throw new \Exception('status 状态参数错误->' . $this->status);
            }
            /** @var ScanCodePayActivities $activity */
            $activity = ScanCodePayActivities::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->activity_id
            ])->one();
            if (!$activity) {
                throw new \Exception('活动不存在');
            }

            // 当前场次活动只允许有一个
            if ($this->status) {
                $res = ScanCodePayActivities::updateAll(['status' => 0], ['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            }

            $activity->status = $this->status;
            $res = $activity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($activity));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
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

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var ScanCodePayActivities $activity */
            $activity = ScanCodePayActivities::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->activity_id
            ])->one();
            if (!$activity) {
                throw new \Exception('活动不存在');
            }

            $activity->is_delete = 1;
            $res = $activity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($activity));
            }

            /** @var ScanCodePayActivitiesGroups[] $groups */
            $groups = ScanCodePayActivitiesGroups::find()
                ->where(['activity_id' => $activity->id, 'is_delete' => 0])
                ->with(['scanMembers', 'rules.scanCards', 'rules.scanCoupons'])
                ->all();
            $groupIds = [];
            $groupMemberIds = [];
            $groupRuleIds = [];
            $ruleCardIds = [];
            $ruleCouponIds = [];
            foreach ($groups as $group) {
                $groupIds[] = $group->id;
                foreach ($group->scanMembers as $scanMember) {
                    $groupMemberIds[] = $scanMember->id;
                }
                foreach ($group->rules as $rule) {
                    $groupRuleIds[] = $rule->id;
                    foreach ($rule->scanCards as $scanCard) {
                        $ruleCardIds[] = $scanCard->id;
                    }
                    foreach ($rule->scanCoupons as $scanCoupon) {
                        $ruleCouponIds[] = $scanCoupon->id;
                    }
                }
            }
            $res = ScanCodePayActivitiesGroups::updateAll(['is_delete' => 1], ['id' => $groupIds]);
            $res = ScanCodePayActivitiesGroupsLevel::updateAll(['is_delete' => 1], ['id' => $groupMemberIds]);
            $res = ScanCodePayActivitiesGroupsRules::updateAll(['is_delete' => 1], ['id' => $groupRuleIds]);
            $res = ScanCodePayActivitiesGroupsRulesCards::updateAll(['is_delete' => 1], ['id' => $ruleCardIds]);
            $res = ScanCodePayActivitiesGroupsRulesCoupons::updateAll(['is_delete' => 1], ['id' => $ruleCouponIds]);


            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
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

    public function getMembers()
    {
        $query = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ]);
        $members = $query->orderBy('level')->all();
        $newMembers = [
            [
                'level' => 0,
                'name' => '普通用户'
            ]
        ];
        /** @var MallMembers $member */
        foreach ($members as $member) {
            $newMembers[] = [
                'level' => $member->level,
                'name' => $member->name
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'members' => $newMembers,
            ]
        ];
    }

    public function getCoupons()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $coupons = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'coupons' => $coupons,
                'pagination' => $pagination
            ]
        ];
    }

    public function getCards()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = GoodsCards::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);


        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $cards = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'cards' => $cards,
                'pagination' => $pagination
            ]
        ];
    }
}