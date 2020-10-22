<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\PluginMchGoods;
use app\forms\common\mch\MchMallSettingForm;
use app\forms\mall\goods\CopyForm;
use app\forms\mall\goods\GoodsEditForm;
use app\forms\mall\goods\GoodsForm;
use app\forms\mall\goods\GoodsListForm;
use app\forms\mall\goods\ImportDataLogForm;
use app\forms\mall\goods\ImportGoodsForm;
use app\forms\mall\goods\ImportGoodsLogForm;
use app\forms\mall\goods\RecommendSettingForm;
use app\forms\mall\goods\TaobaoCsvForm;
use app\forms\mall\goods\TransferForm;
use app\plugins\mch\models\MchMallSetting;

class GoodsController extends MallController
{
    public function init()
    {
        \Yii::$app->validateCloudFile();

        /* 请勿删除下面代码↓↓￿↓↓￿ */
        if (method_exists(\Yii::$app, '。')) {
            $pass = \Yii::$app->。();
        } else {
            if (function_exists('usleep')) usleep(rand(100, 1000));
            $pass = false;
        }
        if (!$pass) {
            if (function_exists('sleep')) sleep(rand(30, 60));
            header('HTTP/1.1 504 Gateway Time-out');
            exit;
        }
        /* 请勿删除上面代码↑↑↑↑ */

        return parent::init();
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new GoodsListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->getList();

                return $this->asJson($res);
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new GoodsListForm();
                $form->flag = \Yii::$app->request->post('flag');
                $chooseList = \Yii::$app->request->post('choose_list');
                $form->choose_list = $chooseList ? explode(',', $chooseList) : [];
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
                $data = \Yii::$app->request->post();
                $form = new GoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attrGroups = json_decode($data['attrGroups'], true);
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $this->asJson($res);
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
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    // 商品上下架
    public function actionSwitchStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }

    // 加入快速购买
    public function actionSwitchQuickShop()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchQuickShop();

        return $this->asJson($res);
    }

    // 申请上架
    public function actionApplyStatus()
    {
        $form = new PluginMchGoods();
        $form->goods_id = \Yii::$app->request->post('id');
        $form->mch_id = \Yii::$app->user->identity->mch_id;

        return $this->asJson($form->applyStatus());
    }

    // 商品采集
    public function actionCollect()
    {
        $form = new CopyForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionTaobaoCsv()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TaobaoCsvForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('taobao-csv');
        }
    }

    public function actionEditSort()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editSort();

        return $this->asJson($res);
    }

    public function actionTransfer()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TransferForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->transfer());
            }
        } else {
            return $this->render('transfer');
        }
    }

    // 批量删除商品
    public function actionBatchDestroy()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchDestroy();

        return $this->asJson($res);
    }

    // 批量更新商品状态
    public function actionBatchUpdateStatus()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }

    // 批量更新快速购买
    public function actionBatchUpdateQuick()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateQuick();

        return $this->asJson($res);
    }

    // 批量更新商品面议
    public function actionBatchUpdateNegotiable()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateNegotiable();

        return $this->asJson($res);
    }

    // 批量设置运费
    public function actionBatchUpdateFreight()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateFreight();

        return $this->asJson($res);
    }

    // 批量设置包邮规则
    public function actionBatchUpdateFreeDelivery()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateFreeDelivery();

        return $this->asJson($res);
    }

    // 批量设置限购
    public function actionBatchUpdateConfineCount()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateConfineCount();

        return $this->asJson($res);
    }

    // 批量设置积分
    public function actionBatchUpdateIntegral()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateIntegral();

        return $this->asJson($res);
    }

    // 推荐商品设置
    public function actionRecommendSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new RecommendSettingForm();
                $form->data = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new RecommendSettingForm();
                $form->attributes = \Yii::$app->request->post();

                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('recommend-setting');
        }
    }

    // 推荐商品设置
    public function actionRecommendGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getRecommendGoodsList();

        return $this->asJson($res);
    }

    // 更新商品名称
    public function actionUpdateGoodsName()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updateGoodsName();

        return $this->asJson($res);
    }

    // TODO 移至 IndexController 此处即将废弃
    public function actionPermissions()
    {
        $permissions = \Yii::$app->role->getAccountPermission();
        if (\Yii::$app->user->identity->mch_id) {
            /** @var MchMallSetting $setting */
            $permissions = [];
            $setting = (new MchMallSettingForm())->search();
            if ($setting->is_share) {
                $permissions[] = 'share';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ];
    }

    public function actionBatchUpdateGoodsPrice()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateGoodsPrice();

        return $this->asJson($res);
    }

    public function actionBatchUpdateGoodsMember()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateGoodsMember();

        return $this->asJson($res);
    }

    public function actionImportData()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ImportGoodsForm();
            $form->attributes = \Yii::$app->request->post();
            $res = $form->save();

            return $this->asJson($res);
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new ImportDataLogForm();
                if (\Yii::$app->request->post('type') == 'first') {
                    $form->import();
                } else {
                    $form->importCat();
                }
                return false;
            } else {
                return $this->render('import-data');
            }
        }
    }

    public function actionImportGoodsLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ImportDataLogForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();

            return $this->asJson($res);
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new ImportDataLogForm();
                $form->import();
                return false;
            } else {
                return $this->render('import-goods-log');
            }

        }
    }

    public function actionExportGoodsList()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->post();
            $form->attributes = \Yii::$app->request->post('search');
            $form->flag = \Yii::$app->request->post('flag');
            $form->choose_list = \Yii::$app->request->post('choose_list');
            $form->ignore_type = ['ecard'];
            $res = $form->getList();
            return $this->asJson($res);
        }
    }

    public function actionUpdateSales()
    {
        $form = new GoodsForm();
        return $this->asJson($form->updateSales());
    }

    public function actionOtherPermission()
    {
        if (\Yii::$app->request->isGet) {
            $form = new GoodsEditForm();
            return $this->asJson($form->getPermission());
        }
    }
}
