<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 16:07
 */

namespace app\forms\api\coupon;


use app\forms\common\coupon\CommonCouponList;
use app\models\Model;

class CouponForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
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
        $res = $common->getList();

        foreach ($res as &$item) {
            if (isset($item['couponCat'])) {
                unset($item['couponCat']);
            }
            if (isset($item['couponGood'])) {
                unset($item['couponGood']);
            }
            if ($item['appoint_type'] == 1) {
                $item['goods'] = [];
            }
            if ($item['appoint_type'] == 2) {
                $item['cat'] = [];
            }
            if ($item['appoint_type'] == 3) {
                $item['goods'] = [];
                $item['cat'] = [];
            }
            $item['begin_time'] = new_date($item['begin_time']);
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