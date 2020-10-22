<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:22
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\region\events\RegionEvent;
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionLevelUp;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionUser;
use app\plugins\region\models\RegionUserInfo;

class RegionEditForm extends Model
{
    public $user_id;
    public $level;
    public $name;
    public $phone;
    public $province_id;
    public $city_id;
    public $district_id;

    /**@var RegionArea area* */
    private $area;

    public $status;
    public $reason;

    public function rules()
    {
        return [
            [['province_id', 'level'], 'required'],
            [['user_id', 'level', 'phone', 'status'], 'integer'],
            [['name', 'reason'], 'string', 'max' => 100],
            [['city_id', 'district_id'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '姓名',
            'phone' => '手机号',
            'province_id' => '省份',
            'reason' => '拒绝理由',
            'status' => '状态',
            'level' => '代理级别'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->user_id) {
                throw new \Exception('错误的用户');
            }
            if (!$this->level) {
                throw new \Exception('错误的等级');
            }
            $region = RegionUser::findOne(
                ['user_id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id]
            );

            $common = CommonRegion::getInstance();
            $common->user_id = $this->user_id;
            $common->province_id = $this->province_id;
            $this->area = $common->area();

            //添加区域开始
            if (!$region) {
                $common->applyed_at = '0000-00-00 00:00:00';
                $common->agreed_at = mysql_timestamp();
            }
            $common->status = RegionUser::STATUS_BECOME;
            $common->area_id = $this->area->id;
            $common->level = $this->level;
            $common->province_id = $this->province_id;
            $common->city_id = $this->city_id;
            $common->district_id = $this->district_id;
            $common->name = $this->name;
            $common->mobile = $this->phone;
            $common->saveRegion();

            $levelUp = RegionLevelUp::find()
                ->where(
                    [
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'user_id' => $this->user_id,
                        'status' => 0
                    ]
                )
                ->one();

            if ($levelUp) {
                $levelUp->is_delete = 1;
                $levelUp->save();
            }

            RegionRelation::updateAll(
                [
                    'is_delete' => 1
                ],
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => $this->user_id,
                    'is_update' => 1
                ]
            );

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 审核成为代理
     * @return array
     * @throws \Exception
     */
    public function become()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $region = RegionUser::findOne(
                [
                    'user_id' => $this->user_id,
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id
                ]
            );
            if (!$region) {
                throw new \Exception('该审核记录不存在');
            }

            if ($region->status == RegionUser::STATUS_BECOME) {
                throw new \Exception('已经是代理了');
            }

            if ($this->status != RegionUser::STATUS_BECOME && $this->status != RegionUser::STATUS_REJECT) {
                throw new \Exception('错误的审核状态');
            }
            if ($this->status == RegionUser::STATUS_REJECT) {
                if (!$this->reason) {
                    throw new \Exception('请填写拒绝理由');
                }
            }

            $common = CommonRegion::getInstance();
            $common->user_id = $this->user_id;
            $common->province_id = $this->province_id;
            $this->area = $common->area();

            //添加区域开始
            $common->status = $this->status;
            $common->area_id = $this->area->id;
            $common->level = $this->level;
            $common->agreed_at = mysql_timestamp();
            $common->province_id = $this->province_id;
            $common->city_id = $this->city_id;
            $common->district_id = $this->district_id;
            $common->name = $this->name;
            $common->mobile = $this->phone;
            $common->reason = $this->reason;
            $newRegion = $common->saveRegion();

            \Yii::$app->trigger(
                RegionUser::EVENT_BECOME,
                new RegionEvent(
                    [
                        'region' => $newRegion,
                    ]
                )
            );

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => '操作成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    //解除代理
    public function remove()
    {
        try {
            $region = RegionUser::findOne(
                [
                    'user_id' => $this->user_id,
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id,
                    'status' => 1
                ]
            );
            if (!$region) {
                throw new \Exception('该代理状态错误');
            }
            if (!$this->reason) {
                throw new \Exception('请填写解除理由');
            }
            $user_info = RegionUserInfo::findOne(['user_id' => $this->user_id]);
            $user_info->reason = $this->reason;
            if (!$user_info->save()) {
                throw new \Exception($this->getErrorMsg($user_info));
            }
            $region->status = RegionUser::STATUS_REMOVE;
            if (!$region->save()) {
                throw new \Exception($this->getErrorMsg($region));
            }

            RegionRelation::updateAll(
                [
                    'is_delete' => 1
                ],
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => $this->user_id
                ]
            );

            RegionLevelUp::updateAll(
                [
                    'is_delete' => 1
                ],
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'status' => 0,
                    'user_id' => $this->user_id,
                ]
            );

            \Yii::$app->trigger(
                RegionUser::EVENT_REMOVE,
                new RegionEvent(
                    [
                        'region' => $region,
                    ]
                )
            );

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
