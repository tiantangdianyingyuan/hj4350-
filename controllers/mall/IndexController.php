<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/8 18:11
 */


namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\common\mch\MchMallSettingForm;
use app\forms\mall\index\MallForm;
use app\forms\mall\index\SettingForm;
use app\forms\mall\MailSettingForm;
use app\forms\mall\recharge\RechargeSettingForm;
use app\models\MailSetting;
use app\models\Mall;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\mch\models\MchMallSetting;

class IndexController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $userIdentity = UserIdentity::find()->where(['user_id' => \Yii::$app->user->id])->asArray()->one();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'mall' => \Yii::$app->mall,
                    'user_identity' => $userIdentity
                ],
            ]);
        } else {
            return $this->render('../data-statistics/index');
        }
    }

    public function actionSetting()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new MallForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new SettingForm();
                $data = \Yii::$app->serializer->decode(\Yii::$app->request->post('ruleForm'));
                $form->name = $data['name'];
                $form->attributes = $data['setting'];
                $recharge = new RechargeSettingForm();
                $recharge->attributes = $data['recharge'];
                $recharge->set();
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionSettingOne()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $column = \Yii::$app->request->get('column');
                $res = \Yii::$app->mall->getMallSettingOne($column);
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $res
                ]);
            }
        }
    }

    // 邮件管理
    public function actionMail()
    {
        if (\Yii::$app->request->isAjax) {
            $model = MailSetting::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => \Yii::$app->user->identity->mch_id
            ]);
            if (!$model) {
                $model = new MailSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
            }
            $model->show_type = \yii\helpers\BaseJson::decode($model->show_type) ?: ['attr' => 1, 'goods_no' => 0, 'form_data' => 0];
            if ($model->receive_mail) {
                $model->receive_mail = explode(',', $model->receive_mail);
            }
            if (!$model->receive_mail) {
                $model->receive_mail = [];
            }
            if (\Yii::$app->request->isPost) {
                $form = new MailSettingForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                return $this->asJson([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('mail');
    }

    public function actionHeaderBar()
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $form = new \app\forms\common\RoleSettingForm();
        $setting = $form->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mch_id' => $user->mch_id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'identity' => $user->identity,
                ],
                'mall' => [
                    'id' => \Yii::$app->mall->id,
                    'name' => \Yii::$app->mall->name,
                    'mall_logo_pic' => current((new Mall())->getMallSetting(['mall_logo_pic'])),
                ],
                'navs' => \Yii::$app->plugin->getHeaderNavs(),
                'update_password_status' => $setting['update_password_status']
            ],
        ];
    }

    public function actionMallPermissions()
    {
        $permissions = \Yii::$app->role->getAccountPermission();
        if (\Yii::$app->user->identity->mch_id) {
            /** @var MchMallSetting  $setting */
            $permissions = [];
            $setting = (new MchMallSettingForm())->search();
            if ($setting->is_share) {
                $permissions[] = 'share';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ];
    }

    public function actionNotice()
    {
        return $this->render('notice', [
            'mch_id' => \Yii::$app->mchId
        ]);
    }

    public function actionRule()
    {
        return $this->render('rule', [
            'mch_id' => \Yii::$app->mchId
        ]);
    }

    public function actionRole()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => \Yii::$app->role->name
        ];
    }
}
