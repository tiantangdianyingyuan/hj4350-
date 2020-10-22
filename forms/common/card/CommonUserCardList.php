<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 14:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\card;


use app\core\response\ApiCode;
use app\models\GoodsCardClerkLog;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCard;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonUserCardList extends Model
{
    public $mall;
    public $user;
    public $user_id;
    public $page;
    public $limit;
    public $status;
    public $date;
    public $isArray = false;
    public $clerk_id;
    public $user_card_id;

    /**
     * @return array
     * 获取某个用户的卡券列表
     */
    public function getUserCardList()
    {
        $query = UserCard::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->with(['clerk', 'store', 'card'])
            ->keyword($this->clerk_id, ['clerk_id' => $this->clerk_id])
            ->keyword($this->user_id, ['user_id' => $this->user_id])
            ->orderBy(['created_at' => SORT_DESC]);

        switch ($this->status) {
            case 1:
                $query->andWhere(['is_use' => 0])->andWhere(['>', 'end_time', mysql_timestamp()]);
                break;
            case 2:
                $query->andWhere(['is_use' => 1]);
                break;
            case 3:
                $query->andWhere(['is_use' => 0])->andWhere(['<=', 'end_time', mysql_timestamp()]);
                break;
            default:
        }

        $list = $query->page($pagination, $this->limit)->asArray($this->isArray)->all();
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * 卡券核销历史记录
     * @return array
     */
    public function getClerkHistory()
    {
        if (!$this->user_card_id) {
            throw new \Exception('请传入user_card_id');
        }

        $list = GoodsCardClerkLog::find()
            ->andWhere([
                'user_card_id' => $this->user_card_id
            ])
            ->with('store', 'user')
            ->orderBy(['clerked_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        /** @var UserCard $userCard */
        $userCard = UserCard::find()
            ->andWhere([
                'id' => $this->user_card_id,
                'mall_id' => \Yii::$app->mall->id
            ])
            ->one();


        $newList = [];
        // 兼容旧卡券数据
        if ($userCard->is_use && !$list && $this->page == 1) {
            $newList[] = [
                'id' => 0,
                'clerked_at' => new_date($userCard->clerked_at),
                'store_name' => $userCard->store->name,
                'clerk_user' => $userCard->user->nickname,
                'use_number' => $userCard->use_number,
                'surplus_number' => $userCard->number - $userCard->use_number,
            ];
        } else {
            /** @var GoodsCardClerkLog $item */
            foreach ($list as $item) {
                $newList[] = [
                    'id' => $item->id,
                    'clerked_at' => new_date($item->clerked_at),
                    'store_name' => $item->store->name,
                    'clerk_user' => $item->user->nickname,
                    'use_number' => $item->use_number,
                    'surplus_number' => $item->surplus_number,
                ];
            }
        }

        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}
