<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 15:51
 */

namespace app\forms\api\coupon;

use app\forms\common\coupon\CommonCouponList;
use app\models\Model;

class UserCouponForm extends Model
{

    public $status;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['status', 'default', 'value' => 0],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $common = new CommonCouponList($this->attributes);
        $common->user = \Yii::$app->user->identity;
        $res = $common->getUserCouponList()['list'];

        foreach ($res as &$item) {
            if (isset($item['coupon'])) {
                if (isset($item['coupon']['couponCat'])) {
                    unset($item['coupon']['couponCat']);
                }
                if (isset($item['coupon']['couponGood'])) {
                    unset($item['coupon']['couponGood']);
                }
                if ($item['coupon']['appoint_type'] == 1) {
                    $item['coupon']['goods'] = [];
                }
                if ($item['coupon']['appoint_type'] == 2) {
                    $item['coupon']['cat'] = [];
                }
                if ($item['coupon']['appoint_type'] == 3) {
                    $item['coupon']['goods'] = [];
                    $item['coupon']['cat'] = [];
                }
                $item['coupon']['type'] = $item['type'];
                $item['coupon']['discount'] = $item['discount'];
                $item['coupon']['sub_price'] = $item['sub_price'];
            }
            $item['start_time'] = new_date($item['start_time']);
            $item['end_time'] = new_date($item['end_time']);
        }
        unset($item);
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $res
            ]
        ];
    }
}
