<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\common;


use app\forms\common\version\Compatible;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchMallSetting;
use app\plugins\mch\models\MchSetting;

abstract class MchEditFormBase extends Model
{
    public $id;
    public $user_id;
    public $realname;
    public $wechat;
    public $mobile;
    public $address;
    public $mch_common_cat_id;
    public $name;
    public $logo;
    public $bg_pic_url;
    public $transfer_rate;
    public $sort;
    public $service_mobile;
    public $status;
    public $is_recommend;
    public $username;
    public $password;
    public $province_id;
    public $city_id;
    public $district_id;

    /**
     * @var Mch
     */
    public $mch;


    public function rules()
    {
        return [
            [['mch_common_cat_id', 'address', 'username', 'mobile', 'service_mobile', 'realname', 'name'], 'required'],
            [['user_id', 'mch_common_cat_id', 'transfer_rate', 'sort', 'id', 'status', 'is_recommend',
                'province_id', 'city_id', 'district_id'], 'integer'],
            [['mobile', 'logo', 'service_mobile', 'password'], 'string', 'max' => 255],
            [['realname', 'wechat', 'name', 'username', 'password'], 'string', 'max' => 65],
            [['bg_pic_url'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'transfer_rate' => '店铺手续费',
            'username' => '商户用户名',
            'password' => '商户密码',
            'mobile' => '联系电话',
            'service_mobile' => '店铺服务电话',
            'realname' => '联系人',
            'name' => '店铺名称'
        ];
    }

    public abstract function save();

    protected function getMch()
    {
        $mch = Mch::findOne(['id' => $this->id, 'is_delete' => 0]);
        if (!$mch) {
            throw new \Exception('商户不存在,ID:' . $this->id);
        }

        return $mch;
    }

    protected function setMch()
    {
        $mch = $this->getMch();
        $mch->user_id = $this->user_id ?: 0;
        $mch->realname = $this->realname;
        $mch->mobile = $this->mobile;
        $mch->mch_common_cat_id = $this->mch_common_cat_id;
        $mch->wechat = $this->wechat ?: '';
        $mch->transfer_rate = $this->transfer_rate ?: 0;
        $mch->sort = $this->sort ?: 100;
        $mch->status = $this->status ?: 0;
        $mch->is_recommend = $this->is_recommend ?: 0;

        $this->extraMchInfo($mch);
        $res = $mch->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($mch));
        }
        $this->mch = $mch;
    }

    /**
     * 额外需要保存的商户信息
     * @param Mch $mch
     * @return bool
     */
    protected function extraMchInfo($mch)
    {
        return true;
    }

    protected function setStore()
    {
        $store = Store::findOne(['mch_id' => $this->mch->id]);
        if (!$store) {
            $store = new Store();
            $store->mall_id = \Yii::$app->mall->id;
            $store->mch_id = $this->mch->id;
            $store->description = '欢迎来到' . $this->name;
            $store->scope = $this->name;
            $store->is_default = 1;
        }
        $store->name = $this->name;
        $store->address = $this->address;
        $store->cover_url = $this->logo ?: '/';

        try {
            // 小程序端和后台管理端数据不统一，所以要区分
            if (is_array($this->bg_pic_url)) {
                $store->pic_url = \Yii::$app->serializer->encode($this->bg_pic_url);
            } else if (is_string($this->bg_pic_url)) {
                $picUrl = [\Yii::$app->serializer->decode($this->bg_pic_url)];
                $store->pic_url = \Yii::$app->serializer->encode($picUrl);
            } else {
                $store->pic_url = \Yii::$app->serializer->encode([]);
            }
        } catch (\Exception $exception) {
            $store->pic_url = \Yii::$app->serializer->encode([]);
        }

        $store->mobile = $this->service_mobile;
        $store->province_id = $this->province_id;
        $store->city_id = $this->city_id;
        $store->district_id = $this->district_id;
        $res = $store->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($store));
        }
    }

    protected function setMallMchSetting()
    {
        // 多商户商城设置
        $mchMallSetting = MchMallSetting::findOne(['mch_id' => $this->mch->id]);
        if (!$mchMallSetting) {
            $mchMallSetting = new MchMallSetting();
            $mchMallSetting->mall_id = \Yii::$app->mall->id;
            $mchMallSetting->mch_id = $this->mch->id;
        }
        $res = $mchMallSetting->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($mchMallSetting));
        }
    }

    protected function setMchSetting()
    {
        // 多商户设置
        $mchSetting = MchSetting::findOne(['mch_id' => $this->mch->id]);
        if (!$mchSetting) {
            $mchSetting = new MchSetting();
            $mchSetting->mall_id = \Yii::$app->mall->id;
            $mchSetting->mch_id = $this->mch->id;
        }
        $sendType = Compatible::getInstance()->sendType($mchSetting->send_type);
        try {
            $sendType = \Yii::$app->serializer->encode($sendType);
        }catch (\Exception $exception) {
            $sendType = \Yii::$app->serializer->encode([]);
        }
        $mchSetting->send_type = $sendType;
        $res = $mchSetting->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($mchSetting));
        }
    }

    protected function setUser()
    {
        /** @var User $user */
        $user = User::find()->where([
            'username' => $this->username,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->andWhere(['!=', 'mch_id', 0])->one();
        // 商户编辑的时候无需判断
        if ($user && $user->mch_id != $this->id) {
            throw new \Exception('商户账号已存在！');
        }

        // 商户账号创建
        $user = User::findOne(['mch_id' => $this->mch->id]);
        if (!$user) {
            if (!$this->password) {
                throw new \Exception('请填写商户密码');
            }

            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $this->password) > 0) {
                throw new \Exception('密码不能包含中文字符');
            }

            $user = new User();
            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $user->mch_id = $this->mch->id;
            $user->mall_id = \Yii::$app->mall->id;
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->access_token = \Yii::$app->security->generateRandomString();
        }
        $user->nickname = $this->mch->realname;
        $user->mobile = $this->mch->mobile;
        $user->username = $this->username;
        $res = $user->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($user));
        }

        $userIdentity = UserIdentity::findOne(['user_id' => $user->id]);
        if (!$userIdentity) {
            $userIdentity = new UserIdentity();
            $userIdentity->user_id = $user->id;
            $res = $userIdentity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($userIdentity));
            }
        }
    }
}
