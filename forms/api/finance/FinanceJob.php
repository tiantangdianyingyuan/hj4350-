<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/1
 * Time: 14:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\finance;


use app\models\Mall;
use app\models\User;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class FinanceJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;
    public $appVersion;

    public $price;
    public $type;
    public $name;
    public $mobile;
    public $bank_name;

    public $setting;

    /** @var string $token */
    public $token;
    /** @var string $financeCashFormClass */
    public $financeCashFormClass;

    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);
        /** @var BaseFinanceCashForm $form */
        $form = new $this->financeCashFormClass();
        $form->setting = $this->setting;
        $form->token = $this->token;
        $form->attributes = [
            'price' => $this->price,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'bank_name' => $this->bank_name,
            'type' => $this->type,
        ];
        $form->job();
    }
}
