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

class LikeGoodsEditForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer']
        ];
    }

    public function add()
    {
        try {
            $model = new ShoppingLikes();
            $model->mall_id = \Yii::$app->mall->id;
            $model->goods_id = $this->id;
            $res = $model->save();

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
}
