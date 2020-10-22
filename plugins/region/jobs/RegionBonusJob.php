<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/8
 * Time: 15:14
 */


namespace app\plugins\region\jobs;

use app\models\Model;
use app\plugins\region\forms\common\CommonForm;
use app\plugins\region\models\RegionBonusLog;
use app\plugins\region\models\RegionCashLog;
use app\plugins\region\models\RegionOrder;
use app\plugins\region\models\RegionSetting;
use app\plugins\region\models\RegionUserInfo;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class RegionBonusJob extends BaseObject implements JobInterface
{
    public $mall_id;
    public $first_day;
    public $last_day;
    public $type;

    public function execute($queue)
    {
        \Yii::error($this->first_day . '-' . $this->last_day . '的区域代理订单队列开始：');
        $t = \Yii::$app->db->beginTransaction();
        try {
            $setting = RegionSetting::getList($this->mall_id);
            if ($setting['is_region'] == 0) {
                throw new \Exception('区域代理未开启');
            }
            if ($setting['region_rate'] <= 0) {
                throw new \Exception('分红比例小于0，不分红');
            }
            //可以分红的金额，订单数
            $order_data = RegionOrder::find()
                ->select(
                    [
                        'sum(total_pay_price) as total_pay_price',
                        'count(order_id) as order_num',
                        'district',
                        'city',
                        'province',
                        'district_id',
                        'city_id',
                        'province_id',
                        'mall_id'
                    ]
                )
                ->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => $this->mall_id])
                ->andWhere(['>=', 'created_at', $this->first_day])
                ->andWhere(['<=', 'created_at', $this->last_day])
                ->orderBy('created_at')
                ->groupBy('district_id,city_id,province_id')
                ->asArray()
                ->all();
            if (empty($order_data)) {
                throw new \Exception('订单已被分红');
            }

            //预记录分红
            $bonus_model = new RegionBonusLog();
            $bonus_model->mall_id = $this->mall_id;
            $bonus_model->bonus_type = $this->type;
            $bonus_model->pre_bonus_price = 0;
            $bonus_model->bonus_price = 0;
            $bonus_model->bonus_rate = $setting['region_rate'];
            $bonus_model->pre_order_num = 0;
            $bonus_model->order_num = 0;
            $bonus_model->region_num = 0;
            $bonus_model->start_time = $this->first_day;
            $bonus_model->end_time = $this->last_day;
            if (!$bonus_model->save()) {
                throw new \Exception((new Model())->getErrorMsg($bonus_model));
            }

            bcscale(6);//为了更精确
            $user_num = 0;//参与分红人数
            $pre_bonus_price = 0;//预计分红金额
            $bonus_price = 0;//实际分红金额
            $pre_order_num = 0;//预计分红订单数
            $order_num = 0;//实际分红订单数
            foreach ($order_data as &$item) {
                $pre_bonus_price += bcmul($item['total_pay_price'], $setting['region_rate'] / 100);
                $pre_order_num += $item['order_num'];
                //取地址对应ID
//                CommonForm::getAddressId($item);//地址ID已存入分红池表，无需再取

                //分红数据处理
                CommonForm::getBonusData($item);
                //计算各级代理分红
                $user_num += $item['province_num'] + $item['city_num'] + $item['district_num'];
                $bonus_price += bcmul($item['total_pay_price'], $setting['region_rate'] / 100);
                $order_num += $item['order_num'];
                //记录每个区域代理分红流水
                $this->saveCashLog(
                    $item['province_user'],
                    $item,
                    $item['province_price'],
                    $item['province_rate'],
                    $bonus_model->id
                );
                $this->saveCashLog(
                    $item['city_user'],
                    $item,
                    $item['city_price'],
                    $item['city_rate'],
                    $bonus_model->id
                );
                $this->saveCashLog(
                    $item['district_user'],
                    $item,
                    $item['district_price'],
                    $item['district_rate'],
                    $bonus_model->id
                );
            }

            //完成用户记录后，添加分红信息
            $bonus_model->pre_bonus_price = bcmul($pre_bonus_price, 1, 2);//取2位小数，不需要四舍五入
            $bonus_model->bonus_price = bcmul($bonus_price, 1, 2);
            $bonus_model->pre_order_num = $pre_order_num;
            $bonus_model->order_num = $order_num;
            $bonus_model->region_num = $user_num;
            if (!$bonus_model->save()) {
                throw new \Exception((new Model())->getErrorMsg($bonus_model));
            }

            //分红后订单状态更新
            if (RegionOrder::updateAll(
                    [
                        'is_bonus' => 1,
                        'bonus_time' => mysql_timestamp(),
                        'bonus_id' => $bonus_model->id,
                        'bonus_rate' => $setting['region_rate']
                    ],
                    [
                        'and',
                        ['>=', 'created_at', $this->first_day],
                        ['<=', 'created_at', $this->last_day],
                        ['mall_id' => $this->mall_id]
                    ]
                ) <= 0) {
                throw new \Exception('分红订单状态更新失败');
            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('区域代理分红队列错误：');
            \Yii::error($exception->getMessage());
        }
    }

    /***
     * 记录每个代理分红情况
     * @param array $users 用户组
     * @param array $data 同一地区订单汇总信息，包括其他这些信息
     * @param float $price 分红金额
     * @param float $bonus_rate 分红比例
     * @param int $bonus_id 分红ID
     * @throws \Exception
     */
    private function saveCashLog($users, $data, $price, $bonus_rate, $bonus_id)
    {
        foreach ($users as $item) {
            //流水记录
            $cash_log = new RegionCashLog();
            $cash_log->mall_id = $this->mall_id;
            $cash_log->user_id = $item['user_id'];
            $cash_log->type = 1;
            $cash_log->price = price_format($price);
            $cash_log->desc = '区域代理分红——' . $data['province'] . '/' . $data['city'] . '/' . $data['district'];
            $cash_log->level_id = $item['user']['level'];
//            $cash_log->level_name = DistrictArr::getDistrict($item['district_id'])['name'] ?? '';
            $level_name = '';
            switch ($item['user']['level']) {
                case 1:
                    $level_name = $data['province'] . '>' . $data['city'] . '>' . $data['district'];
                    break;
                case 2:
                    $level_name = $data['city'] . '>' . $data['district'];
                    break;
                case 3:
                    $level_name = $data['district'];
                    break;
            }
            $cash_log->level_name = $level_name;
            $cash_log->order_num = $data['order_num'];
            $cash_log->bonus_rate = $bonus_rate;
            $cash_log->bonus_id = $bonus_id;
            $cash_log->province_id = $data['province_id'];
            $cash_log->city_id = $data['city_id'];
            $cash_log->district_id = $data['district_id'];

            if (!$cash_log->save()) {
                throw new \Exception((new Model())->getErrorMsg($cash_log));
            }
            //总金额记录
            if ($price > 0) {
                if (RegionUserInfo::updateAllCounters(
                        ['all_bonus' => $price, 'total_bonus' => $price],
                        ['user_id' => $item['user_id']]
                    ) <= 0) {
                    throw new \Exception('区域代理金额更新失败');
                }
            }
        }
    }
}
