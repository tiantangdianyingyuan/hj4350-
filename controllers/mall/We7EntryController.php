<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/8
 * Time: 15:50
 */

namespace app\controllers\mall;


use app\controllers\Controller;
use app\core\response\ApiCode;
use app\forms\install\RedisSettingForm;
use app\jobs\TestQueueServiceJob;
use app\models\User;
use yii\web\ForbiddenHttpException;

class We7EntryController extends Controller
{

    public function actionLogin()
    {
        return \Yii::$app->createForm('app\forms\mall\we7_entry\We7EntryForm')->entry();
    }

    public function actionLogout()
    {
        return \Yii::$app->createForm('app\forms\mall\we7_entry\We7EntryForm')->logout();
    }

    public function actionLocalSetting($action = null, $testQueueStep = null)
    {
        if (\Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('请先登录后操作');
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        if ($user->identity->is_super_admin != 1) {
            throw new ForbiddenHttpException('请使用超级管理员登录设置系统配置');
        }
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                switch ($action) {
                    case 'saveConfig':
                        $form = new RedisSettingForm();
                        $form->attributes = \Yii::$app->request->post();
                        return $form->saveSetting();
                        break;
                    case 'testQueue':
                        if ($testQueueStep == 'create') {
                            try {
                                $id = \Yii::$app->queue->delay(0)->push(new TestQueueServiceJob());
                            } catch (\Exception $exception) {
                                return [
                                    'code' => ApiCode::CODE_ERROR,
                                    'msg' => '队列服务测试失败，请检查Redis配置是否正确或检查Redis服务器是否有访问权限。',
                                ];
                            }
                            if (!$id) {
                                return [
                                    'code' => ApiCode::CODE_ERROR,
                                    'msg' => '队列服务测试失败，请检查Redis配置是否正确或检查Redis服务器是否有访问权限。',
                                ];
                            }
                            return [
                                'code' => ApiCode::CODE_SUCCESS,
                                'data' => [
                                    'id' => $id,
                                ],
                            ];
                        }
                        if ($testQueueStep == 'test') {
                            $id = \Yii::$app->request->post('id');
                            if (\Yii::$app->queue->isDone($id)) {
                                return [
                                    'code' => ApiCode::CODE_SUCCESS,
                                    'data' => [
                                        'done' => true,
                                    ],
                                ];
                            } else {
                                return [
                                    'code' => ApiCode::CODE_SUCCESS,
                                    'data' => [
                                        'done' => false,
                                    ],
                                ];
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        } else {
            return $this->render('local-setting');
        }
    }
}
