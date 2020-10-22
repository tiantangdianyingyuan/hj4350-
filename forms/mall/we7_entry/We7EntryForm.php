<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/7/12
 * Time: 15:20
 */

namespace app\forms\mall\we7_entry;


use app\models\Model;
use app\models\AdminInfo;
use app\models\Mall;
use app\models\User;
use app\models\UserIdentity;
use app\models\We7App;

class We7EntryForm extends Model
{
    private $we7Branch = 'multiple';

    const WE7_BRANCH_SINGLE = 'single';
    const WE7_BRANCH_MULTIPLE = 'multiple';

    public function entry()
    {
        $we7User = \Yii::$app->session->get('we7_user');
        $we7Account = \Yii::$app->session->get('we7_account');
        if (!$we7User) {
            throw new \Exception('登录商城失败，无法获取当前账户信息。');
        }
        if (!$we7Account) {
            throw new \Exception('登录商城失败，无法获取当前应用信息。');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $adminInfo = AdminInfo::findOne([
                                                'we7_user_id' => $we7User['uid'],
                                                'is_delete' => 0,
                                            ]);
            if (!$adminInfo) {
                $adminInfo = $this->registerAdmin($we7User);
            }
            if (\Yii::$app->user->isGuest || \Yii::$app->user->getId() != $adminInfo->user->id) {
                \Yii::$app->user->login($adminInfo->user);
            }

            if ($this->we7Branch == static::WE7_BRANCH_SINGLE) {
                $this->deleteWe7Account();
                $we7App = We7App::find()->where(['acid' => $we7Account['acid'], 'is_delete' => 0])->one();
                if (!$we7App) {
                    $auth = $this->getAuthInfo();
                    if (
                    !(
                        isset($auth['host']['type'])
                        && isset($auth['host']['we7_branch'])
                        && isset($auth['host']['we7_app_num'])
                        && $auth['host']['type'] == 1
                        && $auth['host']['we7_branch'] == 1
                    )
                    ) {
                        throw new \Exception('授权信息错误');
                    }
                    $count = We7App::find()->where(['is_delete' => 0])->count();
                    if (($count >= $auth['host']['we7_app_num']) && ($auth['host']['we7_app_num'] != -1)) {
                        $this->appCountError($auth, $count);
                        $t->rollBack();
                        return false;
                    }
                }
            } elseif ($this->we7Branch == static::WE7_BRANCH_MULTIPLE) {
                $we7App = We7App::findOne([
                                              'acid' => $we7Account['acid'],
                                              'is_delete' => 0,
                                          ]);
            } else {
                throw new \Exception('版本错误。');
            }
            if (!$we7App) {
                $we7App = $this->createWe7App($we7Account);
            }
            $t->commit();
            \Yii::$app->setSessionMallId($we7App->mall_id);
            if (!$this->isLocalSettingSuccess()) {
                return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['mall/we7-entry/local-setting']));
            } else {
                return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['mall/index/index']));
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }

    public function logout()
    {
        \Yii::$app->user->logout();
        \Yii::$app->removeSessionMallId();
        $siteBaseUrl = mb_substr(\Yii::$app->request->url, 0, mb_stripos(\Yii::$app->request->url, '/addons/'));
        $we7AppListUrl = $siteBaseUrl . '/web/index.php?c=account&a=display&type=all';
        return \Yii::$app->response->redirect($we7AppListUrl);
    }

    /**
     * 检查本地配置是否已经配置
     * @return bool
     */
    private function isLocalSettingSuccess()
    {
        $localConfigFile = \Yii::$app->basePath . '/config/local.php';
        if (file_exists($localConfigFile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 微擎账户注册
     * @param $we7User
     * @return AdminInfo
     * @throws \yii\base\Exception
     */
    private function registerAdmin($we7User)
    {
        $baseModel = new Model();

        $user = new User();
        $user->mall_id = 0;
        $user->username = $we7User['username'];
        $user->password = \Yii::$app->security->generatePasswordHash(\Yii::$app->security->generateRandomString());
        $user->nickname = $we7User['name'];
        $user->auth_key = \Yii::$app->security->generateRandomString();
        $user->access_token = \Yii::$app->security->generateRandomString();
        if (!$user->save()) {
            throw new \Exception($baseModel->getErrorMsg($user));
        }

        $we7Founder = $this->getWe7SuperAdminId();
        $userIdentity = new UserIdentity();
        $userIdentity->user_id = $user->id;
        $userIdentity->is_super_admin = $we7User['uid'] == $we7Founder ? 1 : 0;
        $userIdentity->is_admin = $we7User['uid'] == $we7Founder ? 0 : 1;
        if (!$userIdentity->save()) {
            throw new \Exception($baseModel->getErrorMsg($userIdentity));
        }

        $adminInfo = new AdminInfo();
        $adminInfo->user_id = $user->id;
        $adminInfo->app_max_count = 0;
        $adminInfo->permissions = \Yii::$app->serializer->encode([]);
        $adminInfo->we7_user_id = $we7User['uid'];
        $adminInfo->is_default = 1;
        if (!$adminInfo->save()) {
            throw new \Exception($adminInfo);
        }
        return $adminInfo;
    }

    private function getWe7SuperAdminId()
    {
        $founder = we7_setting('founder');
        return $founder ?? 1;
    }

    /**
     * 创建微擎商城应用
     * @param $we7Account
     * @return We7App
     * @throws \Exception
     */
    private function createWe7App($we7Account)
    {
        $baseModel = new Model();
        $mall = new Mall();
        $mall->name = $we7Account['name'];
        $mall->user_id = \Yii::$app->user->id;
        if (!$mall->save()) {
            throw new \Exception($mall);
        }
        $we7App = new We7App();
        $we7App->mall_id = $mall->id;
        $we7App->acid = $we7Account['acid'];
        if (!$we7App->save()) {
            throw new \Exception($we7App);
        }
        return $we7App;
    }

    /**
     * 遍历微擎已删除应用，删除对应的商城
     * @return bool
     * @throws \yii\db\Exception
     */
    private function deleteWe7Account()
    {
        //需要删除的we7app列表
        $deleteList = [];
        $we7List = We7App::find()->where(['is_delete' => 0])->all();
        $checkAcidList = array_column($we7List, 'acid');
        if (empty($checkAcidList)) {
            return true;
        }
        $checkList = implode(",", $checkAcidList);
        $accountTableName = we7_table_name('account');
        $sql = "SELECT * FROM `{$accountTableName}` WHERE `acid` in ({$checkList})";
        $result = \Yii::$app->db->createCommand($sql)->queryAll();
        //微擎完全删除的应用是真删除，通过差集判断
        $trueWe7DeleteList = array_diff($checkAcidList, array_column($result, 'acid'));
        if ($trueWe7DeleteList) {
            $deleteList = array_merge($deleteList, $trueWe7DeleteList);
        }
        foreach ($result as $item) {
            if ($item['isdeleted'] == 1) {
                $deleteList[] = $item['acid'];
            }
        }
        if (empty($deleteList)) {
            return true;
        }

        $waitDelete = We7App::find()->where(['acid' => $deleteList])->all();
        Mall::updateAll(['is_delete' => 1,], ['id' => array_column($waitDelete, 'mall_id')]);
        We7App::updateAll(['is_delete' => 1], ['acid' => $deleteList]);
        return true;
    }

    /**
     * 获取授权平台授权信息
     * @return mixed
     */
    private function getAuthInfo()
    {
        $cacheKey = 'we7_self_operating_by_' . \Yii::$app->request->hostName;
        $cacheDuration = 600;
        $auth = \Yii::$app->cache->get($cacheKey);
        if ($auth) {
            return $auth;
        }
        $auth = \Yii::$app->cloud->auth->getAuthInfo();
        \Yii::$app->cache->set($cacheKey, $auth, $cacheDuration);
        return $auth;
    }

    private function appCountError($auth, $count)
    {
        $response = \Yii::$app->getResponse();
        $response->data = \Yii::$app->controller->renderFile('@app/views/mall/we7-entry/full.php', [
            'authorize_count' => $auth['host']['we7_app_num'],
            'count' => $count
        ]);
        $response->send();
        return false;
    }
}
