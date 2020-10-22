<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\coupon;

use app\core\response\ApiCode;
use app\models\CouponAutoSend;
use app\models\Coupon;
use app\models\Model;
use app\models\User;

class CouponAutoSendForm extends Model
{
    public $page;
    public $page_size;

    public $id;
    public $mall_id;
    public $coupon_id;
    public $event;
    public $send_count;
    public $is_delete;
    public $user_list;
    public $type;

    public function rules()
    {
        return [
            [['id', 'mall_id', 'coupon_id', 'event', 'send_count', 'is_delete', 'type'], 'integer'],
            [['send_count'], 'integer', 'max' => 999999999],
            [['page', 'send_count'], 'default', 'value' => 1],
            [['is_delete'], 'default', 'value' => 0],
            [['page_size'], 'default', 'value' => 10],
            [['user_list'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'coupon_id' => '优惠券',
            'event' => '触发条件',
            'send_count' => '最多发放次数',
            'is_delete' => '删除',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = CouponAutoSend::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->with('coupon')
                ->page($pagination, $this->page_size, $this->page)
                ->orderBy('id ASC,created_at DESC')
                ->asArray()
                ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = CouponAutoSend::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
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

    //DETAIL
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = CouponAutoSend::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ])->one();
        if ($list) {
            $userIdList = $list->user_list ? json_decode($list->user_list, true) : [];
            $userList = User::find()->where(['id' => $userIdList, 'mall_id' => \Yii::$app->mall->id])
                ->with('userInfo')->all();
            $newUserList = [];
            /* @var User[] $userList */
            foreach ($userList as $user) {
                $newUserList[] = [
                    'user_id' => $user->id,
                    'nickname' => $user->nickname,
                    'avatar' => $user->userInfo->avatar,
                    'platform' => $user->userInfo->platform
                ];
            }

            $list->user_list = $newUserList;
        }
        $coupon_list = Coupon::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'coupon_list' => $coupon_list,
                'list' => $list,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = CouponAutoSend::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);

        if (!$model) {
            $model = new CouponAutoSend();
        }

        if ($this->user_list) {
            $userIdList = [];
            foreach ($this->user_list as $item) {
                $userIdList[] = $item['user_id'];
            }
            $this->user_list = \Yii::$app->serializer->encode($userIdList);
        }
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
