<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\AppShareForm;
use app\forms\common\CommonOption;
use app\forms\PickLinkForm;
use app\models\Option;

class PageController extends MallController
{
    public function actionShareSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new AppShareForm();
                $list = $form->links();

                $selectList = CommonOption::get(
                    Option::NAME_APP_SHARE_SETTING,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    []
                );

                $id = 0;
                foreach ($list as $key => $item) {
                    $id++;
                    $list[$key]['id'] = $id;
//                    $list[$key]['title'] = '';
//                    $list[$key]['pic_url'] = '';

                    foreach ($selectList as $sItem) {
                        if ($item['page_url'] == $sItem['page_url']) {
                            $list[$key]['title'] = $sItem['title'];
                            $list[$key]['pic_url'] = $sItem['pic_url'];
                        }
                    }
                }

                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'list' => $list
                    ]
                ]);
            } else {
                $value = \Yii::$app->request->post('list');
                $res = CommonOption::set(
                    Option::NAME_APP_SHARE_SETTING,
                    $value,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP
                );

                if (!$res) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '保存失败',
                    ];
                }

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }
        } else {
            return $this->render('share-setting');
        }
    }
}
