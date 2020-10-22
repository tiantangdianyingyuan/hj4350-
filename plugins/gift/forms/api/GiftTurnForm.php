<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftUserOrder;

class GiftTurnForm extends Model
{

    public $id;
    public $turn_no;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['turn_no'], 'string'],
        ];
    }

    //转赠
    public function turn()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            if (!$this->id) {
                throw new \Exception('礼物转赠必须传入ID');
            }
            $user_gift = GiftUserOrder::findOne(['id' => $this->id, 'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id, 'is_turn' => 0, 'is_delete' => 0, 'is_receive' => 0, 'is_win' => 1]);
            if (empty($user_gift)) {
                throw new \Exception('当前礼物状态错误，无法转赠');
            }
            if (empty($user_gift->turn_no)) {
                $user_gift->turn_no = md5(time() . rand(1, 999999));
            }
            if (!$user_gift->save()) {
                throw new \Exception($user_gift->errors[0]);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'turn_no' => $user_gift->turn_no,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    //得到转赠
    public function get_turn()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->turn_no) {
                throw new \Exception('转赠码不能为空');
            }

            $user_gift = GiftUserOrder::findOne(['turn_no' => $this->turn_no, 'mall_id' => \Yii::$app->mall->id,
                'is_turn' => 0, 'is_delete' => 0, 'is_receive' => 0, 'is_win' => 1]);
            if (empty($user_gift)) {
                throw new \Exception('该礼物已被人领取');
            }
            if ($user_gift->user_id == \Yii::$app->user->id) {
                throw new \Exception('自己不能接收自己转赠的礼物');
            }
            $user_gift->is_turn = 1;
            $user_gift->turn_user_id = \Yii::$app->user->id;
            if (!$user_gift->save()) {
                throw new \Exception($user_gift->errors[0]);
            }
            $new_user_gift = new GiftUserOrder();
            $new_user_gift->mall_id = \Yii::$app->mall->id;
            $new_user_gift->user_id = \Yii::$app->user->id;
            $new_user_gift->gift_id = $user_gift->gift_id;
            $new_user_gift->is_turn = 0;
            $new_user_gift->is_win = 1;
            $new_user_gift->token = $user_gift->token;
            if (!$new_user_gift->save()) {
                throw new \Exception($new_user_gift->errors[0]);
            }
            $old_order = GiftOrder::findOne(['user_order_id' => $user_gift->id]);
            if (empty($old_order)) {
                throw new \Exception('转赠信息有误');
            }
            $old_order->user_order_id = $new_user_gift->id;
            if (!$old_order->save()) {
                throw new \Exception($old_order->errors[0]);
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '接收礼物成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '接收礼物失败,' . $exception->getMessage(),
                'error_msg' => $exception->getMessage()
            ];
        }
    }
}