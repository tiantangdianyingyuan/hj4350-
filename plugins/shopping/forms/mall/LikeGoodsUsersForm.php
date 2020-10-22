<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\forms\mall;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\models\User;
use app\plugins\shopping\models\ShoppingLikes;
use app\plugins\shopping\models\ShoppingLikeUsers;

class LikeGoodsUsersForm extends Model
{
    public $id;
    public $keyword;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword'], 'string']
        ];
    }

    /**
     * 想买用户列表
     * @return array
     */
    public function getList()
    {
        $query = ShoppingLikeUsers::find()->where([
            'like_id' => $this->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $userIds = User::find()->where(['like', 'nickname', $this->keyword])->select('id');
            $query->andWhere(['user_id' => $userIds]);
        }

        $list = $query->with(['user.userInfo'])
            ->page($pagination)
            ->asArray()
            ->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
