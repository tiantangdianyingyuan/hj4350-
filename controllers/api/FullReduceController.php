<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/16
 * Time: 11:53
 */

namespace app\controllers\api;

use app\forms\api\full_reduce\ActivityForm;

class FullReduceController extends ApiController
{
    public function actionIndex()
    {
        $form = new ActivityForm();
        return $this->asJson($form->getActivity());
    }
}
