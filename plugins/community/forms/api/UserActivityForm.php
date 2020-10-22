<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\helpers\ArrayHelper;
use app\models\Mall;
use app\models\Model;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityLog;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityRelations;
use yii\db\Exception;

/**
 * @property Mall $mall
 */
class UserActivityForm extends Model
{
    public $id;
    public $user_id;
    public $middleman_id;
    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'middleman_id'], 'integer'],
            [['longitude', 'latitude'], 'number']
        ];
    }

    public function getActivityDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id) {
                throw new Exception('活动ID不能为空');
            }

            //不是通过分享，且未绑定团长，最近的团长
            if (!$this->longitude || !$this->latitude) {
                throw new Exception('手机位置未授权');
            }
            $setting = CommonSetting::getCommon()->getSetting();
            $relations = CommunityRelations::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
            $model = CommunityMiddleman::find()->alias('m')->leftJoin(['ca' => CommunityAddress::tableName()], 'ca.user_id = m.user_id')
                ->where(['m.mall_id' => \Yii::$app->mall->id, 'm.is_delete' => 0, 'm.status' => 1, 'ca.is_delete' => 0, 'ca.is_default' => 1])
                ->with('userInfo')->select('ca.*');
            if (empty($relations) || $relations->middleman_id == 0) {
                //用户分享，被分享的人显示分享人的团长
                if ($this->user_id) {
                    $user_relations = CommunityRelations::findOne(['user_id' => $this->user_id, 'is_delete' => 0]);
                    $this->middleman_id = $user_relations->middleman_id ?? 0;
                }
                //未绑定，非分享进入
                if (!$this->middleman_id) {
                    $middleman_info = $model->asArray()->all();
                    if (empty($middleman_info)) {
                        throw new Exception('附近没有团长');
                    }
                    $distance = 0;
                    $info = [];
                    foreach ($middleman_info as $item) {
                        $item['distance'] = get_distance($this->longitude, $this->latitude, $item['longitude'], $item['latitude']);
                        //取最近距离的
                        if ($item['distance'] < $distance || $distance == 0) {
                            $distance = $item['distance'];
                            $info = $item;
                        }
                    }
                    $middleman_info = $info;
                    $this->middleman_id = $info['user_id'];
                } else {
                    //未绑定，分享进入
                    $middleman_info = $model->andWhere(['m.user_id' => $this->middleman_id])->asArray()->one();

                }
                $setting['is_allow_change'] = 1;//只要是没绑定团长的都是可以切换的
            } else {
                $this->middleman_id = $relations->middleman_id;
                //已绑定
                $middleman_info = $model->andWhere(['m.user_id' => $this->middleman_id])->asArray()->one();
            }
            $middleman_info['distance'] = get_distance($this->longitude, $this->latitude, $middleman_info['longitude'], $middleman_info['latitude']);
            $middleman_info['avatar'] = $middleman_info['userInfo']['avatar'];
            $middleman_info['is_allow_change'] = $setting['is_allow_change'];
            unset($middleman_info['userInfo']);
            //记录浏览
            $log = CommunityLog::findOne(['user_id' => \Yii::$app->user->id, 'middleman_id' => $this->middleman_id, 'activity_id' => $this->id, 'is_delete' => 0]);
            if (empty($log)) {
                $log = new CommunityLog();
                $log->user_id = \Yii::$app->user->id;
                $log->middleman_id = $this->middleman_id;
                $log->activity_id = $this->id;
                if (!$log->save()) {
                    throw new Exception((new Model())->getErrorMsg($log));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => array_merge(
                    CommonActivity::getActivityDetail($this->id, $this->middleman_id),
                    [
                        'middleman_info' => ArrayHelper::filter($middleman_info, [
                            'avatar', 'city', 'detail', 'distance', 'district', 'id', 'latitude', 'location',
                            'longitude', 'mobile', 'name', 'province', 'user_id', 'is_allow_change'
                        ]),
                        'is_middleman' => CommunityMiddleman::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'status' => 1]) ? 1 : 0
                    ]
                )
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
