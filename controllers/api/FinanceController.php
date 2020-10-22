<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/30
 * Time: 15:05
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\finance\FinanceFactory;
use app\forms\api\finance\FinanceListForm;

class FinanceController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 提现配置
     */
    public function actionConfig()
    {
        $form = new FinanceFactory();
        return $this->asJson($form->config());
    }

    /**
     * 提现提交申请
     * @return \yii\web\Response
     */
    public function actionCash()
    {
        $form = new FinanceFactory();
        return $this->asJson($form->save());
    }

    /**
     * @return \yii\web\Response
     * 提现列表
     */
    public function actionList()
    {
        $form = new FinanceListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * @return \yii\web\Response
     * 提现申请结果
     */
    public function actionCashResult()
    {
        $form = new FinanceFactory();
        return $this->asJson($form->result());
    }
}
