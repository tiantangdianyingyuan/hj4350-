<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/21
 * Time: 14:11
 */


namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\models\DistrictArr;
use app\models\Model;
use app\plugins\region\events\RegionEvent;
use app\plugins\region\models\RegionLevelUp;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionUser;

class RegionLevelUpForm extends Model
{
    public $user_id;
    public $level;
    public $city_id;
    public $status;
    public $reason;

    /**@var RegionLevelUp $model * */
    private $model;

    public function rules()
    {
        return [
            [['level'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['city_id'], 'safe'],
            [['reason'], 'string', 'max' => 512],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '等级',
            'reason' => '理由',
        ];
    }

    public function levelUp()
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

            $model = RegionLevelUp::find()
                ->where(
                    [
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'user_id' => $this->user_id,
                        'status' => 0
                    ]
                )
                ->one();

            if (!$model) {
                throw new \Exception('升级记录不存在');
            }
            $this->model = $model;
            if ($this->status == 1) {
                $this->agree();
            } elseif ($this->status == 2) {
                $this->refuse();
            } else {
                throw new \Exception('不允许的状态码');
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '审核成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    private function agree()
    {
        $this->model->status = 1;
        if (!$this->model->save()) {
            return $this->getErrorResponse($this->model);
        }

        $user = RegionUser::find()
            ->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => $this->user_id,
                    'status' => RegionUser::STATUS_BECOME
                ]
            )
            ->one();

        if (!$user) {
            throw new \Exception('代理不存在');
        }
        $originLevel = $user->level;
        $user->level = $this->level;
        if (!$user->save()) {
            throw new \Exception($this->getErrorMsg($user));
        }

        RegionRelation::updateAll(
            [
                'is_delete' => 1
            ],
            [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'user_id' => $this->user_id,
                'is_update' => 0
            ]
        );

        if ($this->level == 2) {
            if (empty($this->city_id)) {
                throw new \Exception('请选择市');
            }

            $district = [];
            foreach ($this->city_id as $item) {
                if (DistrictArr::getDistrict($item)['level'] != 'city') {
                    throw new \Exception('你选择的市级数据不合法');
                }
                $district[] = [
                    $item,
                    \Yii::$app->mall->id,
                    $this->user_id,
                    0,
                    mysql_timestamp()
                ];
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

            \Yii::$app->db->createCommand()->batchInsert(
                RegionRelation::tableName(),
                ['district_id', 'mall_id', 'user_id', 'is_delete', 'created_at'],
                $district
            )->execute();
        } else {
            RegionRelation::updateAll(
                [
                    'is_update' => 0
                ],
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'user_id' => $this->user_id,
                    'is_update' => 1
                ]
            );
        }

        \Yii::$app->trigger(
            RegionUser::EVENT_LEVEL_UP,
            new RegionEvent(
                [
                    'region' => $user,
                    'originLevel' => $originLevel
                ]
            )
        );
    }

    private function refuse()
    {
        $this->model->status = 2;
        $this->model->reason = $this->reason;
        if (!$this->model->save()) {
            return $this->getErrorResponse($this->model);
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
    }
}
