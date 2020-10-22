<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\mall;

use app\core\response\ApiCode;
use app\forms\mall\finance\CashApplyForm;
use app\forms\mall\finance\FinanceForm;

class FinanceController extends MallController
{
    public function actionCash()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new FinanceForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson([
                'code' => 0,
                'data' => $form->search()
            ]);
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new FinanceForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            }
            return $this->render('cash');
        }
    }

    public function actionCashApply()
    {
        $form = new CashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionRemark()
    {
        $form = new CashApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remark());
    }

    public function actionPermission()
    {
        $form = new FinanceForm();
        $form->attributes = \Yii::$app->request->get();
        $list = $form->getPermission();
        $newList = [];
        foreach ($list as $item) {
            $newItem = [];
            $newItem['name'] = $item['name'];
            $newItem['key'] = $item['key'];
            $newList[] = $newItem;
        }
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $newList
        ]);
    }
}
