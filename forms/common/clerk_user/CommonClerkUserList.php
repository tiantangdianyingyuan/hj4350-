<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\clerk_user;


use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\GoodsCardClerkLog;
use app\models\Model;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use app\models\UserInfo;
use yii\db\Query;

class CommonClerkUserList extends Model
{
    /**
     * @var Query $query
     */
    public $query;
    public $pagination;
    public $is_pagination;

    public $mall_id;
    public $all_mch;
    public $mch_id;
    public $limit;
    public $page;
    public $keyword;
    public $is_array;
    public $sort;
    public $sum_sort;
    public $card_sort;
    public $order_sort;
    public $is_card_count;
    public $is_order_count;
    public $is_total_sum;
    public $store_id;
    public $platform;

    /**
     * 关联关系
     * @var
     */
    public $is_user;
    public $is_mch;
    public $is_store;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['mall_id', 'limit', 'mch_id', 'is_array', 'limit', 'sort',
                'is_pagination', 'is_user', 'is_mch', 'all_mch', 'is_store', 'is_card_count',
                'is_order_count', 'store_id'], 'integer'],
            [['limit',], 'default', 'value' => 10],
            [['sort', 'mch_id', 'is_array', 'sum_sort', 'card_sort', 'order_sort'], 'default', 'value' => 0],
            [['page', 'is_pagination'], 'default', 'value' => 1],
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    private function getMethod($key)
    {
        $array = [
            'keyword' => 'setKeyword',
            'sort' => 'setSortWhere',
            'all_mch' => 'setAllMch',
            'mch_id' => 'setMchId',
            'store_id' => 'setStoreId',
            'is_mch' => 'setWithMch',
            'is_user' => 'setWithUser',
            'is_store' => 'setWithStore',
            'is_card_count' => 'setCardCount',
            'is_order_count' => 'setOrderCount',
            'is_total_sum' => 'setTotalSum',
            'platform' => 'setPlatform',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    //持续改进
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            $this->query = $query = ClerkUser::find()->alias('u')->with('store')->where([
                'u.mall_id' => \Yii::$app->mall->id,
                'u.is_delete' => 0,
            ]);
            $this->query->select('u.*');
            foreach ($this->attributes as $key => $value) {
                $method = $this->getMethod($key);
                if ($method && method_exists($this, $method) && $value !== null) {
                    $this->$method();
                }
            }
            if ($this->is_pagination) {
                $this->query->page($this->pagination, $this->limit, $this->page);
            }

            $list = $this->query->asArray($this->is_array)->groupBy('u.id')->all();
            foreach ($list as $key => $value) {
                $list[$key]['card_count'] = $value['card_count'] ? $value['card_count'] : 0;
            }

            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private function setKeyword()
    {
        $userIds = User::find()->where(['like', 'nickname', $this->keyword])->select('id');
        $storeIds = Store::find()->where(['like', 'name', $this->keyword])->select('id');
        $userStoreIds = ClerkUserStoreRelation::find()->where(['store_id' => $storeIds])->select('clerk_user_id');
        $clerkUserIds = ClerkUser::find()->where(['id' => $userStoreIds])->select('user_id');
        $this->query->andWhere([
            'or',
            ['u.user_id' => $userIds],
            ['u.user_id' => $clerkUserIds],
        ]);
    }

    private function setAllMch()
    {
        $this->query->andWhere(['>', 'u.mch_id', 0]);
    }

    private function setMchId()
    {
        $this->query->andWhere(['u.mch_id' => $this->mch_id]);
    }

    private function setStoreId()
    {
        $clerkUserId = ClerkUserStoreRelation::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ])->select('clerk_user_id');

        $this->query->andWhere(['u.id' => $clerkUserId]);
    }

    private function setWithMch()
    {
        $this->query->with('mch');
    }

    private function setWithUser()
    {
        $this->query->with('user.userInfo');
    }

    private function setWithStore()
    {
        $this->query->with('store');
    }

    private function setCardCount()
    {
        $cardQuery = GoodsCardClerkLog::find()->andWhere('clerk_id = u.user_id')->select('SUM(use_number)');
        $this->query->addSelect([
            'card_count' => $cardQuery
        ]);
        isset($this->card_sort) && !empty($this->card_sort) && $this->query->orderBy(['card_count' => $this->card_sort, 'u.id' => SORT_ASC]);
    }

    private function setOrderCount()
    {
        $orderCount = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere('clerk_id = u.id')->select('count(1)');
        $this->query->addSelect([
            'order_count' => $orderCount
        ]);
        isset($this->order_sort) && !empty($this->order_sort) && $this->query->orderBy(['order_count' => $this->order_sort, 'u.id' => SORT_ASC]);
    }

    private function setSortWhere()
    {
        switch ($this->sort) {
            case 1:
                $this->query->orderBy(['u.created_at' => SORT_DESC]);
                break;
            default:
        }
    }

    private function setTotalSum()
    {
        $orderSum = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere('clerk_id = u.id')->select("SUM(total_pay_price)");
        $this->query->addSelect([
            'order_sum' => $orderSum
        ]);
        isset($this->sum_sort) && !empty($this->sum_sort) && $this->query->orderBy(['order_sum' => $this->sum_sort, 'u.id' => SORT_ASC]);
    }

    private function setPlatform()
    {
        if ($this->platform) {
            $this->query->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id=u.user_id')
                ->andWhere(['ui.platform' => $this->platform]);
        }
    }
}
