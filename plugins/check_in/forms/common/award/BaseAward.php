<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/24
 * Time: 10:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common\award;


use app\models\User;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;
use app\plugins\check_in\models\CheckInSign;

/**
 * @property User $user
 * @property Common $common
 */
abstract class BaseAward extends Model
{
    public $user;
    public $common;
    public $day;
    public $status;
    public $token;

    /**
     * @return mixed
     * @throws \Exception
     * 校验
     */
    abstract public function check();

    /**
     * @throws \Exception
     * @return CheckInSign
     * 添加签到奖励
     */
    public function addSignIn()
    {
        $res = $this->check();
        if (!$res) {
            throw new \Exception('校验不通过x01');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $common = $this->common;
            $award = $common->getAwardByDay($this->status, $this->day);
            if (!$award) {
                throw new \Exception('错误的奖励信息');
            }
            $this->otherSave();
            $form = new CheckInSign();
            $form->mall_id = \Yii::$app->mall->id;
            $form->user_id = $this->user->id;
            $form->number = $award->number;
            $form->type = $award->type;
            $form->day = $this->day;
            $form->status = $this->status;
            $form->is_delete = 0;
            $form->token = $this->token;
            $form->award_id = $award->id;
            if (!$form->save()) {
                throw new \Exception($this->getErrorMsg($form));
            }
            switch ($form->type) {
                case 'integral':
                    \Yii::$app->currency->setUser($this->user)->integral
                        ->add(intval($form->number), '签到赠送积分' . $form->number);
                    break;
                case 'balance':
                    \Yii::$app->currency->setUser($this->user)->balance
                        ->add(floatval($form->number), '签到赠送余额' . $form->number . '元');
                    break;
                default:
                    throw new \Exception('错误的奖励类型');
            }
            $t->commit();
            return $form;
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     * 其他信息保存
     */
    public function otherSave()
    {
        return true;
    }
}
