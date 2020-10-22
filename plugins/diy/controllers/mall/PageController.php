<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/16
 * Time: 9:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\controllers\mall;


use app\core\response\ApiCode;
use app\plugins\Controller;
use app\plugins\diy\forms\mall\AuthPageEForm;
use app\plugins\diy\forms\mall\InfoForm;
use app\plugins\diy\forms\mall\PageEditForm;
use app\plugins\diy\forms\mall\PageUpdateForm;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;
use yii\helpers\ArrayHelper;

class PageController extends Controller
{
    public function actionIndex($page = 0)
    {
        if (\Yii::$app->request->isAjax) {
            $query = DiyPage::find()->with(['navs.template'])->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            $list = $query->orderBy('created_at DESC, id DESC')->page($pagination)->all();
            $newList = [];
            /* @var DiyPage[] $list */
            foreach ($list as $page) {
                $newPage = $page->toArray();
                $newNavs = [];
                foreach ($page->navs as $navs) {
                    $newNavs[] = [
                        'navs' => $navs->name,
                        'navs_id' => $navs->id,
                        'template' => $navs->template->name,
                        'template_id' => $navs->template->id
                    ];
                }
                $newPage['navs'] = $newNavs;
                $newList[] = $newPage;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'pagination' => $pagination,
                    'list' => $newList,
                ],
            ];
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PageEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $model = DiyPage::find()
                    ->select('id,title,is_disable,show_navs')
                    ->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'id' => $id,
                        'is_delete' => 0,
                    ])->with(['navs' => function ($query) {
                        $query->alias('n')
                            ->select('n.name,n.template_id,n.page_id,t.name AS template_name')
                            ->leftJoin(['t' => DiyTemplate::tableName()], 'n.template_id=t.id');
                    }])->asArray()->one();
                if (!$model) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '内容不存在。',
                    ];
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $model,
                ];
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionUpdate()
    {
        if (\Yii::$app->request->isPost) {
            $form = new PageUpdateForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    public function actionAuth()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AuthPageEForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            } else {
                $form = new AuthPageEForm();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('auth');
        }
    }

    public function actionInfo()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new InfoForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new InfoForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('info');
            }
        }
    }

    public function actionInfoDel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new InfoForm();
            $form->id = \Yii::$app->request->post('id');
            return $this->asJson($form->delete());
        }
    }
}
