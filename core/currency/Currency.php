<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/30
 * Time: 16:28
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\core\currency;


use app\models\Share;
use app\models\User;
use yii\base\Component;
use yii\db\Exception;

/**
 * @property BalanceModel $balance;
 * @property IntegralModel $integral;
 * @property BrokerageModel $brokerage;
 * @property User $user;
 */
class Currency extends Component
{
    private $integral;
    private $balance;
    private $user;
    private $brokerage;


    /**
     * @return BalanceModel
     * @throws Exception
     */
    public function getBalance()
    {
        $form = new BalanceModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        return $form;
    }

    /**
     * @return IntegralModel
     * @throws Exception
     */
    public function getIntegral()
    {
        $form = new IntegralModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        return $form;
    }

    /**
     * @param $user
     * @return $this
     * @throws Exception
     */
    public function setUser($user)
    {
        if ($user instanceof User) {
            $this->user = $user;
        } else {
            throw new Exception('用户不存在');
        }
        return $this;
    }

    /**
     * @return User
     * @throws Exception
     */
    public function getUser()
    {
        if ($this->user instanceof User) {
            return $this->user;
        } else {
            throw new Exception('用户不存在');
        }
    }

    /**
     * @return BrokerageModel
     * @throws Exception
     */
    public function getBrokerage()
    {
        $form = new BrokerageModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        /* @var Share $share */
        $share = $this->user->share;
        if (!$share) {
            throw new Exception('指定用户不是分销商');
        }
        $form->share = $share;
        return $form;
    }
}
