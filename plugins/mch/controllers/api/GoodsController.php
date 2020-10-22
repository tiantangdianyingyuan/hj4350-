<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\PluginMchGoods;
use app\forms\mall\card\CardForm;
use app\forms\mall\cat\CatForm;
use app\forms\mall\postage_rules\PostageRulesListForm;
use app\forms\mall\service\ServiceForm;
use app\plugins\mch\controllers\api\filter\MchLoginFilter;
use app\plugins\mch\forms\api\GoodsEditForm;
use app\plugins\mch\forms\api\GoodsForm;

class GoodsController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['cat-style', 'index', 'detail']
            ],
            'mchLogin' => [
                'class' => MchLoginFilter::class,
                'ignore' => [
                    'plugin/mch/api/goods/cat-style',
                    'plugin/mch/api/goods/index',
                    'plugin/mch/api/goods/detail',
                    'plugin/mch/api/order/preview',
                    'plugin/mch/api/order/submit',
                ]
            ]
        ]);
    }

    public function actionIndex()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionCatStyle()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getCatStyle());
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new GoodsEditForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    // 商品上下架
    public function actionSwitchStatus()
    {
        $form = new GoodsEditForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }

    // 申请上架
    public function actionApplyStatus()
    {
        $form = new PluginMchGoods();
        $form->goods_id = \Yii::$app->request->post('id');
        $form->mch_id = \Yii::$app->request->post('mch_id');

        return $this->asJson($form->applyStatus());
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
            $form = new \app\forms\mall\goods\GoodsEditForm;
            $data = json_decode($data['form'], true);
            $form->attributes = $data;
            $form->attrGroups = $data['use_attr'] ? $data['attr_groups'] : [];
            $form->use_attr = $data['use_attr'];
            $res = $form->save();

            return $this->asJson($res);
        } else {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->show();

            return $this->asJson($res);
        }
    }

    /**
     * 获取商品服务列表
     * @return \yii\web\Response
     */
    public function actionServices()
    {
        $form = new ServiceForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    /**
     * 获取商品分类列表
     * @return \yii\web\Response
     */
    public function actionCats()
    {
        $form = new CatForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    /**
     * 获取多商户商品分类列表
     * @return \yii\web\Response
     */
    public function actionMchCats()
    {
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    // 运费规则
    public function actionRules()
    {
        $form = new PostageRulesListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->allList());
    }
}
