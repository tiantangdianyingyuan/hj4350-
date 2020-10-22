<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/29
 * Time: 11:59
 */

namespace app\controllers\api\admin;

use app\forms\mall\goods\GoodsForm;
use app\forms\mall\goods\GoodsEditForm;

class GoodsController extends AdminController
{
    /**
     * 获取商品列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        /** {"keyword":"","status":""} */
        $form->attributes = \Yii::$app->request->get('search');
        $res = $form->getList();

        return $this->asJson($res);
    }


    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $form = new GoodsEditForm();
            $data = json_decode($post['form'], true);
            $form->attributes = $data;

//            $group = $data['attr'];
//            $group = array_column($group, 'attr_list');

//            $result = array_reduce($group, function ($result, $value) {
//                return array_merge($result, array_values($value));
//            }, array());

//            $res = array_values(array_unique($result, SORT_REGULAR));

//            $attrs = array_column($res, null, 'attr_group_id');
//            foreach ($attrs as $k => $v) {
//                unset($attrs[$k]['attr_id']);
//                unset($attrs[$k]['attr_name']);
//                foreach ($res as $value) {
//                    if ($k == $value['attr_group_id']) {
//                        $temp['attr_id'] = $value['attr_id'];
//                        $temp['attr_name'] = $value['attr_name'];
//                        $attrs[$k]['attr_list'][] = $temp;
//                    }
//                }
//            }

//            $form->attrGroups = array_values($attrs);
            $form->attrGroups = $data['use_attr'] ? $data['attr_groups'] : [];
            $form->use_attr = $data['use_attr'];
            $res = $form->save();

            return $this->asJson($res);
        } else {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getDetail();

            return $this->asJson($res);
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

    public function actionGoodsConfig()
    {
        $form = new GoodsEditForm();
        return $this->asJson($form->getPermission());
    }
}
