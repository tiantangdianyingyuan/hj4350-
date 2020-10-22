<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14
 * Time: 11:51
 */

namespace app\controllers\mall;

use app\core\response\ApiCode;
use app\forms\common\share\CommonShareLevel;
use app\forms\mall\share\ApplyForm;
use app\forms\mall\share\BasicForm;
use app\forms\mall\share\CashApplyForm;
use app\forms\mall\share\CashListForm;
use app\forms\mall\share\ContentForm;
use app\forms\mall\share\EditForm;
use app\forms\mall\share\IndexForm;
use app\forms\mall\share\LevelEditForm;
use app\forms\mall\share\LevelForm;
use app\forms\mall\share\OrderForm;
use app\forms\mall\share\ShareCustomForm;
use app\forms\mall\share\ShareForm;
use app\forms\mall\share\TeamForm;
use app\models\ShareSetting;

class ShareController extends MallController
{
    // 分销商列表
    public function actionIndex()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $fields = explode(',', \Yii::$app->request->post('fields'));
            $form = new IndexForm();
            $form->attributes = \Yii::$app->request->post();
            $form->fields = $fields;
            $form->getList();
            return false;
        } else {
            return $this->render('index');
        }
    }

    // 分销订单列表
    public function actionOrder()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                $post = \Yii::$app->request->post();
                if (isset($post['keyword_1']) && isset($post['keyword'])) {
                    switch ($post['keyword_1']) {
                        case 'order_no':
                            $form->order_no = $post['keyword'];
                            break;
                        case 'nickname':
                            $form->nickname = $post['keyword'];
                            break;
                        case 'user_id':
                            $form->user_id = $post['keyword'];
                            break;
                        case 'goods_name':
                            $form->goods_name = $post['keyword'];
                            break;
                    }
                }
            } else {
                $get = \Yii::$app->request->get();
                $form->attributes = $get;

                if (isset($get['keyword_1']) && isset($get['keyword'])) {
                    $keyword1 = $get['keyword_1'];
                    $form->$keyword1 = $get['keyword'];
                }
            }
            $form->mall = \Yii::$app->mall;
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $post = \Yii::$app->request->post();
                $form->attributes = $post;
                if (isset($post['keyword_1']) && isset($post['keyword'])) {
                    $keyword1 = $post['keyword_1'];
                    $form->$keyword1 = $post['keyword'];
                }
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('order');
            }
        }
    }

    // 分销商列表数据获取
    public function actionIndexData()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    // 分销基础设置
    public function actionBasic()
    {
        $mall = \Yii::$app->mall;
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BasicForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new BasicForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('basic');
    }

    // 分销申请审核
    public function actionApply()
    {
        $form = new ApplyForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    // 分销商删除
    public function actionDelete()
    {
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->delete());
    }

    // 分销商团队详情
    public function actionTeam()
    {
        $form = new TeamForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    // 添加分销商备注
    public function actionContent()
    {
        $form = new ContentForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    public function actionCustomize()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShareCustomForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->saveData());
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getData());
            }
        } else {
            return $this->render('customize');
        }
    }

    // 提现列表页面
    public function actionCash()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $fields = explode(',', \Yii::$app->request->post('fields'));
            $form = new CashListForm();
            $form->attributes = \Yii::$app->request->post();
            $form->attributes = \Yii::$app->request->get();
            $form->fields = $fields;
            $form->search();
            return false;
        } else {
            return $this->render('cash');
        }
    }

    // 提现列表数据
    public function actionCashData()
    {
        $form = new CashListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionCashApply()
    {
        $form = new CashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // TODO 无作用
    public function actionOrderData()
    {
        $form = new OrderForm();
        $get = \Yii::$app->request->get();
        $form->attributes = $get;
        if (isset($get['keyword_1']) && isset($get['keyword'])) {
            $keyword1 = $get['keyword_1'];
            $form->$keyword1 = $get['keyword'];
        }
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    public function actionQrcode()
    {
        $form = new ShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getQrcode());
    }

    // 分销等级
    public function actionLevel()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new LevelForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            } else {
                return $this->asJson([]);
            }
        } else {
            return $this->render('level');
        }
    }

    // 分销等级编辑
    public function actionLevelEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => [
                        'detail' => CommonShareLevel::getInstance()->getDetail(\Yii::$app->request->get('id')),
                    ],
                ]);
            } else {
                $form = new LevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('level-edit');
        }
    }

    public function actionOptions()
    {
        $level = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => CommonShareLevel::getInstance()->getOptions(),
                'level' => $level,
            ],
        ]);
    }

    public function actionLevelDestroy()
    {
        try {
            CommonShareLevel::getInstance()->destroy(\Yii::$app->request->post('id'));
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ]);
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ]);
        }
    }

    public function actionSwitchStatus()
    {
        try {
            CommonShareLevel::getInstance()->switchStatus(\Yii::$app->request->post('id'));
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '分销等级状态变更成功',
            ]);
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ]);
        }
    }

    public function actionGoodsShareConfig()
    {
        $form = new BasicForm();
        return $this->asJson($form->getGoodsShareConfig());
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('edit');
        }
    }

    public function actionUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }

    public function actionGetLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getLevel());
        }
    }

    public function actionChangeLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }

    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new EditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }
}
