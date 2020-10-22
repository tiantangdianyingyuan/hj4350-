<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 16:56
 */


namespace app\controllers;


use app\core\Pagination;
use app\core\response\ApiCode;
use app\models\Attachment;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class DemoController extends Controller
{
    public function actionIndex($testCode = 200)
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                return $this->asJson([
                    'code' => 0,
                    'msg' => 'AJAX POST REQUEST.',
                ]);
            } else {
                switch ($testCode) {
                    case 200:
                        return $this->asJson([
                            'code' => 0,
                            'msg' => 'AJAX GET REQUEST.',
                        ]);
                        break;
                    case 400:
                        throw new BadRequestHttpException();
                        break;
                    case 403:
                        throw new ForbiddenHttpException();
                        break;
                    case 404:
                        throw new NotFoundHttpException();
                        break;
                    case 500:
                        throw new ServerErrorHttpException();
                        break;
                    case 502:
                        throw new HttpException(502);
                        break;
                    default:
                        \Yii::warning('测试提交');
                        break;
                }
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $pagination = new Pagination(['totalCount' => 200]);
            $list = [];
            for ($i = $pagination->offset + 1; $i < $pagination->offset + 11; $i++) {
                $list[] = [
                    'id' => $i,
                    'name' => "数据{$i}",
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                ],
            ];
        } else {
            return $this->render('list');
        }
    }

    public function actionForm()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $singlePic = Attachment::find()->limit(1)->asArray()->one();
                $multiplePic = Attachment::find()->limit(5)->asArray()->all();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'name' => 'Hello',
                        'single_pic' => $singlePic,
                        'multiple_pic' => $multiplePic,
                    ],
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '操作成功。',
                ];
            }
        } else {
            return $this->render('form');
        }
    }
}
