<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/9
 * Time: 17:28
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\data_importing;


use app\events\ShareMemberEvent;
use app\handlers\HandlerRegister;
use app\models\Share;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;

class UserImporting extends BaseImporting
{
    public static $idList = [];
    public static $isCheck = false;
    public static $checkResult = false;

    protected function checkClass()
    {
        if (!self::$isCheck) {
            $exists = User::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])->exists();
            if ($exists) {
                self::$checkResult = true;
            }
            self::$isCheck = true;
        }
        return self::$checkResult;
    }

    public function import()
    {
        if ($this->checkClass()) {
            throw new \Exception('商城中存在用户数据，无法导入');
        }
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }
        foreach ($this->v3Data as $datum) {
            $this->save($datum);
        }
        return true;
    }

    public function saveParentId()
    {
        foreach (self::$userInfo as $index => $tempParentId) {
            if ($tempParentId != 0) {
                try {
                    $parentId = $this->getV4Id($tempParentId);
                    $userInfo = UserInfo::findOne(['user_id' => $index]);
                    $userInfo->temp_parent_id = 0;
                    $userInfo->parent_id = $parentId;
                    $userInfo->junior_at = mysql_timestamp();
                    if (!$userInfo->save()) {
                        continue;
                    }
                    \Yii::$app->trigger(HandlerRegister::CHANGE_SHARE_MEMBER, new ShareMemberEvent([
                        'mall' => $this->mall,
                        'beforeParentId' => 0,
                        'parentId' => $parentId,
                        'userId' => $index
                    ]));
                    unset(self::$userInfo[$index]);
                } catch (\Exception $exception) {
                }
            }
        }
    }

    /* @var UserInfo[] $userInfo */
    public static $userInfo = [];

    /**
     * @param $datum
     * @return bool
     * @throws \Exception
     */
    private function save($datum)
    {
        if (!$this->checkData($datum)) {
            throw new \Exception('用户数据不完整，请重新检查文件');
        }
        foreach ($datum as &$value) {
            $value = $value === null ? '' : $value;
        }
        $user = new User();
        $user->mall_id = $this->mall->id;
        $user->access_token = $datum['access_token'];
        $user->auth_key = $datum['auth_key'];
        $user->username = $datum['username'];
        $user->nickname = $datum['nickname'];
        $user->password = $datum['password'];
        $user->mobile = $datum['binding'];
        if (!$user->save()) {
            throw new \Exception($this->getErrorMsg($user));
        }

        $parentId = 0;
        $tempParentId = $datum['parent_id'];
        try {
            $parentId = $this->getV4Id($tempParentId);
            $tempParentId = 0;
        } catch (\Exception $exception) {
        }
        $uInfo = new UserInfo();
        $uInfo->user_id = $user->id;
        $uInfo->avatar = $datum['avatar_url'];
        $uInfo->platform_user_id = $datum['wechat_open_id'];
        $uInfo->platform = $this->getPlatForm($datum['platform']);
        $uInfo->is_delete = 0;
        $uInfo->parent_id = $parentId;
        $uInfo->temp_parent_id = $tempParentId;
        $uInfo->integral = $datum['integral'];
        $uInfo->total_integral = $datum['total_integral'];
        $uInfo->balance = $datum['money'];
        $uInfo->total_balance = $datum['money'];
        $uInfo->is_blacklist = $datum['blacklist'];
        $uInfo->contact_way = $datum['contact_way'] ? $datum['contact_way'] : '';
        $uInfo->remark = $datum['comments'] ? $datum['comments'] : '';
        if (!$uInfo->save()) {
            throw new \Exception($this->getErrorMsg($uInfo));
        }
        if ($uInfo->temp_parent_id != 0) {
            self::$userInfo[$user->id] = $uInfo->temp_parent_id;
        }

        $userIdentity = new UserIdentity();
        $userIdentity->user_id = $user->id;
        $userIdentity->member_level = $this->getMemberLevel($datum['level']);
        $userIdentity->is_distributor = $datum['is_distributor'];

        if (!$userIdentity->save()) {
            throw new \Exception($this->getErrorMsg($userIdentity));
        }

        if ($datum['share']) {
            $share = new Share();
            $share->mall_id = $this->mall->id;
            $share->user_id = $user->id;
            $share->name = $datum['share']['name'];
            $share->mobile = $datum['share']['mobile'];
            $share->status = $datum['share']['status'];
            $share->money = $datum['price'];
            $share->total_money = $datum['total_price'];
            $share->is_delete = 0;
            $share->apply_at = mysql_timestamp();
            $share->become_at = mysql_timestamp();

            if (!$share->save()) {
                throw new \Exception($this->getErrorMsg($share));
            }
            $this->saveId($datum['id'], $user->id);
        }
        return true;
    }

    private function getPlatForm($platform)
    {
        if ($platform == 0) {
            return 'wxapp';
        } else {
            return 'aliapp';
        }
    }

    /**
     * @param $memberLevel
     * @return int
     */
    private function getMemberLevel($memberLevel)
    {
        if ($memberLevel == -1) {
            return 0;
        } else {
            return $memberLevel + 1;
        }
    }

    /**
     * @param $v3Id
     * @param $v4Id
     * @throws \Exception
     */
    private function saveId($v3Id, $v4Id)
    {
        if (isset(static::$idList[$v3Id])) {
            throw new \Exception('v3Id：' . $v3Id . '已经导入，请勿重复导入');
        }
        static::$idList[$v3Id] = $v4Id;
    }

    /**
     * @param $v3Id
     * @return mixed
     * @throws \Exception
     */
    private function getV4Id($v3Id)
    {
        if (isset(static::$idList[$v3Id])) {
            return static::$idList[$v3Id];
        } else {
            throw new \Exception('关联关系未导入');
        }
    }

    private function checkData($datum)
    {
        $required = ['access_token', 'auth_key', 'username', 'nickname', 'password', 'avatar_url',
            'wechat_open_id', 'platform'];
        foreach ($required as $item) {
            if (!isset($datum[$item])) {
                return false;
            }
        }
        return true;
    }
}
