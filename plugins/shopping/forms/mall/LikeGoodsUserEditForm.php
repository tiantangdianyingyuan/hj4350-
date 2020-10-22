<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\shopping\forms\common\CommonShopping;
use app\plugins\shopping\models\ShoppingLikes;

class LikeGoodsUserEditForm extends Model
{
    public $user_id;
    public $id;

    public function rules()
    {
        return [
            [['user_id', 'id'], 'integer']
        ];
    }

    public function add()
    {
        try {
            $common = new CommonShopping();
            $res = $common->addLikeUser($this->user_id, $this->id);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function destroyLikeUser()
    {
        try {
            $common = new CommonShopping();
            $res = $common->destroyLikeUser($this->user_id, $this->id);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
