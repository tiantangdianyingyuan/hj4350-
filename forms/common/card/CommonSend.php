<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/13
 * Time: 11:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\card;


use app\jobs\UserCardCreatedJob;
use app\models\GoodsCardRelation;
use app\models\GoodsCards;
use app\models\Model;
use app\models\OrderDetail;
use app\models\UserCard;
use yii\db\Exception;

class CommonSend extends Model
{
    public $mall_id;
    public $user_id;
    public $order_id;

    const hour = 6; //hour

    /**
     * @return array
     * @throws Exception
     */
    public function save()
    {
        $goodsList = OrderDetail::find()
            ->with('card')
            ->where([
                'is_delete' => 0,
                'order_id' => $this->order_id
            ])->all();

        if (!$goodsList) {
            throw new Exception('商品不存在，无效的order_id');
        }
        $commonCard = new CommonCard();
        $cardList = [];
        /** @var OrderDetail $item */
        foreach ($goodsList as $item) {
            /* @var GoodsCards[] $goodsCardList */
            $goodsCardList = $item->goodsCard;
            if (empty($goodsCardList)) {
                continue;
            }
            /** @var GoodsCardRelation $card */
            foreach ($goodsCardList as $card) {
                if ($card->goodsCards->is_delete !== 0) {
                    continue;
                }
                $count = 0;
                while ($count < bcmul($item->num, $card->num)) {
                    /** @var GoodsCards $value */
                    $value = $card->goodsCards;
                    $commonCard->user_id = $this->user_id;
                    $cardList[] = $commonCard->receive($value, $this->order_id, $item->id);
                    $count++;
                }
            }
        }
        return $cardList;
    }
}
