<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;

use app\forms\common\coupon\CommonCouponList;
use app\models\Coupon;
use app\models\Model;
use app\models\UserCoupon;
use app\plugins\diy\models\DiyCouponLog;
use yii\db\Query;

class DiyCouponForm extends Model
{
    //领取中心
    public function getCoupons()
    {
        $common = new CommonCouponList();
        $common->user = \Yii::$app->user->identity;
        $common->page = 10;
        $res = $common->getList();
        return $res;
    }

    public function selectIds(array $coupon, array $coupons, $extraInfo)
    {
        $hasHide = $extraInfo['has_hide'] ?? false;
        $sentinel_limit = isset($extraInfo['has_limit']) ? $extraInfo['has_limit'] === 'limit' ? $extraInfo['limit_num'] ?: 0 : 0 : 0;
        $arr = [];
        foreach ($coupon as $key => $item) {
            foreach ($coupons as $item1) {
                if ($item1['id'] == $item['id']) {
                    //$coupon[$key] = $item1;
                    $arr[$key] = $item1;
                }
            }
        }

        $limit_num = 0;
        //$ids = array_column($arr, 'id');
        $data = array_filter($arr, function ($item) use ($hasHide, &$limit_num, $sentinel_limit, $extraInfo) {
            if ($hasHide && $item['is_receive'] > 0) {
                return false;
            }
            if (
                isset($extraInfo['addType'])
                && $extraInfo['addType'] === ''
                && $sentinel_limit
                && $limit_num
                && $sentinel_limit <= $limit_num
            ) {
                return false;
            }
            $limit_num++;
            return true;
        });
        unset($limit_num);
        //uasort($data, function ($current, $next) use ($ids) {
        //    return array_search($current['id'], $ids) <=> array_search($next['id'], $ids);
        //});

        //$rules = array_column($data, 'is_receive');
        //array_multisort($rules, SORT_ASC, $data);
        return array_values($data);
    }

    public function getCouponIds($ids = [], $template_id = 0)
    {
        $user = \Yii::$app->user->identity;
        $mall_id = \Yii::$app->mall->id;
        $userId = $user->id ?? 0;
        $userCouponId = null;
        if ($user) {
            $userCouponId = DiyCouponLog::find()->alias('ucc')
                ->where([
                    'ucc.mall_id' => $mall_id,
                    'ucc.is_delete' => 0,
                    'ucc.user_id' => $userId,
                    'ucc.template_id' => $template_id,
                ])
                        ->select('ucc.user_coupon_id');
        }
        $receiveQuery = UserCoupon::find()->alias('uc')
            ->where(['uc.mall_id' => $mall_id, 'uc.is_delete' => 0, 'uc.user_id' => $userId])
            ->keyword($userCouponId, ['uc.id' => $userCouponId])
            ->select('uc.coupon_id coupon_id');
        $receiveQuery = (new Query())->from(['uc' => $receiveQuery])
            ->where('uc.coupon_id=c.id')
            ->select('count(uc.coupon_id)');
        $query = Coupon::find()->alias('c')
            ->where([
                'c.mall_id' => $mall_id,
                'c.is_delete' => 0,
                'c.id' => $ids,
            ])
            ->andWhere([
                'or',
                [
                    'and',
                    ['c.expire_type' => 2],
                    ['>', 'c.end_time', date('Y-m-d H:i:s')]
                ],
                ['c.expire_type' => 1]
            ])
            ->with(['cat', 'goods'])
            ->select(['c.*', 'is_receive' => $receiveQuery])
            ->orderBy(['is_receive' => SORT_DESC, 'c.sort' => SORT_ASC, 'c.created_at' => SORT_DESC]);
        $list = $query->asArray()->all();
        array_walk($list, function (&$item) {
            $item['page_url'] = '/pages/goods/list?coupon_id=' . $item['id'];
            /** 发放优惠券需要 */
            $item['share_type'] = 4;
            if ($item['appoint_type'] == 4) {
                $item['page_url'] = '/plugins/scan_code/index/index';
            }
        });
        unset($item);
        return $list;
    }
}
