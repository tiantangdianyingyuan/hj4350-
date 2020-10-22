<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 17:04
 */

namespace app\plugins\diy\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\behaviors\LoginFilter;
use app\core\response\ApiCode;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\forms\api\InfoForm;

class PageController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'safeActions' => ['detail']
            ]
        ]);
    }

    public function actionDetail($id)
    {
        $page = DiyPage::find()->select('id,title,show_navs')
            ->where([
                'id' => $id,
                'mall_id' => \Yii::$app->mall->id,
                'is_disable' => 0,
                'is_delete' => 0,
            ])->with(['navs' => function ($query) {
                $query->select('id,name,page_id,template_id')->with(['template' => function ($query) {
                    $query->select('id,name,data');
                }]);
            }])->asArray()->one();
        if (!$page) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '页面不存在。',
            ];
        }
        if (!empty($page['navs'])) {
            foreach ($page['navs'] as &$nav) {
                if (!empty($nav['template']['data'])) {
                    $nav['template']['data'] = json_decode($nav['template']['data']);
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $page,
        ];
    }

    public function actionStore()
    {
        $form = new InfoForm();
        $form->form_data = json_decode(\Yii::$app->request->post('form_data'), true);
        return $this->asJson($form->save());
    }
}
