<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/15
 * Time: 18:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\models\GoodsCardClerkLog;
use app\models\GoodsCards;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use yii\helpers\ArrayHelper;

class UserCardForm extends Model
{
    public $user_id;
    public $id;
    public $ids;
    public $status;
    public $clerk_id;
    public $store_id;
    public $user_name;
    public $store_name;
    public $card_name;

    public $send_date;
    public $clerk_date;

    public function rules()
    {
        return [
            [['status'], 'default', 'value' => -1],
            [['user_id', 'store_id', 'clerk_id', 'id'], 'integer'],
            [['user_name', 'send_date', 'clerk_date', 'store_name', 'card_name', 'ids'], 'trim']
        ];
    }

    public function getCard()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = UserCard::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with(['receive', 'from'])
            ->keyword($this->id, ['id' => $this->id])
            ->keyword($this->user_id, ['user_id' => $this->user_id])
            ->keyword($this->status == 1, ['is_use' => 0])
            ->keyword($this->status == 2, ['is_use' => 1])
            ->keyword($this->store_id, ['store_id' => $this->store_id])
            ->keyword($this->card_name, ['like', 'name', $this->card_name]);

            if ($this->clerk_id) {
                $userCardIds = GoodsCardClerkLog::find()->andWhere(['clerk_id' => $this->clerk_id])->select('user_card_id');
                $query->andWhere(['id' => $userCardIds]);
            }

        if ($this->user_name) {
            $userIds = User::find()
                ->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'is_delete' => 0,
                ])
                ->andWhere(['like', 'nickname', $this->user_name])
                ->select('id');
            $query->andWhere(['user_id' => $userIds]);
        }

        if ($this->store_name) {
            $storeIds = Store::find()
                ->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'is_delete' => 0
                ])
                ->andWhere(['like', 'name', $this->store_name])
                ->select('id');
            $query->andWhere(['store_id' => $storeIds]);
        }


        if ($this->send_date && count($this->send_date) > 0) {
            $query->andWhere([
                'AND',
                ['>=', 'created_at', $this->send_date[0]],
                ['<=', 'created_at', $this->send_date[1]]
            ]);
        };

        if ($this->clerk_date && count($this->clerk_date) > 0) {
            $query->andWhere([
                'AND',
                ['>=', 'clerked_at', $this->clerk_date[0]],
                ['<=', 'clerked_at', $this->clerk_date[1]]
            ]);
        };

        $list = $query
            ->orderBy('created_at DESC')
            ->with('user', 'store', 'lastClerkLog.store')
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var UserCard $item */
        foreach ($list as $item) {
            $newItem['id'] = $item->id;
            $newItem['nickname'] = $item->user->nickname;
            $newItem['name'] = $item->name;
            $newItem['pic_url'] = $item->pic_url;
            $newItem['content'] = $item->content;
            $newItem['is_use'] = $item->is_use;
            $newItem['number'] = $item->number;
            $newItem['use_number'] = $item->use_number;
            $newItem['clerked_at'] = new_date($item->clerked_at);
            $newItem['created_at'] = new_date($item->created_at);
            $newItem['store_name'] = $item->store ? $item->store->name : '';
            $newItem['receive'] = [
                'name' => $item->receive ? $item->receive->nickname : '',
                'platform' => $item->receive ? $item->receive->userInfo->platform : 'webapp'
            ];
            $newItem['from'] = [
                'name' => $item->from ? $item->from->nickname : '平台',
                'platform' => $item->from ? $item->from->userInfo->platform : 'webapp',
                'id' => $item->parent_card_id,
            ];

            $newItem = array_merge($newItem, $item->getNewItem($item));
            $newList[] = $newItem;
        }

        $byUsername = '';
        if ($this->user_id) {
            $byUser = User::findOne($this->user_id);
            $byUsername = $byUser ? $byUser->nickname : '';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'by_username' => $byUsername
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = UserCard::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $ids = $this->ids;
        if (!$ids) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }

        UserCard::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
            'id' => $ids,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}
