<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/8 18:11
 */


namespace app\controllers\mall;

class StatisticController extends MallController
{
    public function actionLottery()
    {
        return $this->render('lottery');
    }

    public function actionBargain()
    {
        return $this->render('bargain');
    }

    public function actionReport()
    {
        return $this->render('report');
    }

    public function actionDare()
    {
        return $this->render('dare');
    }

    public function actionStep()
    {
        return $this->render('step');
    }

    public function actionClerk()
    {
        return $this->render('clerk');
    }

    public function actionMiaosha()
    {
        return $this->render('miaosha');
    }

    public function actionPintuan()
    {
        return $this->render('pintuan');
    }

    public function actionBooking()
    {
        return $this->render('booking');
    }
}
