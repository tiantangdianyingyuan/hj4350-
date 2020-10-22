<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/7
 * Time: 9:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\common;


use app\models\Mall;
use app\models\User;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityMiddlemanActivity;
use app\plugins\community\models\CommunityRelations;
use app\plugins\community\models\CommunitySwitch;

/**
 * Class CommonMiddleman
 * @package app\plugins\community\forms\common
 * @property Mall $mall
 */
class CommonMiddleman extends Model
{
    /* @var self $instance */
    public static $instance;
    public $mall;

    public static function getCommon($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (self::$instance && self::$instance->mall->id = $mall->id) {
            return self::$instance;
        }
        self::$instance = new self();
        self::$instance->mall = $mall;
        return self::$instance;
    }

    /**
     * @param $user_id
     * @return array|\yii\db\ActiveRecord|null|CommunityMiddleman
     * 根据用户id查询团长数据
     */
    public function getConfig($user_id)
    {
        $model = CommunityMiddleman::find()->with('address')
            ->where(['user_id' => $user_id, 'mall_id' => $this->mall->id, 'is_delete' => 0])
            ->one();
        return $model;
    }

    /**
     * @param $token
     * @return array|\yii\db\ActiveRecord|null|CommunityMiddleman
     * 根据token查询团长数据
     */
    public function getConfigByToken($token)
    {
        $model = CommunityMiddleman::find()->with('address')
            ->where(['token' => $token, 'is_delete' => 0, 'mall_id' => $this->mall->id])
            ->one();
        return $model;
    }

    /**
     * @param User $user
     * @return array|\yii\db\ActiveRecord|null|CommunityAddress
     * 查询团长地址
     */
    public function getAddress($user)
    {
        $model = CommunityAddress::find()->where(['user_id' => $user->id])->one();
        return $model;
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null|CommunityMiddleman
     * 根据团长id查询团长数据
     */
    public function getConfigById($id)
    {
        $model = CommunityMiddleman::find()->with('address')
            ->where(['id' => $id, 'is_delete' => 0, 'mall_id' => $this->mall->id])
            ->one();
        return $model;
    }

    /**
     * @param CommunityMiddleman $middleman
     * @return array
     * 获取团长信息
     */
    public function getMiddleman($middleman)
    {
        if (!$middleman) {
            return [];
        }
        return [
            'id' => $middleman->id,
            'user_id' => $middleman->user_id,
            'name' => $middleman->name,
            'mobile' => $middleman->mobile,
            'apply_at' => $middleman->apply_at,
            'become_at' => $middleman->become_at,
            'status' => $middleman->status,
            'statusText' => $middleman->statusText,
            'reason' => $middleman->reason,
            'content' => $middleman->content,
            'money' => $middleman->money,
            'total_money' => $middleman->total_money,
            'total_price' => $middleman->total_price,
            'order_count' => $middleman->order_count,
            'pay_price' => $middleman->pay_price,
            'pay_time' => $middleman->pay_time,
            'location' => $middleman->address->location,
            'province' => $middleman->address->province,
            'latitude' => $middleman->address->latitude,
            'longitude' => $middleman->address->longitude,
            'city' => $middleman->address->city,
            'district' => $middleman->address->district,
            'province_id' => $middleman->address->province_id,
            'city_id' => $middleman->address->city_id,
            'district_id' => $middleman->address->district_id,
            'detail' => $middleman->address->detail,
            'nickname' => $middleman->user->nickname,
            'avatar' => $middleman->user->userInfo->avatar,
        ];
    }

    /**
     * @param $user_id
     * @return CommunityRelations|null
     * 获取指定用户的团长
     */
    public function getParent($user_id)
    {
        $relation = CommunityRelations::findOne(['user_id' => $user_id, 'is_delete' => 0]);
        return $relation;
    }

    /**
     * @param CommunityMiddleman $middleman
     * @param $user_id
     * @param bool $flag 是否允许变更
     * @throws \Exception
     * @return bool
     * 绑定团长
     */
    public function bindMiddleman($middleman, $user_id, $flag = false)
    {
        if (!$middleman) {
            throw new \Exception('团长不存在');
        }
        if ($middleman->status != 1) {
            throw new \Exception('团长不存在');
        }
        $setting = CommonSetting::getCommon()->getSetting();
        $relation = $this->getParent($user_id);
        if ($relation) {
            if (!$flag && $relation->middleman_id > 0) {
                throw new \Exception('不允许变更团长');
            }
            if ($setting['is_allow_change'] == 0 && $middleman->user_id != $relation->middleman_id && $relation->middleman_id > 0) {
                // 用户存在团长无需绑定
                throw new \Exception('用户已绑定团长且无法更换');
            }
        } else {
            $relation = new CommunityRelations();
            $relation->user_id = $user_id;
            $relation->is_delete = 0;
        }
        $relation->middleman_id = $middleman->user_id;
        if (!$relation->save()) {
            throw new \Exception($this->getErrorMsg($relation));
        }
        return true;
    }

    /**
     * @param CommunityMiddleman $middleman
     * @param $activityId
     * @return array
     */
    public function getNotJoin($middleman, $activityId)
    {
        $goodsIdList = CommunitySwitch::find()
            ->where(['middleman_id' => $middleman->user_id, 'activity_id' => $activityId, 'is_delete' => 0])
            ->select('goods_id')->column();
        return $goodsIdList;
    }

    /**
     * @param CommunityMiddleman $middleman
     * @param $activityId
     * @return false|string|null
     */
    public function getRemind($middleman, $activityId)
    {
        $remind = CommunityMiddlemanActivity::find()
            ->where(['middleman_id' => $middleman->user_id, 'activity_id' => $activityId, 'is_delete' => 0])
            ->select('is_remind')->scalar();
        return $remind;
    }

    /**
     * @param $longitude
     * @param $latitude
     * @return array|\yii\db\ActiveRecord|null
     * 根据经纬度获取距离最近的团长
     */
    public function getMiddlemanByDistance($longitude, $latitude)
    {
        $middleman = CommunityMiddleman::find()->alias('m')->with(['address', 'user'])
            ->where(['m.mall_id' => $this->mall->id, 'm.is_delete' => 0, 'm.status' => 1])
            ->leftJoin(['a' => CommunityAddress::tableName()], 'a.user_id=m.user_id')
            ->select(['m.*', "(st_distance(point(a.longitude, a.latitude), point($longitude, $latitude)) * 111195) as distance"])
            ->orderBy(['distance' => SORT_ASC, 'm.id' => SORT_ASC])
            ->one();
        return $middleman;
    }

    /**
     * @param $userId
     * @return int|mixed
     * 获取生成二维码的团长参数
     */
    public function getQrcodeMiddlemanId($userId)
    {
        $middlemanId = 0;
        $common = CommonMiddleman::getCommon();
        $relation = $common->getParent($userId);
        if ($relation) {
            $middlemanId = $relation->user_id;
        }
        return $middlemanId;
    }
}
