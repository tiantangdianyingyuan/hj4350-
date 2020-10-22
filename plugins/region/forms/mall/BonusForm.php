<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\region\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\region\jobs\RegionBonusJob;
use app\plugins\region\models\RegionOrder;
use app\plugins\region\models\RegionSetting;
use app\plugins\region\models\RegionUser;

class BonusForm extends Model
{
    public $type;
    public $is_save;//分红

    public function rules()
    {
        return [
            [['type', 'is_save'], 'integer'],
        ];
    }

    //查询分红总数据
    public function search_data()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $setting = RegionSetting::getList(\Yii::$app->mall->id);
            if ($setting['is_region'] == 0) {
                throw new \Exception('区域代理未开启');
            }
            //是否有可分红订单，1有，0无
            $is_bonus = 1;
            /* @var RegionOrder $first_data */
            $first_data = RegionOrder::find()->where(
                ['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]
            )->orderBy('created_at')->one();
            if (empty($first_data)) {
                $is_bonus = 0;
            }
            $year = date('Y', strtotime(@$first_data->created_at)) ?? '';
            $mouth = date('m', strtotime(@$first_data->created_at)) ?? '';
            $day = date('d', strtotime(@$first_data->created_at)) ?? '';
            $week = '';

            $first_day = '';
            $last_day = '';
            if ($this->type == 1) {
                if ($day >= 1 && $day <= 7) {
                    $week = '第一周';
                    $first_day = $year . '-' . $mouth . '-01 00:00:00';
                    $last_day = $year . '-' . $mouth . '-07 23:59:59';
                } else {
                    if ($day >= 8 && $day <= 14) {
                        $week = '第二周';
                        $first_day = $year . '-' . $mouth . '-08 00:00:00';
                        $last_day = $year . '-' . $mouth . '-14 23:59:59';
                    } else {
                        if ($day >= 15 && $day <= 21) {
                            $week = '第三周';
                            $first_day = $year . '-' . $mouth . '-15 00:00:00';
                            $last_day = $year . '-' . $mouth . '-21 23:59:59';
                        } else {
                            if ($day >= 22 && $day <= 31) {
                                $week = '第四周';
                                $first_day = $year . '-' . $mouth . '-22 00:00:00';
                                $last_day = date('Y-m-t 23:59:59', strtotime(@$first_data->created_at));//月末日期时间
                            }
                        }
                    }
                }
            } elseif ($this->type == 2) {
                $first_day = date('Y-m-01 00:00:00', strtotime(@$first_data->created_at));//月头日期时间
                $last_day = date('Y-m-t 23:59:59', strtotime(@$first_data->created_at));//月末日期时间
            } else {
                throw new \Exception('结算周期参数错误');
            }
            if (strtotime($last_day) >= time()) {
                $is_bonus = 0;
            }
            //分红操作
            if ($this->is_save) {
                $data = [
                    'mall_id' => \Yii::$app->mall->id,
                    'first_day' => $first_day,
                    'last_day' => $last_day,
                    'type' => $this->type
                ];
                $queue_id = \Yii::$app->queue->delay(0)->push(new RegionBonusJob($data));
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '处理请求已发送',
                    'queue_id' => $queue_id
                ];
            } else {
                $time_data = RegionOrder::find()->select(
                    ['sum(total_pay_price) as total_pay_price', 'count(order_id) as order_num']
                )
                    ->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                    ->andWhere(['>=', 'created_at', $first_day])
                    ->andWhere(['<=', 'created_at', $last_day])
                    ->orderBy('created_at')
                    ->asArray()
                    ->one();
                $region_rate = $setting['region_rate'];
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'is_bonus' => $is_bonus,
                        'year' => $year . '年',
                        'mouth' => $mouth . '月',
                        'week' => $week,
                        'first_day' => substr($first_day, 0, 10),
                        'last_day' => substr($last_day, 0, 10),
                        'order_num' => $time_data['order_num'],
                        'total_pay_price' => $time_data['total_pay_price'],
                        'bonus_price' => bcmul($time_data['total_pay_price'], $region_rate / 100),
                        'region_rate' => (string)$region_rate,
                        'region_num' => RegionUser::find()->where(
                                ['status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]
                            )->count() ?? '0',
                    ]
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
