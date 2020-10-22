<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 15:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\card;


use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\GoodsCardClerkLog;
use app\models\Mall;
use app\models\Model;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserCard;
use yii\db\Exception;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonUserCard extends Model
{
    public $cardId;
    public $mall;
    public $user;
    public $userId;
    public $use_number;

    /**
     * @return array|\yii\db\ActiveRecord|null
     * @throws Exception
     * 卡券信息
     */
    public function detail()
    {
        $userCard = UserCard::find()
            ->where([
                'id' => $this->cardId,
                'is_delete' => 0,
                'mall_id' => $this->mall->id
            ])
            ->with(['clerk', 'store', 'order', 'detail', 'card'])
            ->one();

        if (!$userCard) {
            throw new Exception('卡券不存在，无效的信息');
        }

        return $userCard;
    }

    /**
     * 核销卡券
     * @return array
     * @throws \Exception
     */
    public function clerk()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var ClerkUser $clerkUser */
            $clerkUser = ClerkUser::find()->where([
                'mall_id' => $this->mall->id,
                'is_delete' => 0,
                'user_id' => $this->user->id,
                'mch_id' => 0,
            ])->one();

            if (!$clerkUser) {
                throw new Exception('没有核销权限，禁止核销');
            }

            /* @var UserCard $userCard */
            $userCard = $this->detail();

            if ($userCard->order->cancel_status == 2) {
                throw new Exception('订单申请退款中');
            }
            if ($userCard->order->cancel_status == 1) {
                throw new Exception('卡券已失效');
            }

            //售后中退款
            if ($userCard->detail->refund_status == 1) {
                $refund = OrderRefund::findOne(['order_detail_id' => $userCard->order_detail_id, 'type' => 1]);
                if (!empty($refund)) {
                    throw new Exception('订单申请退款中');
                }
            }
            //售后退款完成
            if ($userCard->detail->refund_status == 2 && $userCard->detail->is_refund == 1) {
                throw new Exception('卡券已失效');
            }

            if ($userCard->receive_id > 0) {
                throw new Exception('卡券已转赠');
            }

            if ($userCard->is_use == 1) {
                throw new Exception('卡券已核销');
            }

            if ($userCard->start_time >= mysql_timestamp()) {
                throw new Exception('卡券未到有效期，无法核销');
            }

            if ($userCard->end_time <= mysql_timestamp()) {
                throw new Exception('卡券已过期');
            }

            $currentSurplusNumber = $userCard->number - $userCard->use_number;
            if ($currentSurplusNumber <= 0) {
                throw new \Exception('卡券核销次数已用完');
            }

            if ($this->use_number > $currentSurplusNumber) {
                throw new \Exception('卡券可核销次数不足(剩余:' . $currentSurplusNumber . '次)');
            }

            $relation = ClerkUserStoreRelation::findOne(['clerk_user_id' => $clerkUser->id, 'is_delete' => 0]);

            $userCard->use_number = $userCard->use_number + $this->use_number;
            // 核销次数用完 卡券才算已核销
            if ($userCard->number - $userCard->use_number == 0) {
                $userCard->is_use = 1;
            }
            // TODO 核销时间 核销人 门店ID 每次都保存 是为了列表搜索查询
            $userCard->clerk_id = $this->user->id;
            $userCard->store_id = $relation->store_id;
            $userCard->clerked_at = mysql_timestamp();
            $res = $userCard->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($userCard));
            }

            $surplusNumber = $userCard->number - $userCard->use_number;

            /** @var GoodsCardClerkLog $log */
            $log = new GoodsCardClerkLog();
            $log->user_card_id = $userCard->id;
            $log->clerk_id = $this->user->id;
            $log->store_id = $relation->store_id;
            $log->use_number = $this->use_number;
            $log->surplus_number = $surplusNumber;
            $log->clerked_at = mysql_timestamp();
            $res = $log->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($log));
            }
            $transaction->commit();

            return [
                'surplus_number' => $surplusNumber
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }
}
