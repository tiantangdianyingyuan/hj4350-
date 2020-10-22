<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\clerk;

use app\core\response\ApiCode;
use app\forms\mall\clerk\BaseClerk;
use app\forms\mall\export\ClerkCardExport;
use app\models\ClerkUser;
use app\models\GoodsCardClerkLog;
use app\models\Store;
use app\models\UserCard;

class ClerkCardForm extends BaseClerk
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

        $query = UserCard::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_use' => 1,
            'is_delete' => 0,
        ]);

        switch ($this->keyword_name) {
            case 'card_id':
                $query->andWhere(['like', 'card_id', $this->keyword]);
                break;
            case 'card_name':
                $query->andWhere(['like', 'name', $this->keyword]);
                break;
            default:
                break;
        }

        if ($this->time) {
            $query->andWhere(['>=', 'clerked_at', $this->time[0]])
                ->andWhere(['<=', 'clerked_at', $this->time[1]]);
        }

        if ($this->clerk_id) {
            $userCardIds = GoodsCardClerkLog::find()->andWhere(['clerk_id' => $this->clerk_id])->select('user_card_id');

            $query->andWhere([
                'or',
                ['clerk_id' => $this->clerk_id],
                ['id' => $userCardIds]
            ]);
        }

        if ($this->flag == "EXPORT") {
            $exp = new ClerkCardExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->page = $this->page;
            return $exp->export($query);
        }

        $list = $query->with('lastClerkLog.store', 'lastClerkLog.user.userInfo', 'clerk.userInfo', 'store')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        foreach ($list as $key => $item) {
            $newItem = [];
            $newItem['card_id'] = $item->card_id;
            $newItem['card_name'] = $item->name;
            if ($item->lastClerkLog) {
                $newItem['clerk_user_id'] = $item->lastClerkLog->user->id;
                $newItem['clerk_user_name'] = $item->lastClerkLog->user->nickname;
                $newItem['clerk_user_avatar'] = $item->lastClerkLog->user->userInfo->avatar;
                $newItem['clerk_user_platform'] = $item->lastClerkLog->user->userInfo->platform;
                $newItem['clerk_store_name'] = $item->lastClerkLog->store->name;
                $newItem['clerk_number'] = $item->number - $item->lastClerkLog->surplus_number;
                $newItem['clerk_time'] = $item->lastClerkLog->clerked_at;
            } else {
                $newItem['clerk_user_id'] = $item->clerk->id;
                $newItem['clerk_user_name'] = $item->clerk->nickname;
                $newItem['clerk_user_avatar'] = $item->clerk->userInfo->avatar;
                $newItem['clerk_user_platform'] = $item->clerk->userInfo->platform;
                $newItem['clerk_store_name'] = $item->store->name;
                $newItem['clerk_number'] = 1;
                $newItem['clerk_time'] = $item->clerked_at;
            }

            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'export_list' => (new ClerkCardExport())->fieldsList(),
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
            $newClerkUserItem['id'] = $clerkUser->user_id;
            $platform = $clerkUser->user->getPlatform($clerkUser->user->userInfo->platform);
            $newClerkUserItem['name'] = sprintf('(%s)%s', $platform, $clerkUser->user->nickname);
            $newClerkuserList[] = $newClerkUserItem;
        }

        return $newClerkuserList;
    }
}
