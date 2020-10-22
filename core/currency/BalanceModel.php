<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/30
 * Time: 16:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\core\currency;


use app\forms\common\template\TemplateSend;
use app\forms\common\template\tplmsg\AccountChange;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\BalanceLog;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class BalanceModel extends Model implements BaseCurrency
{
    public $mall;
    public $user;

    /**
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function add($price, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        /* @var UserInfo $userInfo */
        $userInfo = $this->user->userInfo;
        $t = \Yii::$app->db->beginTransaction();
        $userInfo->balance += $price;
        $userInfo->total_balance += $price;
        if ($userInfo->save()) {
            try {
                $this->createLog(1, $price, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($userInfo), $userInfo->errors, 1);
        }
    }

    /**
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function sub($price, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        if ($this->user->userInfo->balance < $price) {
            throw new Exception('用户余额不足');
        }
        /* @var UserInfo $userInfo */
        $userInfo = $this->user->userInfo;
        $t = \Yii::$app->db->beginTransaction();
        $userInfo->balance -= $price;
        if ($userInfo->save()) {
            try {
                $this->createLog(2, $price, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($userInfo), $userInfo->errors, 1);
        }
    }

    /**
     * 余额查询
     * @return mixed
     */
    public function select()
    {
        return round($this->user->userInfo->balance, 2);
    }

    /**
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function refund($price, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        /* @var UserInfo $userInfo */
        $userInfo = $this->user->userInfo;
        $t = \Yii::$app->db->beginTransaction();
        $userInfo->balance += $price;
        if ($userInfo->save()) {
            try {
                $this->createLog(1, $price, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($userInfo), $userInfo->errors, 1);
        }
    }

    /**
     * @param $type
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws \Exception
     */
    private function createLog($type, $price, $desc, $customDesc, $orderNo)
    {
        if ($price == 0) {
            \Yii::warning('余额为' . $price . '不记录日志');
            return true;
        }
        if (!$customDesc) {
            $customDesc = \Yii::$app->serializer->encode(['msg' => '用户余额变动说明']);
        }
        $form = new BalanceLog();
        $form->mall_id = $this->user->mall_id;
        $form->user_id = $this->user->id;
        $form->type = $type;
        $form->money = $price;
        $form->desc = $desc;
        $form->custom_desc = $customDesc;
        $form->order_no = $orderNo;
        if ($form->save()) {
            return true;
        } else {
            throw new \Exception($this->getErrorMsg($form), $form->errors, 1);
        }
    }
}
