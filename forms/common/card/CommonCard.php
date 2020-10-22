<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/8/10
 * Time: 16:25
 */

namespace app\forms\common\card;

use app\jobs\UserCardCreatedJob;
use app\models\GoodsCards;
use app\models\Model;
use app\models\UserCard;
use yii\db\Exception;

class CommonCard extends Model
{
    const hour = 6; //hour

    public $mall;
    public $user;
    public $user_id;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->mall = \Yii::$app->mall;
    }

    /**
     * @param GoodsCards $card
     * @param int $order_id
     * @param int $order_detail_id
     * @param string $remark
     * @return UserCard
     */
    public function receive(GoodsCards $card, $order_id = 0, $order_detail_id = 0, $remark = '', $send_num = 1)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $card->updateCount('sub', $send_num);
            if ($card->expire_type == 1) {
                $endTime = date('Y-m-d H:i:s', time() + $card->expire_day * 86400);
            } else {
                $endTime = $card->end_time;
            }
            for ($i = 0; $i < $send_num; $i++) {
                $userCard = new UserCard();
                $userCard->mall_id = $this->mall->id;
                $userCard->user_id = $this->user_id;
                $userCard->card_id = $card->id;
                $userCard->name = $card->name;
                $userCard->pic_url = $card->pic_url;
                $userCard->content = $card->description;
                $userCard->created_at = mysql_timestamp();
                $userCard->is_use = 0;
                $userCard->clerk_id = 0;
                $userCard->store_id = 0;
                $userCard->clerked_at = '0000-00-00 00:00:00';
                $userCard->order_id = $order_id;
                $userCard->order_detail_id = $order_detail_id;
                $userCard->data = '';
                $userCard->remark = $remark;
                $userCard->start_time = $card->expire_type == 1 ? mysql_timestamp() : $card->begin_time;
                $userCard->end_time = $endTime;
                $userCard->number = $card->number;
                $userCard->save();

                $interval = self::hour * 3600;
                $diff = strtotime($endTime) - time();
                $diff = $diff > $interval ? $diff - $interval : 0;

                \Yii::$app->queue->delay($diff)->push(
                    new UserCardCreatedJob(
                        [
                            'mall' => \Yii::$app->mall,
                            'id' => $userCard->id,
                            'user_id' => $this->user_id,
                        ]
                    )
                );
            }
            $t->commit();
            return $userCard;
        } catch (Exception $exception) {
            $t->rollBack();
            \Yii::error('卡券发放失败');
            \Yii::error($exception);
        }
    }
}
