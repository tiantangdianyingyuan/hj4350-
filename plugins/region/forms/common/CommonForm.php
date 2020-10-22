<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019年12月14日 10:53:47
 * Time: 18:39
 */

namespace app\plugins\region\forms\common;

use app\models\DistrictArr;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionAreaDetail;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionSetting;
use app\plugins\region\models\RegionUser;

class CommonForm extends Model
{
    public static $members = [];
    public static $setting;

    //下级
    public static function allMembers($user_id)
    {
        self::$members[] = $user_id;
        $users = UserInfo::find()->alias('u')->where(['u.parent_id' => $user_id,])->with(['identity'])->all();
        $sum = count($users);
        foreach ($users as $user) {
            if (!in_array($user->user_id, self::$members)) {
                $sum += self::allMembers($user->user_id);
            }
        }
        return $sum;
    }


    /**
     * 获取区域信息及分红利率
     * @param $user_id
     * @return RegionUser|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    public static function captain($user_id)
    {
        $captain = RegionUser::find()
            ->where(['user_id' => $user_id, 'is_delete' => 0, 'status' => CommonRegion::STATUS_BECOME])
            ->with(['level'])
            ->asArray()
            ->one();
        if (!$captain) {
            throw new \Exception('区域不存在');
        }
        if (!empty($captain['level'])) {
            $captain['bonus_rate'] = $captain['level']['rate'];
        } else {
            $bonusRate = RegionSetting::get(\Yii::$app->mall->id, 'bonus_rate', 0);
            $captain['bonus_rate'] = $bonusRate;
        }
        return $captain;
    }

    /**
     * 根据地址获取ID
     * @param $data
     */
    public static function getAddressId(&$data)
    {
        $address_arr = DistrictArr::getArr();
        $data['province_id'] = $data['city_id'] = $data['district_id'] = 0;
        foreach ($address_arr as $item) {
            if (
                $item['name'] === $data['province'] &&
                $item['level'] === 'province'
            ) {
                $data['province_id'] = $item['id'];
            }
        }

        foreach ($address_arr as $item) {
            if (
                $item['name'] === $data['city'] &&
                $item['level'] === 'city' &&
                $item['parent_id'] == $data['province_id']
            ) {
                $data['city_id'] = $item['id'];
                break;
            }
        }

        foreach ($address_arr as $item) {
            if (
                $item['name'] === $data['district'] &&
                $item['level'] === 'district' &&
                $item['parent_id'] == $data['city_id']
            ) {
                $data['district_id'] = $item['id'];
                break;
            }
        }
    }

    public static function getBonusData(&$data)
    {
        if (empty(self::$setting)) {
            self::$setting = RegionSetting::getList($data['mall_id']);
        }
        //取得区域各级分红比例
        $area_data = RegionArea::find()->alias('a')->leftJoin(
            ['ad' => RegionAreaDetail::tableName()],
            'ad.area_id = a.id'
        )
            ->andWhere(
                [
                    'a.is_delete' => 0,
                    'a.mall_id' => $data['mall_id'],
                    'ad.is_delete' => 0,
                    'ad.mall_id' => $data['mall_id']
                ]
            )
            ->andWhere(['ad.province_id' => $data['province_id']])
            ->asArray()
            ->one();
        if (empty($area_data)) {
            \Yii::error($data['province'] . '未被设置区域代理——跳过分红');
            $data['area_name'] = '该省未被设置区域代理';
            $data['province_rate'] = 0;
            $data['city_rate'] = 0;
            $data['district_rate'] = 0;

            $data['province_num'] = 0;
            $data['city_num'] = 0;
            $data['district_num'] = 0;

            //省
            $data['province_bonus_rate'] = 0;
            $data['province_price'] = 0;
            //市
            $data['city_bonus_rate'] = 0;
            $data['city_price'] = 0;
            //区
            $data['district_bonus_rate'] = 0;
            $data['district_price'] = 0;
        } else {
            $data['area_name'] = $area_data['name'] ?? '该省未被设置区域代理';
            $data['province_rate'] = $area_data['province_rate'] ?? 0;
            $data['city_rate'] = $area_data['city_rate'] ?? 0;
            $data['district_rate'] = $area_data['district_rate'] ?? 0;

            //取对应区域级别用户及人数
            $data['province_user'] = RegionRelation::find()->alias('rr')
                ->innerJoin(['ru' => RegionUser::tableName()], 'ru.user_id = rr.user_id')
                ->andWhere(
                    [
                        'rr.mall_id' => $data['mall_id'],
                        'rr.is_delete' => 0,
                        'rr.district_id' => $data['province_id'],
                        'rr.is_update' => 0,
                        'ru.is_delete' => 0,
                        'ru.status' => 1
                    ]
                )->with('user')
                ->select('rr.user_id,rr.district_id')->asArray()->all();
            $data['province_num'] = count($data['province_user']) ?? 0;

            $data['city_user'] = RegionRelation::find()->alias('rr')
                ->innerJoin(['ru' => RegionUser::tableName()], 'ru.user_id = rr.user_id')
                ->andWhere(
                    [
                        'rr.mall_id' => $data['mall_id'],
                        'rr.is_delete' => 0,
                        'district_id' => $data['city_id'],
                        'rr.is_update' => 0,
                        'ru.is_delete' => 0,
                        'ru.status' => 1
                    ]
                )->with('user')
                ->select('rr.user_id,rr.district_id')->asArray()->all();
            $data['city_num'] = count($data['city_user']) ?? 0;

            $data['district_user'] = RegionRelation::find()->alias('rr')
                ->innerJoin(['ru' => RegionUser::tableName()], 'ru.user_id = rr.user_id')
                ->andWhere(
                    [
                        'rr.mall_id' => $data['mall_id'],
                        'rr.is_delete' => 0,
                        'rr.district_id' => $data['district_id'],
                        'rr.is_update' => 0,
                        'ru.is_delete' => 0,
                        'ru.status' => 1
                    ]
                )->with('user')
                ->select('rr.user_id,rr.district_id')->asArray()->all();
            $data['district_num'] = count($data['district_user']) ?? 0;

            //40%*15+30%*10+20%*5+50%*20+40%*14+30%*8=19
            //总权重
            bcscale(6);
            $weight =
                bcadd(
                    bcadd(
                        bcmul($data['province_rate'] / 100, $data['province_num']),
                        bcmul($data['city_rate'] / 100, $data['city_num'])
                    ),
                    bcmul($data['district_rate'] / 100, $data['district_num'])
                );
            //省
            $data['province_bonus_rate'] = $weight == 0 ? 0 : bcdiv($data['province_rate'], $weight);
            $data['province_price'] = !empty($data['province_num']) && $weight != 0
                ? bcmul(
                    bcmul($data['total_pay_price'], self::$setting['region_rate'] / 100),
                    $data['province_bonus_rate'] / 100,
                    2
                ) : 0;
            //市
            $data['city_bonus_rate'] = $weight == 0 ? 0 : bcdiv($data['city_rate'], $weight);
            $data['city_price'] = !empty($data['city_num']) && $weight != 0
                ? bcmul(
                    bcmul($data['total_pay_price'], self::$setting['region_rate'] / 100),
                    $data['city_bonus_rate'] / 100,
                    2
                ) : 0;
            //区
            $data['district_bonus_rate'] = $weight == 0 ? 0 : bcdiv($data['district_rate'], $weight);
            $data['district_price'] = !empty($data['district_num']) && $weight != 0
                ? bcmul(
                    bcmul($data['total_pay_price'], self::$setting['region_rate'] / 100),
                    $data['district_bonus_rate'] / 100,
                    2
                ) : 0;
        }
    }
}
