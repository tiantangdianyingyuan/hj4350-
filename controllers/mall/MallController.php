<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 18:18
 */


namespace app\controllers\mall;


use app\controllers\behaviors\LoginFilter;
use app\controllers\Controller;
use app\controllers\mall\filters\PermissionsBehavior;
use app\models\Mall;
use app\models\UserIdentity;

class MallController extends Controller
{
    public $layout = 'mall';

    public function init()
    {
        \Yii::$app->validateCloudFile();
        parent::init();
        if (property_exists(\Yii::$app, 'appIsRunning') === false) {
            exit('property not found.');
        }
        if (mb_stripos(\Yii::$app->requestedRoute, 'mall/plugin/') === 0) {
            return;
        }
        $this->loadMall();
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
            ],
            'permissions' => [
                'class' => PermissionsBehavior::class,
            ],
        ]);
    }

    /**
     * @return $this
     */
    private function loadMall()
    {
        $id = \Yii::$app->getSessionMallId();
        if (!$id) {
            $id = \Yii::$app->getMallId();
        }
        // 这是判断ID是因为 员工登录已经将mall_id 存储在session中。
        // 员工、多商户。记住我功能特殊处理、需把mall_id重新存回session
        if (!$id) {
            // 角色为员工时 存储
            $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
            if ($userIdentity && $userIdentity->is_operator == 1) {
                $id = \Yii::$app->user->identity->mall_id;
                \Yii::$app->setSessionMallId($id);
            }
        }

        // 角色为商户时 存储
        if (\Yii::$app->user->identity && \Yii::$app->user->identity->mch_id) {
            \Yii::$app->mchId = \Yii::$app->user->identity->mch_id;

            if (!$id) {
                $id = \Yii::$app->user->identity->mall_id;
                \Yii::$app->setSessionMallId($id);
            }
        }

        $url = \Yii::$app->branch->logoutUrl();
        if (!$id) {
            return $this->redirect($url)->send();
        }
        $mall = Mall::find()->where(['id' => $id, 'is_delete' => 0])->with('option')->one();
        if (!$mall) {
            return $this->redirect($url)->send();
        }
        if ($mall->is_delete !== 0 || $mall->is_recycle !== 0) {
            return $this->redirect($url)->send();
        }

        $newOptions = [];
        foreach ($mall['option'] as $item) {
            $newOptions[$item['key']] = $item['value'];
        }
        $mall->options = (object)$newOptions;

        \Yii::$app->mall = $mall;
        return $this;
    }
}
