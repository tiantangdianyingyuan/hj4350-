<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\forms\common\clerk_user\CommonClerkUserList;
use app\forms\mall\export\ClerkExport;
use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\Model;
use app\models\Order;
use app\models\Store;
use app\models\User;
use app\models\UserCard;

class ClerkForm extends Model
{
    public $page_size;
    public $page;
    public $keyword;
    public $id;

    public $store_id;

    public $order_sort;
    public $card_sort;
    public $sum_sort;
    public $platform;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['page_size'], 'default', 'value' => 10],
            [['keyword'], 'string', 'max' => 255],
            [['id', 'store_id', 'page'], 'integer'],
            [['platform'], 'string'],
            [['flag'], 'trim'],
            [['fields'], 'safe'],
        ];
    }

    //用户列表
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $form = new CommonClerkUserList();
        $form->is_array = 1;
        $form->is_user = 1;
        $form->is_store = 1;
        $form->is_card_count = 1;
        $form->is_order_count = 1;
        $form->is_total_sum = 1;
        $form->keyword = $this->keyword ?: null;
        $form->mch_id = \Yii::$app->user->identity->mch_id;
        $form->store_id = $this->store_id ?: null;
        $form->page = $this->page;
        $form->order_sort = $this->order_sort ? $this->order_sort : 0;
        $form->card_sort = $this->card_sort ? $this->card_sort : 0;
        $form->sum_sort = $this->sum_sort ? $this->sum_sort : 0;
        $form->platform = $this->platform;
        $form->is_pagination = $this->flag == 'EXPORT' ? 0 : 1;
        $list = $form->search();

        if ($this->flag == "EXPORT") {
            $new_query = clone $form->query;
            $exp = new ClerkExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        foreach ($list as &$item) {
            $totalPrice = Order::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'clerk_id' => $item['id'],
            ])->sum('total_pay_price');

            $item['total_price'] = $totalPrice ?: 0; //总额
        }
        $storeList = $this->getStore();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
                'store_list' => $storeList,
                'exportList' => (new ClerkExport())->fieldsList(),
            ]
        ];
    }

    private function getStore()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $query = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        $list = $query->asArray()->all();
        return $list;
    }

    public function clerkUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        /** @var ClerkUser $clerkUser */
        $query = ClerkUser::find()->alias('c')->where([
            'c.mall_id' => \Yii::$app->mall->id,
            'c.is_delete' => 0,
            'c.mch_id' => \Yii::$app->user->identity->mch_id,
        ])->with('user');

        if ($this->keyword) {
            $userIds = User::find()->where([
                'like', 'nickname', $this->keyword,
            ])->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
            $query->andWhere(['c.user_id' => $userIds]);
        }

        $list = $query->innerJoinwith(['store s' => function ($query) {
            $query->keyword($this->store_id, ['s.id' => $this->store_id]);
        }])->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' =>  '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function destroy()
    {
        try {
            $clerkUser = ClerkUser::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id
            ]);
            if (!$clerkUser) {
                throw new \Exception('核销员不存在');
            }

            $clerkUser->is_delete = 1;
            $res = $clerkUser->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($clerkUser));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '解除成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
