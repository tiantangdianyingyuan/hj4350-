<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\controllers\mall;


use app\plugins\Controller;
use app\plugins\integral_mall\forms\mall\IntegralMallEditForm;
use app\plugins\integral_mall\forms\mall\IntegralMallForm;

class SlideController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
