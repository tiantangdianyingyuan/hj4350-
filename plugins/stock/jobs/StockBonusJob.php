<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/8
 * Time: 15:14
 */


namespace app\plugins\stock\jobs;

use app\models\Mall;

use app\models\Model;
use app\plugins\stock\models\StockBonusLog;
use app\plugins\stock\models\StockCashLog;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class StockBonusJob extends BaseObject implements JobInterface
{
    public $mall_id;
    public $first_day;
    public $last_day;
    public $type;

    public function execute($queue)
    {
        \Yii::error($this->first_day . '-' . $this->last_day . '的股东分红订单队列开始：');
        $t = \Yii::$app->db->beginTransaction();
        try {
            $setting = StockSetting::getList($this->mall_id);
            if ($setting['is_stock'] == 0) {
                throw new \Exception('股东分红未开启');
            }
            if ($setting['stock_rate'] <= 0) {
                throw new \Exception('分红比例小于0，不分红');
            }
            //可以分红的金额，订单数
            $time_data = StockOrder::find()->select(['sum(total_pay_price) as total_pay_price', 'count(order_id) as order_num'])
                ->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => $this->mall_id])
                ->andWhere(['>=', 'created_at', $this->first_day])
                ->andWhere(['<=', 'created_at', $this->last_day])
                ->orderBy('created_at')
                ->asArray()
                ->one();
            if (empty($time_data)) {
                throw new \Exception('订单已被分红');
            }

            //股东计算
            $user_list = StockUser::find()->where(['is_delete' => 0, 'status' => 1, 'mall_id' => $this->mall_id])->with('level')->asArray()->all();
            if (empty($user_list)) {
                throw new \Exception('股东人数小于0，不分红');
            }
            $level_arr = [];//记录等级，分红比例，人数，对应用户ids
            foreach ($user_list as $item) {
                //第一个
                if (empty($level_arr)) {
                    $level_arr[] = [
                        'level_id' => $item['level']['id'],
                        'name' => $item['level']['name'],
                        'bonus_rate' => $item['level']['bonus_rate'],
                        'num' => 1,
                        'user' => [
                            $item['user_id']
                        ]
                    ];
                } else {
                    //第二个后每个对比是否存在同一个等级的记录
                    $is_have = false;
                    foreach ($level_arr as &$value) {
                        if ($value['level_id'] === $item['level']['id']) {
                            array_push($value['user'], $item['user_id']);
                            $value['num']++;
                            $is_have = true;//已存在
                        }
                    }
                    //不存在，则新增
                    if (!$is_have) {
                        $level_arr[] = [
                            'level_id' => $item['level']['id'],
                            'name' => $item['level']['name'],
                            'bonus_rate' => $item['level']['bonus_rate'],
                            'num' => 1,
                            'user' => [
                                $item['user_id']
                            ]
                        ];
                    }
                }
            }
            //计算每个股东分红
            bcscale(6);//为了更精确
            $all_rate = 0;
            foreach ($level_arr as &$a) {
                $num = count($a['user']) ?? 0;

                $all_rate = bcadd($all_rate, bcmul($a['bonus_rate'] / 100, $num));//记录每个等级分红比例  10%*2+20%*5+30%*10
                $a['bonus_price'] = bcmul(bcmul($time_data['total_pay_price'], $setting['stock_rate'] / 100), $a['bonus_rate'] / 100);//第一次记录  10%*100元
            }
//            var_dump($level_arr, $all_rate);die;
//等级1每个股东可得：10%*100元/（10%*2+20%*5+30%*10）=2.38元
//
//等级2每个股东可得： 20%*100元/（10%*2+20%*5+30%*10）=4.76元
//
//等级3每个股东可得： 30%*100元/（10%*2+20%*5+30%*10）=7.14元
            //记录分红
            $bonus_model = new StockBonusLog();
            $bonus_model->mall_id = $this->mall_id;
            $bonus_model->bonus_type = $this->type;
            $bonus_model->bonus_price = bcmul($time_data['total_pay_price'], $setting['stock_rate'] / 100);
            $bonus_model->bonus_rate = $setting['stock_rate'];
            $bonus_model->order_num = $time_data['order_num'];
            $bonus_model->stock_num = count($user_list) ?? 0;
            $bonus_model->start_time = $this->first_day;
            $bonus_model->end_time = $this->last_day;
            if (!$bonus_model->save()) {
                throw new \Exception((new Model())->getErrorMsg($bonus_model));
            }
            //记录每个股东分红
            foreach ($level_arr as $c) {
                foreach ($c['user'] as $d) {
                    bcscale(2);//四舍五入，不至于最后分红大于总分红
                    $price = bcdiv($c['bonus_price'], $all_rate) ?? 0;
                    //流水记录
                    $cash_log = new StockCashLog();
                    $cash_log->mall_id = $this->mall_id;
                    $cash_log->user_id = $d;
                    $cash_log->type = 1;
                    $cash_log->price = $price;
                    $cash_log->desc = '股东分红';
                    $cash_log->level_id = $c['level_id'];
                    $cash_log->level_name = $c['name'];
                    $cash_log->order_num = $time_data['order_num'];
                    $cash_log->bonus_rate = $c['bonus_rate'];
                    $cash_log->bonus_id = $bonus_model->id;
                    if (!$cash_log->save()) {
                        throw new \Exception((new Model())->getErrorMsg($cash_log));
                    }
                    //总金额记录
                    if ($price > 0) {
                        if (StockUserInfo::updateAllCounters(['all_bonus' => $price, 'total_bonus' => $price], ['user_id' => $d]) <= 0) {
                            throw new \Exception('股东分红金额更新失败');
                        }
                    }
                }
            }

            //分红后订单状态更新
            if (StockOrder::updateAll(['is_bonus' => 1, 'bonus_time' => mysql_timestamp(), 'bonus_id' => $bonus_model->id],
                    ['and', ['>=', 'created_at', $this->first_day], ['<=', 'created_at', $this->last_day], ['mall_id' => $this->mall_id]]) <= 0) {
                throw new \Exception('分红订单状态更新失败');
            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('股东分红队列：');
            \Yii::error($exception->getMessage());
        }
    }
}