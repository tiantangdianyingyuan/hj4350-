<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 17:22
 */

namespace app\plugins\flash_sale\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\flash_sale\forms\api\GoodsForm;
use app\plugins\flash_sale\forms\api\IndexForm;
use Yii;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'login' => [
                    'class' => LoginFilter::class,
                    'ignore' => ['goods-list', 'goods-detail']
                ],
            ]
        );
    }

    public function actionAddCart()
    {
        $form = new IndexForm();
        $form->attributes = Yii::$app->request->post();
        return $this->asJson($form->addCart());
    }

    public function actionGoodsList()
    {
        $form = new GoodsForm();
        $form->attributes = Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionGoodsDetail()
    {
        $form = new GoodsForm();
        $form->attributes = Yii::$app->request->get();
        return $this->asJson($form->detail());
    }
}
