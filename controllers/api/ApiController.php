<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 18:19
 */


namespace app\controllers\api;


use app\controllers\api\filters\BlackListFilter;
use app\controllers\api\filters\MallDisabledFilter;
use app\controllers\Controller;
use app\forms\common\share\CommonShare;
use app\models\Formid;
use app\models\Mall;
use app\models\StatisticsDataLog;
use app\models\StatisticsUserLog;
use app\models\User;
use app\models\We7App;
use yii\web\NotFoundHttpException;

class ApiController extends Controller
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'disabled' => [
                'class' => MallDisabledFilter::class,
            ],
            'blackList' => [
                'class' => BlackListFilter::class
            ]
        ]);
    }

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        $this->setMall()->login()->saveFormIdList()->bindParent();
    }

    private function setMall()
    {
        $acid = \Yii::$app->request->get('_acid');
        if ($acid && $acid > 0) {
            $we7app = We7App::findOne([
                'acid' => $acid,
                'is_delete' => 0,
            ]);
            $mallId = $we7app ? $we7app->mall_id : null;
        } else {
            $mallId = \Yii::$app->request->get('_mall_id');
        }
        $mall = Mall::findOne([
            'id' => $mallId,
            'is_delete' => 0,
            'is_recycle' => 0,
        ]);
        if (!$mall) {
            throw new NotFoundHttpException('商城不存在，id = ' . $mallId);
        }
        \Yii::$app->setMall($mall);
        return $this;
    }

    private function login()
    {
        $headers = \Yii::$app->request->headers;
        $accessToken = empty($headers['x-access-token']) ? null : $headers['x-access-token'];

        //访问量记录
        $this->setVisits();

        if (!$accessToken) {
            return $this;
        }
        $user = User::findOne([
            'access_token' => $accessToken,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
//        \Yii::$app->setMall(Mall::findOne(2));
//        $user = User::findOne(311837);

        if ($user) {
            \Yii::$app->user->login($user);
            //访问人数记录
            $this->setUserLog();
        }
        return $this;
    }

    private function saveFormIdList()
    {
        if (\Yii::$app->user->isGuest) {
            return $this;
        }
        if (empty(\Yii::$app->request->headers['x-form-id-list'])) {
            return $this;
        }
        $rawData = \Yii::$app->request->headers['x-form-id-list'];
        $list = json_decode($rawData, true);
        if (!$list || !is_array($list) || !count($list)) {
            return $this;
        }
        foreach ($list as $item) {
            $formid = new Formid();
            $formid->user_id = \Yii::$app->user->id;
            $formid->form_id = $item['value'];
            $formid->remains = $item['remains'];
            $formid->expired_at = $item['expires_at'];
            $formid->save();
        }
        return $this;
    }

    private function bindParent()
    {
        if (\Yii::$app->user->isGuest) {
            return $this;
        }
        $headers = \Yii::$app->request->headers;
        $userId = empty($headers['x-user-id']) ? null : $headers['x-user-id'];
        if (!$userId) {
            return $this;
        }
        $common = CommonShare::getCommon();
        $common->mall = \Yii::$app->mall;
        $common->user = \Yii::$app->user->identity;
        try {
            $common->bindParent($userId, 1);
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            $userInfo = $common->user->userInfo;
            $userInfo->temp_parent_id = $userId;
            $userInfo->save();
        }
        return $this;
    }

    private $second = 5;//记录间隔秒

    //记录访问数
    private function setVisits()
    {
        $data_log = StatisticsDataLog::find()
            ->andWhere(['and', ['mall_id' => \Yii::$app->mall->id], ['like', 'created_at', date('Y-m-d')], ['key' => 'visits']])
            ->one();
        if (empty($data_log)) {
            $data_log = new StatisticsDataLog();
            $data_log->mall_id = \Yii::$app->mall->id;
            $data_log->key = 'visits';
            $data_log->value = 1;
            $data_log->time_stamp = time();
            $data_log->save();
        } elseif (bcsub(time(), $data_log->time_stamp) > $this->second) {
            $data_log->updateCounters(['value' => 1]);
            $data_log->time_stamp = time();
            $data_log->save();
        }
    }

    //记录访客数
    private function setUserLog()
    {
        $user_log = StatisticsUserLog::find()
            ->andWhere(['mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id, 'is_delete' => 0])
            ->andWhere(['like', 'created_at', date('Y-m-d')])
            ->one();
        if (empty($user_log)) {
            $user_log = new StatisticsUserLog();
            $user_log->mall_id = \Yii::$app->mall->id;
            $user_log->user_id = \Yii::$app->user->id;
            $user_log->num = 1;
            $user_log->time_stamp = time();
            $user_log->save();
        } elseif (bcsub(time(), $user_log->time_stamp) > $this->second) {
            $user_log->updateCounters(['num' => 1]);
            $user_log->time_stamp = time();
            $user_log->save();
        }
    }
}

