<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 9:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\jobs;


use app\models\OrderSubmitResult;
use app\plugins\community\forms\api\ApplyForm;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ApplyJob extends BaseObject implements JobInterface
{
    public $form;
    public $token;
    public $mall;
    public $user;
    public $appVersion;

    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var ApplyForm $form */
            $form = $this->form;
            $form->token = $this->token;
            $form->save();
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception->getMessage());
            \Yii::error($exception);
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $exception->getMessage();
            $orderSubmitResult->save();
            throw $exception;
        }
    }
}
