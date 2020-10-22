<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/30
 * Time: 13:56
 */

namespace app\controllers\api\admin;

use app\forms\mall\mall_member\MallMemberForm;
use app\forms\mall\store\StoreForm;
use app\forms\mall\user\ClerkEditForm;
use app\forms\mall\user\ClerkForm;
use app\forms\mall\user\UserEditForm;
use app\forms\mall\user\UserForm;
use app\forms\mall\user\IntegralForm;
use app\forms\mall\user\BalanceForm;

class UserController extends AdminController
{
    public function actionIndex()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionIntegral()
    {
        if (\Yii::$app->request->isPost) {
            $form = new IntegralForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionBalance()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BalanceForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    //核销员
    public function actionClerk()
    {
        $form = new ClerkForm();
        $form->attributes = \Yii::$app->request->get();
        $sortType = \Yii::$app->request->get('sort');
        switch ($sortType) {
            case 1:
                $form->order_sort = SORT_DESC;
                break;
            case 2:
                $form->order_sort = SORT_ASC;
                break;
            case 3:
                $form->sum_sort = SORT_DESC;
                break;
            case 4:
                $form->sum_sort = SORT_ASC;
                break;
            case 5:
                $form->card_sort = SORT_DESC;
                break;
            case 6:
                $form->card_sort = SORT_ASC;
                break;
            default:

        }
        return $this->asJson($form->search());
    }

    public function actionClerkDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ClerkForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    public function actionStore()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreForm();
            $form->attributes = \Yii::$app->request->get();
            $list = $form->getList();
            return $this->asJson($list);
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

    public function actionUpdateUserLevel()
    {
        $form = new UserEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updateUserLevel());
    }

    public function actionAllMember() {
        $form = new  MallMemberForm();
        $res = $form->getAllMember();

        return $this->asJson($res);
    }

    public function actionUpdateUserRemark()
    {
        $form = new UserEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updateUserRemark());
    }

    public function actionUpdateUserRemarkName()
    {
        $form = new UserEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updateUserRemarkName());
    }
}