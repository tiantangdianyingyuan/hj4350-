<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Order;

class BuyDataForm extends Model
{
    public function search()
    {
        try {
            $mall = \Yii::$app->mall->getMallSetting();
            if ($mall['setting']['is_purchase_frame'] == 1) {
                $list = Order::find()->alias('o')->select(['o.id', 'o.user_id', 'o.pay_time'])->where([
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['is_pay' => 1],
                    ['not', ['in', 'sign', ['scan_code_pay', 'pond', 'scratch', 'lottery']]]
                ])->orderBy('id DESC')
                    ->limit($mall['setting']['purchase_num'] ?: 0)
                    ->all();

                $newList = [];

                foreach ($list as $k => $v) {
                    $nickname = mb_strlen($v->user->nickname, 'UTF-8') > 5 ? mb_substr($v->user->nickname, 0, 4, 'UTF-8') . '...' : $v->user->nickname;
                    $goods_name = mb_strlen($v->detail{0}->goodsWarehouse->name, 'UTF-8') > 8 ? mb_substr($v->detail{0}->goodsWarehouse->name, 0, 7, 'UTF-8') . '...' : $v->detail{0}->goodsWarehouse->name;

                    $diff = time() - strtotime($v{'pay_time'});
                    $minute = floor($diff / 60);
                    $hour = floor($minute / 60);

                    if ($diff > 24 * 60 * 60) {
                        $time_str = '一天前';
                    } else if ($hour > 0) {
                        $time_str = $hour . '小时前';
                    } else if ($minute > 0) {
                        $time_str = $minute . '分钟前';
                    } else {
                        $time_str = $diff . '秒前';
                    }
                    array_push($newList, [
                        'content' => sprintf('%s购买了%s', $nickname, $goods_name),
                        'time_str' => $time_str,
                        'avatar' => $v->user->userInfo->avatar,
                        'page_url' => '',
                        'sign' => $v->sign ?: '',
                        'goods_id' => $v->detail{0}->goods_id,
                    ]);
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => $newList
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}