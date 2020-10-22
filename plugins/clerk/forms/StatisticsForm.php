<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/24
 * Email: <657268722@qq.com>
 */

namespace app\plugins\clerk\forms;


use app\core\response\ApiCode;
use app\models\ClerkUser;
use app\models\GoodsCardClerkLog;
use app\models\Model;
use app\models\Order;
use app\models\OrderClerk;

class StatisticsForm extends Model
{
    public $key;

    public function rules()
    {
        return [
            [['key'], 'integer'],
            [['key',], 'default', 'value' => 0],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        //核销订单数量及金额
        $order_query = OrderClerk::find()->alias('oc')
            ->leftJoin(['o' => Order::tableName()], 'o.id = oc.order_id and o.is_delete = 0')
            ->leftJoin(['cu' => ClerkUser::tableName()], 'cu.id = o.clerk_id')
            ->where(['oc.mall_id' => \Yii::$app->mall->id, 'oc.is_delete' => 0])
            ->andWhere(['cu.user_id' => \Yii::$app->user->id])
//            ->andWhere(['cu.user_id' => 226])
            ->orderBy('time')
            ->groupBy('time');
        switch ($this->key) {
            case 1://今日
                $order_list = $order_query->select("DATE_FORMAT(`oc`.`created_at`, '%H') AS `time`,COALESCE(COUNT(`oc`.`id`),0) as `order_num`,SUM(`o`.`total_goods_price`) as `order_price`")
                    ->andWhere(['>=', 'oc.created_at', date('Y-m-d', time()) . ' 00:00:00'])
                    ->andWhere(['<=', 'oc.created_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                break;
            case -1://昨日
                $order_list = $order_query->select("DATE_FORMAT(`oc`.`created_at`, '%H') AS `time`,COALESCE(COUNT(`oc`.`id`),0) as `order_num`,SUM(`o`.`total_goods_price`) as `order_price`")
                    ->andWhere(['>=', 'oc.created_at', date('Y-m-d', strtotime('-1 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'oc.created_at', date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'])
                    ->asArray()->all();
                break;
            case 7://7日
                $order_list = $order_query->select("DATE_FORMAT(`oc`.`created_at`, '%m-%d') AS `time`,COALESCE(COUNT(`oc`.`id`),0) as `order_num`,SUM(`o`.`total_goods_price`) as `order_price`")
                    ->andWhere(['>=', 'oc.created_at', date('Y-m-d', strtotime('-7 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'oc.created_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                break;
            case 30://30日
                $order_list = $order_query->select("DATE_FORMAT(`oc`.`created_at`, '%m-%d') AS `time`,COALESCE(COUNT(`oc`.`id`),0) as `order_num`,SUM(`o`.`total_goods_price`) as `order_price`")
                    ->andWhere(['>=', 'oc.created_at', date('Y-m-d', strtotime('-30 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'oc.created_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                break;
            default://默认累计
                $order_list = $order_query->select("DATE_FORMAT(`oc`.`created_at`, '%Y-%m') AS `time`,COALESCE(COUNT(`oc`.`id`),0) as `order_num`,SUM(`o`.`total_goods_price`) as `order_price`")->asArray()->all();
                break;
        }
        $order_num_list = $order_price_list = [];
        foreach ($order_list as $key => $item) {
            $order_num_list[$key]['time'] = $item['time'];
            $order_num_list[$key]['num'] = $item['order_num'];

            $order_price_list[$key]['time'] = $item['time'];
            $order_price_list[$key]['num'] = $item['order_price'];
        }
        switch ($this->key) {
            case 1:
            case -1:
                $order_num_list = $this->hour_24($order_num_list);
                $order_price_list = $this->hour_24($order_price_list);
                break;
            case 7:
            case 30:
                $order_num_list = $this->day_data($order_num_list, $this->key);
                $order_price_list = $this->day_data($order_price_list, $this->key);
                break;
            default:
                $month = $this->get_month_num($order_list[0]['time'] ?? date('Y-m-d', time()), date('Y-m', time()));
                $order_num_list = $this->month_data($order_num_list, $month);
                $order_price_list = $this->month_data($order_price_list, $month);
        }

        //卡卷核销数量
        $card_query = GoodsCardClerkLog::find()
            ->andWhere(['clerk_id' => \Yii::$app->user->id])
            ->orderBy('time')
            ->groupBy('time');
        switch ($this->key) {
            case 1://今日
                $card_list = $card_query->select("DATE_FORMAT(`clerked_at`, '%H') AS `time`,SUM(`use_number`) as `num`")
                    ->andWhere(['>=', 'clerked_at', date('Y-m-d', time()) . ' 00:00:00'])
                    ->andWhere(['<=', 'clerked_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                $card_list = $this->hour_24($card_list);
                break;
            case -1://昨日
                $card_list = $card_query->select("DATE_FORMAT(`clerked_at`, '%H') AS `time`,SUM(`use_number`) as `num`")
                    ->andWhere(['>=', 'clerked_at', date('Y-m-d', strtotime('-1 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'clerked_at', date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'])
                    ->asArray()->all();
                $card_list = $this->hour_24($card_list);
                break;
            case 7://7日
                $card_list = $card_query->select("DATE_FORMAT(`clerked_at`, '%m-%d') AS `time`,SUM(`use_number`) as `num`")
                    ->andWhere(['>=', 'clerked_at', date('Y-m-d', strtotime('-7 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'clerked_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                $card_list = $this->day_data($card_list, $this->key);
                break;
            case 30://30日
                $card_list = $card_query->select("DATE_FORMAT(`clerked_at`, '%m-%d') AS `time`,SUM(`use_number`) as `num`")
                    ->andWhere(['>=', 'clerked_at', date('Y-m-d', strtotime('-30 day')) . ' 00:00:00'])
                    ->andWhere(['<=', 'clerked_at', date('Y-m-d', time()) . ' 23:59:59'])
                    ->asArray()->all();
                $card_list = $this->day_data($card_list, $this->key);
                break;
            default://默认累计
                $card_list = $card_query->select("DATE_FORMAT(`clerked_at`, '%Y-%m') AS `time`,SUM(`use_number`) as `num`")
                    ->asArray()->all();
                $num = $this->get_month_num($card_list[0]['time'] ?? date('Y-m-d', time()), date('Y-m-d', time()));
                $card_list = $this->month_data($card_list, $num);
                break;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order_num_list' => $order_num_list,
                'order_price_list' => $order_price_list,
                'card_list' => $card_list,
            ]
        ];
    }

    protected function hour_24($list)
    {
        for ($i = 0; $i < 24; $i++) {
            $bool = false;
            foreach ($list as $item) {
                if ($i == intval($item['time'])) {
                    $bool = true;
                    $arr[$i]['time'] = $item['time'];
                    $arr[$i]['num'] = $item['num'];
                }
            }
            if (!$bool) {
                $arr[$i]['time'] = $i;
                $arr[$i]['num'] = '0';
            }
        }
        return !empty($arr) ? $arr : [];
    }

    protected function day_data($list, $day)
    {
        for ($i = 0; $i < $day; $i++) {
            $date = date('m.d', strtotime("-$i day"));
            $bool = false;
            foreach ($list as $item) {
                if ($date == str_replace('-', '.', $item['time'])) {
                    $bool = true;
                    $arr[$i]['time'] = str_replace('-', '.', $item['time']);
                    $arr[$i]['num'] = $item['num'];
                }
            }
            if (!$bool) {
                $arr[$i]['time'] = $date;
                $arr[$i]['num'] = '0';
            }
        }
        return !empty($arr) ? array_reverse($arr) : [];
    }

    protected function month_data($list, $month)
    {
        for ($i = 0; $i < $month; $i++) {
            $date = date('Y.m', strtotime("-$i month", strtotime(date('Y-m', time()))));//格式化到月，避免遇到31号，得到上个月也是本月的BUG
            $bool = false;
            foreach ($list as $item) {
                if ($date == str_replace('-', '.', $item['time'])) {
                    $bool = true;
                    $arr[$i]['time'] = str_replace('-', '.', $item['time']);
                    $arr[$i]['num'] = $item['num'];
                }
            }
            if (!$bool) {
                $arr[$i]['time'] = $date;
                $arr[$i]['num'] = '0';
            }
        }
        return !empty($arr) ? array_reverse($arr) : [];
    }

    //取2个时间相差的月数
    protected function get_month_num($day1, $day2)
    {
        $day1_y = date('Y', strtotime($day1));
        $day1_d = date('m', strtotime($day1));
        $day2_y = date('Y', strtotime($day2));
        $day2_d = date('m', strtotime($day2));//var_dump($day1,$day2,$day1_y,$day1_d,$day2_y,$day2_d);die;
        return abs(($day2_y - $day1_y) * 12 + ($day2_d - $day1_d) + 1);
    }
}