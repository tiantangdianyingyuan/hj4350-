<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\stock\controllers\mall;


namespace app\plugins\region\controllers\mall;

use app\plugins\Controller;
use app\plugins\region\forms\mall\LevelForm;
use app\plugins\region\forms\mall\UpgradeConditionForm;

class LevelController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

}
