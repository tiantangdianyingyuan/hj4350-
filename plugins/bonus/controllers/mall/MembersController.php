<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/30
 * Time: 16:54
 */

namespace app\plugins\bonus\controllers\mall;

use app\plugins\bonus\forms\mall\MemberEditForm;
use app\plugins\bonus\forms\mall\MemberForm;
use app\plugins\bonus\models\BonusMembers;
use app\plugins\Controller;

class MembersController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new MemberForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();
                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MemberEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new MemberForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new MemberForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    /**
     * 获取会员等级列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new MemberForm();
        $res = $form->getOptionList();
        return $this->asJson($res);
    }

    /**
     * 获取所有会员
     * @return \yii\web\Response
     */
    public function actionAllMember()
    {
        $form = new MemberForm();
        $res = $form->getAllMember();
        return $this->asJson($res);
    }

    public function actionSwitchStatus()
    {
        $form = new MemberForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->switchStatus());
    }
}