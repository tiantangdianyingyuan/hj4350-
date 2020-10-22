<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;

use app\core\response\ApiCode;
use app\forms\mall\user\BalanceForm;
use app\forms\mall\user\ClerkEditForm;
use app\forms\mall\user\ClerkForm;
use app\forms\mall\user\IntegralForm;
use app\forms\mall\user\LevelForm;
use app\forms\mall\user\UserCardForm;
use app\forms\mall\user\UserEditForm;
use app\forms\mall\user\UserForm;
use app\models\User;

class UserController extends MallController
{
    public function init()
    {
        /* 请勿删除下面代码↓↓￿↓↓￿ */
        if (method_exists(\Yii::$app, '。')) {
            $pass = \Yii::$app->。();
        } else {
            if (function_exists('usleep')) {
                usleep(rand(100, 1000));
            }

            $pass = false;
        }
        if (!$pass) {
            if (function_exists('sleep')) {
                sleep(rand(30, 60));
            }

            header('HTTP/1.1 504 Gateway Time-out');
            exit;
        }
        /* 请勿删除上面代码↑↑↑↑ */
        return parent::init();
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UserEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionCouponDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->destroy();
        }
    }

    public function actionHandle()
    {
        return $this->render('handle');
    }

    public function actionCoupon()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getCoupon());
        } else {
            return $this->render('coupon');
        }
    }

    //余额积分
    public function actionBalance()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BalanceForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    public function actionBalanceLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->balanceLog());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->balanceLog();
                return false;
            } else {
                return $this->render('balance-log');
            }
        }
    }

    public function actionIntegral()
    {
        if (\Yii::$app->request->isPost) {
            $form = new IntegralForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionIntegralLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->integralLog());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->integralLog();
                return false;
            } else {
                return $this->render('integral-log');
            }
        }
    }

    public function actionShareUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->shareUser());
        }
    }

    //核销员
    public function actionClerk()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {

            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new ClerkForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('clerk');
            }
        }
    }

    public function actionClerkEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ClerkEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionClerkDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    public function actionClerkUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->clerkUser());
        }
    }

    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchUser());
        }
    }

    //会员购买记录
    public function actionLevelLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LevelForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new LevelForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('level-log');
            }
        }
    }

    public function actionCard()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new UserCardForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getCard());
            }
        } else {
            return $this->render('card');
        }
    }

    public function actionCardDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserCardForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    public function actionCardBatchDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserCardForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchDestroy());
        }
    }

    public function actionLogout()
    {
        $logout = \Yii::$app->user->logout();
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '注销成功',
        ]);
    }

    public function actionIntegralSetting()
    {
        return $this->render('integral-setting');
    }

    public function actionBatchUpdateMemberLevel()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateMemberLevel();

        return $this->asJson($res);
    }

    public function actionBatchUpdateIntegral()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateIntegral();

        return $this->asJson($res);
    }

    public function actionBatchUpdateBalance()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateBalance();

        return $this->asJson($res);
    }
}
