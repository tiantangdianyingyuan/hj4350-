<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/22
 * Time: 11:34
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\api;


use app\core\response\ApiCode;
use app\models\User;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\models\FxhbActivity;

/**
 * @property User $user
 */
class DetailForm extends ApiModel
{
    public $user;

    public $user_activity_id;

    public function rules()
    {
        return [
            [['user_activity_id'], 'required'],
            [['user_activity_id'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonFxhbDb::getCommon($this->mall);
            $userActivityAll = $common->getUserActivityAllById($this->user_activity_id);
            $newList = [];
            $status = 0;
            $isJoin = 0;
            $minPrice = 0;
            $userPrice = 0;
            $max = 0;
            $isMyHongbao = 0;
            $couponTotalMoney = 0;
            $totalUser = 0;
            $resetTime = 0;
            $pageUrl = '';
            foreach ($userActivityAll as $userActivity) {
                /* @var FxhbActivity $data */
                $data = \Yii::$app->serializer->decode($userActivity->data);
                if ($userActivity->parent_id == 0) {
                    $status = $userActivity->status;
                    if ($userActivity->user_id == $this->user->id) {
                        $isMyHongbao = 1;
                    }
                    $couponTotalMoney = $data->count_price;
                    $totalUser = $data->number;
                    $resetTime = $common->getResetTime($userActivity);
                }
                if ($status == 1) {
                    $pageUrl = $data->coupon_type == 4 ? '/plugins/scan_code/index/index' :
                        '/pages/goods/list?coupon_id=' . $userActivity->userCoupon->coupon_id;
                }
                if ($userActivity->user_id == $this->user->id) {
                    $isJoin = 1;
                    $userPrice = $userActivity->get_price;
                }
                $minPrice = $data->least_price;
                $newItem = [
                    'nickname' => $userActivity->user->nickname,
                    'avatar' => $userActivity->user->userInfo->avatar,
                    'get_price' => price_format($userActivity->get_price, 'float', 2),
                    'max' => false,
                ];
                $max = max($max, $newItem['get_price']);
                $newList[] = $newItem;
            }
            array_walk($newList, function (&$value, $key) use ($max) {
                if ($value['get_price'] == $max) {
                    $value['is_best'] = true;
                }
            });

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $newList,
                    'status' => $status,
                    'is_join' => $isJoin,
                    'min_price' => $minPrice,
                    'user_price' => $userPrice,
                    'is_my_hongbao' => $isMyHongbao,
                    'coupon_total_money' => $couponTotalMoney,
                    'user_num' => intval($totalUser - count($userActivityAll)),
                    'reset_time' => $resetTime,
                    'totalUser' => $totalUser,
                    'page_url' => $pageUrl
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
