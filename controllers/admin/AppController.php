<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/20 17:17
 */


namespace app\controllers\admin;


class AppController extends AdminController
{
    public function actionRecycle()
    {
        return $this->render('recycle');
    }
}
