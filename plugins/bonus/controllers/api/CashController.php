<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:14
 */

namespace app\plugins\bonus\controllers\api;

use app\core\response\ApiCode;
use app\plugins\bonus\forms\mall\CashListForm;

class CashController extends ApiController
{
    public function actionIndex()
    {
        $form = new CashListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->search_type = 4;
        $form->keyword = \Yii::$app->user->id;

        $res = $form->search();
        $list = $res['data']['list'];
        $newList = [];
        foreach ($list as $item) {
            $time = date('Y-m-d', strtotime($item['time']['created_at']));
            if (isset($newList[$time])) {
                $newList[$time]['list'][] = $item;
            } else {
                $newItem = [
                    'date' => date('m月d日', strtotime($time)),
                    'list' => [
                        $item
                    ]
                ];
                $newList[$time] = $newItem;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => array_values($newList)
            ]
        ];
    }
}