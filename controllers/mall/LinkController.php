<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\PickLinkForm;
use app\models\User;

class LinkController extends MallController
{
    public function init()
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $userIdentity = $user->identity;
        if ($userIdentity->is_super_admin == 1 || $userIdentity->is_admin == 1) {
            $this->superAdminSetMallId();
        }
        parent::init();
    }

    /**
     * 获取小程序菜单 可跳转链接菜单
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new PickLinkForm();
        $ignore = \Yii::$app->request->get('ignore');
        // 暂时只有导航底栏
        if ($ignore && $ignore == PickLinkForm::IGNORE_NAVIGATE) {
            $model->ignore = $ignore;
        }
        $res = $model->getLink();
        return $res;
    }

    /**
     * 超级管理员可通过GET的mall_id参数设置当前商城ID
     */
    private function superAdminSetMallId()
    {
        $mallId = \Yii::$app->request->get('mall_id');
        if (!$mallId) {
            return;
        }
        \Yii::$app->setMallId($mallId);
    }
}
