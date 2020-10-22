<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\controllers\mall;

use app\plugins\Controller;

class OrderController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
