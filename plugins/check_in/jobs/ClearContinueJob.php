<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 14:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\jobs;


use app\models\Mall;
use app\plugins\check_in\forms\common\Common;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ClearContinueJob extends BaseObject implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        try {
            \Yii::warning('执行清除计划');
            $this->mall = Mall::findOne($this->mall->id);
            \Yii::$app->setMall($this->mall);
            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            $continueTypeClass = $common->getContinueTypeClass($config->continue_type);
            $count = $continueTypeClass->clearContinue();
            $continueTypeClass->setJob();
        } catch (\Exception $exception) {
            \Yii::warning('执行清除计划--失败');
            \Yii::error($exception->getMessage());
        }
    }
}
