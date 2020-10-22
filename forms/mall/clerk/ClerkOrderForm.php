<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\clerk;

use app\core\response\ApiCode;
use app\forms\mall\clerk\BaseClerk;
use app\forms\mall\export\ClerkOrderExport;
use app\models\ClerkUser;
use app\models\Order;

class ClerkOrderForm extends BaseClerk
{
    public $time;
    public $keyword;
    public $keyword_name;
    public $clerk_id;

    public $flag;
    public $fields;
    public $page;

    public function rules()
    {
        return [
            [['keyword', 'keyword_name', 'flag'], 'trim'],
            [['time', 'fields'], 'safe'],
            [['page', 'clerk_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = Order::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'send_type' => 1,
        ])->andWhere(['>', 'clerk_id', 0]);

        switch ($this->keyword_name) {
            case 'order_no':
                $query->andWhere(['like', 'order_no', $this->keyword]);
                break;

            default:
                break;
        }

        if ($this->time) {
            $query->andWhere(['>=', 'send_time', $this->time[0]])
                ->andWhere(['<=', 'send_time', $this->time[1]]);
        }

        if ($this->clerk_id) {
            $query->andWhere(['clerk_id' => $this->clerk_id]);
        }

        if ($this->flag == "EXPORT") {
            $exp = new ClerkOrderExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->page = $this->page;
            return $exp->export($query);
        }

        $list = $query->with('store', 'clerkUser.user.userInfo')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        foreach ($list as $key => $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['order_no'] = $item->order_no;
            $newItem['clerk_user_id'] = $item->clerkUser->user->id;
            $newItem['clerk_user_name'] = $item->clerkUser->user->nickname;
            $newItem['clerk_user_avatar'] = $item->clerkUser->user->userInfo->avatar;
            $newItem['clerk_user_platform'] = $item->clerkUser->user->userInfo->platform;
            $newItem['clerk_store_name'] = $item->store->name;
            $newItem['clerk_time'] = $item->send_time;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'export_list' => (new ClerkOrderExport())->fieldsList(),
                'clerk_user_list' => $this->getClerkUserList(),
                'pagination' => $pagination,
            ],
        ];
    }

    private function getClerkUserList()
    {
        $clerkUserlist = ClerkUser::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->with('user.userInfo')->all();

        $newClerkuserList = [];
        foreach ($clerkUserlist as $key => $clerkUser) {
            $newClerkUserItem = [];
            $newClerkUserItem['id'] = $clerkUser->id;
            $platform = $clerkUser->user->getPlatform($clerkUser->user->userInfo->platform);
            $newClerkUserItem['name'] = sprintf('(%s)%s', $platform, $clerkUser->user->nickname);
            $newClerkuserList[] = $newClerkUserItem;
        }

        return $newClerkuserList;
    }
}
