<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/21
 * Time: 10:27
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\events\ShareEvent;
use app\forms\common\share\CommonShare;
use app\forms\common\template\tplmsg\Tplmsg;
use app\handlers\HandlerRegister;
use app\models\Model;
use app\models\Share;
use app\models\User;
use app\models\UserIdentity;

class ApplyForm extends Model
{
    public $user_id;
    public $status;
    public $reason;

    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['reason'], 'trim'],
            [['reason'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /* @var User $user */
        $user = User::find()->with('share')
            ->where(['id' => $this->user_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();
        if (!$user) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '分销商不存在'
            ];
        }
        if (!$user->share) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '分销商不存在'
            ];
        }
        $key = 'shareApply' . $user->id;
        if (\Yii::$app->cache->get($key)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请不要重复提交'
            ];
        }
        \Yii::$app->cache->set($key, true);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $commonShare = CommonShare::getCommon();
            $commonShare->becomeShare($user, [
                'status' => $this->status,
                'reason' => $this->reason
            ]);
            $t->commit();
            \Yii::$app->cache->delete($key);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::$app->cache->delete($key);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
