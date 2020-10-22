<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftLog;

class GiftForm extends Model
{
    public $gift_id;

    public function rules()
    {
        return [
            [['gift_id'], 'required'],
            [['gift_id'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $gift_info = GiftLog::find()->andWhere([
                'id' => $this->gift_id,
                'is_delete' => 0,
            ])->with('userOrder.user.userInfo')
                ->with('userOrder.giftOrder')
                ->with('winUser.user.userInfo')
                ->with('user.userInfo')
                ->with('sendOrder.detail.goods.goodsWarehouse')
                ->orderBy('created_at desc')
                ->asArray()->one();
            if (empty($gift_info)) {
                throw new \Exception('该礼物不存在');
            }

            $status = 0;//当前用户中奖状态
            $address_status = 0;//当前用户填写地址状态
            $join_status = 0;//当前用户参与状态
            $num = count($gift_info['userOrder']);//人数
            $win_num = count($gift_info['winUser']);//中奖人数
            $open_status = 0;//开奖状态
            $win_time = '0秒';//被领光时间
            $is_gift_null = 0;//是否被领光
            $win_goods_name = '';
            $win_goods_status = 0;

            if ($gift_info['is_confirm'] == 1 || $gift_info['type'] == 'direct_open') {
                $open_status = 1;
            }

            foreach ($gift_info['userOrder'] as $item) {
                if (!\Yii::$app->user->isGuest && $item['user_id'] == \Yii::$app->user->id) {
                    $join_status = 1;
                    //中奖且未转赠
                    if ($item['is_win'] == 1 && $item['is_turn'] == 0) {
                        $status = 1;
                        if ($item['is_receive'] == 0 && $item['giftOrder'][0]['is_refund'] != 1) {
                            $address_status = 1;
                        }
                    }
                }
            }

            //判断商品下架和删除
            foreach ($gift_info['sendOrder'] as $o) {
                foreach ($o['detail'] as $d) {
                    if ($d['goods']['status'] != 1 || $d['goods']['is_delete'] == 1) {
                        $address_status = -1;
                        break;
                    }
                }
            }

            $is_big_gift = 0;//是否是大礼包
            //判断礼包商品数量
            if (count($gift_info['sendOrder']) > 1 || count($gift_info['sendOrder'][0]['detail']) > 1) {
                $is_big_gift = 1;
            }

            if ($gift_info['num'] <= $win_num || ($gift_info['open_type'] == 0 && $win_num == 1)) {
                $starttime = strtotime($gift_info['created_at']);
                $endtime = strtotime($gift_info['updated_at']);
                //计算天数
                $timediff = $endtime - $starttime;
                $days = intval($timediff / 86400);
                //计算小时数
                $remain = $timediff % 86400;
                $hours = intval(($remain / 3600) + ($days * 24));
                //计算分钟数
                $remain = $remain % 3600;
                $mins = intval($remain / 60);
                //计算秒数
                $secs = $remain % 60;
                if ($secs > 0) {
                    $win_time = "{$secs}秒";
                }
                if ($mins > 0) {
                    $win_time = "{$mins}分" . $win_time;
                }
                if ($hours > 0) {
                    $win_time = "{$hours}时" . $win_time;
                }
                $is_gift_null = 1;
                $win_goods_name = '礼物全部被领取';
                $win_goods_status = 1;
            }
            if ($gift_info['num'] > $win_num && $win_num > 0 && $gift_info['open_type'] == 1) {
                $win_goods_name = '礼物部分被领取';
                $win_goods_status = 2;
            }
            if ($win_num == 0 && (
                    ($gift_info['type'] == 'time_open' && strtotime($gift_info['open_time']) < time())
                    || ($gift_info['type'] != 'time_open' && strtotime($gift_info['auto_refund_time']) < time())
                )) {
                $win_goods_name = '礼物送出失败';
                $win_goods_status = 3;
            }
            if (($gift_info['type'] == 'time_open' && strtotime($gift_info['open_time']) > time())
                || ($gift_info['type'] == 'num_open' && count($gift_info['userOrder']) < $gift_info['open_num'])) {
                $win_goods_name = '等待开奖';
                $win_goods_status = 4;
            }
            if ($gift_info['type'] == 'direct_open' && $win_num == 0) {
                $win_goods_name = '等待领取';
                $win_goods_status = 5;
            }

            $refund_time = (CommonGift::getSetting())['auto_refund'] ?? 0;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $gift_info,
                    'num' => $num,
                    'win_num' => $win_num,
                    'win_time' => $win_time,
                    'win_goods_name' => $win_goods_name,
                    'win_goods_status' => $win_goods_status,
                    'refund_time' => $refund_time,
                    'is_gift_null' => $is_gift_null,
                    'status' => $status,
                    'address_status' => $address_status,
                    'join_status' => $join_status,
                    'open_status' => $open_status,
                    'is_big_gift' => $is_big_gift,
                    'setting' => (CommonGift::getSetting())['auto_refund'],
                    'user_id' => \Yii::$app->user->id
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}