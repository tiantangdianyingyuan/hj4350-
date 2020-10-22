<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/12/3
 * Time: 16:25
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonPostageRules;
use app\forms\mall\postage_rules\PostageRulesEditForm;
use app\forms\mall\postage_rules\PostageRulesListForm;
use app\models\PostageRules;

class PostageRuleController extends MallController
{
    // 运费列表
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PostageRulesListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('index');
    }

    // 运费模板编辑
    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            $model = PostageRules::findOne([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'id' => $id
            ]);
            if (!$model) {
                $model = new PostageRules();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
            } else {
                $model->detail = $model->decodeDetail();
            }
            if (\Yii::$app->request->isPost) {
                $form = new PostageRulesEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model,
                    ]
                ]);
            }
        }
        return $this->render('edit');
    }

    // 设置运费默认
    public function actionStatus($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            return $this->asJson(CommonPostageRules::setStatus($id));
        } else {
            throw new \Exception('请求错误');
        }
    }

    public function actionDestroy($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            return $this->asJson(CommonPostageRules::deleteItem($id));
        } else {
            throw new \Exception('请求错误');
        }
    }

    public function actionAllList()
    {
        $form = new PostageRulesListForm();

        return $this->asJson($form->allList());
    }
}
