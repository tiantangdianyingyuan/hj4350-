<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/23
 * Time: 14:21
 */

namespace app\forms\api\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShare;
use app\models\Model;
use app\models\Share;
use app\models\User;

class ShareBindForm extends Model
{
    public $parent_id;
    public $condition;

    public function rules()
    {
        return [
            [['parent_id', 'condition'], 'integer']
        ];
    }

    public function save()
    {
        /* @var User $user */
        $mall = \Yii::$app->mall;
        $user = \Yii::$app->user->identity;

        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonShare();
            $form->mall = $mall;
            $form->user = $user;
            $form->bindParent($this->parent_id, $this->condition);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '绑定成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
