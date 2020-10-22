<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role_user;


use app\core\response\ApiCode;
use app\models\CoreActionLog;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use yii\helpers\ArrayHelper;

class ActionForm extends Model
{
    public $keyword;
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['page', 'id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        $query = User::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'mch_id' => 0]);
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'username', $this->keyword],
                ['like', 'nickname', $this->keyword],
            ]);
        }
        $userIds = $query->select('id');
        $userIds = UserIdentity::find()->where(['user_id' => $userIds, 'is_operator' => 1])->select('user_id');

        $list = CoreActionLog::find()->where(['user_id' => $userIds, 'is_delete' => 0])
            ->with('user')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var CoreActionLog $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['user'] = $item->user;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        /** @var CoreActionLog $detail */
        $detail = CoreActionLog::find()->where(['id' => $this->id])
            ->with('user')
            ->one();

        $detail->after_update = \Yii::$app->serializer->decode($detail->after_update);
        $detail->before_update = \Yii::$app->serializer->decode($detail->before_update);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'detail' => $detail,
            ]
        ];
    }
}