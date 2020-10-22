<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 13:32
 */

namespace app\plugins\region\forms\api;

use app\core\response\ApiCode;
use app\models\DistrictArr;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionLevelUp;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionSetting;
use app\plugins\region\models\RegionUser;
use app\plugins\region\models\RegionUserInfo;
use app\validators\PhoneNumberValidator;

class RegionForm extends Model
{
    public $name;
    public $mobile;
    public $is_agree;
    public $province_id;
    public $city_id;
    public $district_id;
    public $level;

    /**@var RegionArea area* */
    private $area;

    public function rules()
    {
        return [
            [['province_id', 'level'], 'required'],
            [['is_agree', 'level'], 'integer'],
            [['name'], 'trim'],
            [['mobile'], PhoneNumberValidator::className()],
            [['name'], 'string'],
            [['city_id', 'district_id'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '申请人姓名',
            'mobile' => '申请人联系方式',
            'is_agree' => '阅读申请协议',
            'province_id' => '省',
            'city_id' => '市',
            'district_id' => '区/县',
            'level' => '等级'
        ];
    }

    public function apply()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = CommonRegion::getInstance();
            $common->user_id = \Yii::$app->user->id;
            $common->province_id = $this->province_id;
            $this->area = $common->area();
            $condition = $common->index($this->level);
            if ($condition['pass'] == false) {
                throw new \Exception('未满足条件');
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
        $applyType = RegionSetting::get(\Yii::$app->mall->id, 'apply_type', 0);
        $status = RegionUser::STATUS_APPLYING;
        $agreeTime = '0000-00-00 00:00:00';
        if ($this->is_agree != 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "请先查看区域代理申请协议并同意"
            ];
        }

        if (
            $applyType == RegionSetting::APPLY_INFO_NEED_VERIFY
            && (empty($this->mobile) || empty($this->name))
        ) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "请完善申请信息"
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $common->status = $status;
            $common->area_id = $this->area->id;
            $common->level = $this->level;
            $common->applyed_at = mysql_timestamp();
            $common->agreed_at = $agreeTime;
            $common->province_id = $this->province_id;
            $common->city_id = $this->city_id;
            $common->district_id = $this->district_id;
            $common->name = $this->name;
            $common->mobile = $this->mobile;
            $common->saveRegion();
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '申请区域代理成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**查看代理申请状态**/
    public function getStatus()
    {
        $model = RegionUser::find()
            ->select(
                "ru.user_id,
                u.nickname,
                ru.level,
                ru.province_id,
                ru.status,
                ru.is_delete,
                ru.applyed_at,
                ru.agreed_at,
                ru.created_at,
                rui.all_bonus,
                rui.total_bonus,
                rui.out_bonus,
                rui.name,
                rui.phone,
                i.avatar,
                rui.reason,
                (CASE WHEN ru.level = 1 THEN ra.province_rate WHEN ru.level = 2 
                THEN ra.city_rate ELSE ra.district_rate END) AS bonus_rate
                "
            )
            ->alias('ru')
            ->where(['ru.mall_id' => \Yii::$app->mall->id, 'ru.user_id' => \Yii::$app->user->id, 'ra.is_delete' => 0])
            ->leftJoin(['u' => User::tableName()], 'u.id = ru.user_id')
            ->leftJoin(['rui' => RegionUserInfo::tableName()], 'ru.user_id = rui.user_id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
            ->leftJoin(['ra' => RegionArea::tableName()], 'ra.id = ru.area_id')
            ->asArray()
            ->one();
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '用户未申请区域代理'
            ];
        }

        $cashed = CommonRegion::getInstance()->getPrice(\Yii::$app->user->id);
        $model['all_bonus'] = price_format($model['all_bonus']);
        $model['cash_bonus'] = price_format($cashed['cash_bonus']);
        $model['total_bonus'] = price_format($model['total_bonus']);
        $model['is_up'] = 0;
        $model['province_text'] = DistrictArr::getDistrict($model['province_id'])['name'];

        if (!in_array($model['status'], [0, 1])) {
            return [
                'code' => 0,
                'msg' => '数据请求成功',
                'data' => [
                    'region' => $model
                ]
            ];
        }

        try {
            $common = CommonRegion::getInstance();
            $common->user_id = \Yii::$app->user->id;
            $res = $common->nextLevel();
            foreach ($res as $item) {
                if ($item['pass'] == true && $item['level'] < $model['level']) {
                    $model['is_up'] = 1;
                    $model['level_up'] = $item;
                    break;
                }
            }
        } catch (\Exception $exception) {
            $model['is_up'] = 0;
        }

        if (!empty($model['level_up'])) {
            $relation = RegionRelation::find()
                ->andWhere(
                    [
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'user_id' => \Yii::$app->user->id,
                        'is_update' => 1
                    ]
                )
                ->select('user_id,district_id')->asArray()->all();
            $model['level_up']['relation'] = $relation;
            foreach ($model['level_up']['relation'] as &$v) {
                $v['name'] = DistrictArr::getDistrict($v['district_id'])['name'];
            }
            unset($v);
        }

        $relation = RegionRelation::find()
            ->andWhere(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => \Yii::$app->user->id,
                    'is_update' => 0
                ]
            )
            ->select('district_id')->column();

        foreach ($relation as $item) {
            $newItem['district_id'] = $item;
            $newItem['name'] = DistrictArr::getDistrict($item)['name'];
            $model['relation'][] = $newItem;
        }

        if ($model['level'] == 3) {
            $parent_id = DistrictArr::getDistrict($model['relation'][0]['district_id'])['parent_id'];
            $model['city_id'] = $parent_id;
            $model['city_text'] = DistrictArr::getDistrict($parent_id)['name'];
        } elseif ($model['level'] == 2) {
            $model['city_id'] = DistrictArr::getDistrict($model['relation'][0]['district_id'])['id'];
            $model['city_text'] = DistrictArr::getDistrict($model['city_id'])['name'];
        }

        $levelUpLog = RegionLevelUp::find()->where(
            [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id,
                'is_read' => 0
            ]
        )->select(['level', 'status', 'reason', 'updated_at'])
            ->orderBy(['id' => SORT_DESC])->one();

        $model['level_up_log'] = $levelUpLog;

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'region' => $model
            ]
        ];
    }

    public function clearApply()
    {
        $t = \Yii::$app->db->beginTransaction();

        try {
            $region = RegionUser::findOne(['user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id]);
            if ($region) {
                $region->status = RegionUser::STATUS_REAPPLYING;
                $region->is_delete = 0;
                $region->applyed_at = '0000-00-00 00:00:00';
                $region->agreed_at = '0000-00-00 00:00:00';
                $region->save();

                $regionInfo = RegionUserInfo::findOne(['user_id' => \Yii::$app->user->id]);
                $regionInfo->remark = '';
                $regionInfo->reason = '';
                $regionInfo->save();
                $t->commit();
            }
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => ''
        ];
    }

    public function getInfo()
    {
        $model = RegionUserInfo::find()
            ->where(['user_id' => \Yii::$app->user->id])
            ->one();

        $list = CommonRegion::getInstance()->getPrice(\Yii::$app->user->id);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '数据请求成功',
            'data' => [
                'all_bonus' => $model->all_bonus,
                'total_bonus' => $model->total_bonus,
                'out_bonus' => $model->out_bonus,
                'cash_bonus' => price_format($list['cash_bonus'], 2),
                'loading_bonus' => price_format($list['un_pay'], 2),
                'user_instructions' => RegionSetting::get(\Yii::$app->mall->id, 'user_instructions', '')
            ]
        ];
    }

    public function clearLevelUp()
    {
        $t = \Yii::$app->db->beginTransaction();

        try {
            $levelUpLog = RegionLevelUp::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => \Yii::$app->user->id,
                    'is_read' => 0,
                    'status' => [1, 2]
                ]
            )->orderBy(['id' => SORT_DESC])->one();

            if ($levelUpLog) {
                $levelUpLog->is_read = 1;
                if (!$levelUpLog->save()) {
                    throw new \Exception($this->getErrorMsg($levelUpLog));
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => ''
        ];
    }
}
