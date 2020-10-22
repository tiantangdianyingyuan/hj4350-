<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order_comments;

use app\core\response\ApiCode;
use app\models\OrderComments;
use app\models\Model;

class OrderCommentsReplyForm extends Model
{
    public $id;
    public $reply_content;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['reply_content'], 'string', 'max' => 255],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reply_content' => '回复评价',
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = OrderComments::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $this->id,
        ])
            ->with('user','detail')
            ->with(['goods.goodsWarehouse' => function ($query) {
                $query->select('name,cover_pic');
            }])
            ->asArray()
            ->one();
        $list['pic_url'] = json_decode($list['pic_url'], true);
        $list['goods_name'] = $list['goods']['goodsWarehouse']['name'];
        $list['cover_pic'] = $list['goods']['goodsWarehouse']['cover_pic'];
        $list['attr_list'] = json_decode($list['detail']['goods_info'], true)['attr_list'];
        $list['nickname'] = $list['is_virtual'] ? '(' . $list['virtual_user'] . ')' : $list['user']['nickname'];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = OrderComments::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ]);

        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除'
            ];
        }

        $model->attributes = $this->attributes;
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
