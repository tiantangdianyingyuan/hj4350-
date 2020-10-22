<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\common;


use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Mall;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCoupons;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallCouponsUser;
use app\plugins\integral_mall\models\IntegralMallOrders;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class CouponListForm extends Model
{
    public $mall;
    public $page;
    public $keyword;
    public $limit;

    public function search()
    {

        $res = $this->getCouponList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
            ]
        ];
    }

    public function getCouponList()
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $time = date('Y-m-d H:i:s');
        $query = IntegralMallCoupons::find()->alias('imc')->where([
            'imc.mall_id' => $this->mall->id,
            'imc.is_delete' => 0,
        ])->innerJoin(['c' => Coupon::tableName()], 'c.id=imc.coupon_id')
            ->andWhere([
                'or',
                [
                    'and',
                    ['c.expire_type' => 2],
                    ['>', 'c.end_time', $time]
                ],
                ['c.expire_type' => 1]
            ]);

        if ($this->keyword) {
            $couponIds = Coupon::find()->where(['like', 'name', $this->keyword])->select('id');
            $query->andWhere(['coupon_id' => $couponIds]);
        }

        $list = $query->with('coupon.couponCat', 'coupon.couponGoods', 'couponOrders')
            ->orderBy(['imc.id' => SORT_DESC])
            ->page($pagination, $this->limit)
            ->all();

        $couponUserList = IntegralMallCouponsUser::find()->where([
            'mall_id' => \Yii::$app->mall->id,
        ])->with('userCoupon')->all();

        $newList = [];
        /** @var IntegralMallCoupons $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['coupon'] = $item->coupon ? ArrayHelper::toArray($item->coupon) : [];
            $newItem['coupon']['couponCat'] = isset($item->coupon->couponCat) ? ArrayHelper::toArray($item->coupon->couponCat) : [];
            $newItem['coupon']['couponGoods'] = isset($item->coupon->couponGoods) ? ArrayHelper::toArray($item->coupon->couponGoods) : [];
            $newItem['couponOrders'] = $item->couponOrders ? ArrayHelper::toArray($item->couponOrders) : [];
            $newItem['is_receive'] = 0;
            if ($item->exchange_num != -1 && count($item->couponOrders) >= $item->exchange_num) {
                $newItem['is_receive'] = 1;
            }
            $getNum = 0;
            $useNum = 0;
            /** @var IntegralMallCouponsUser $uItem */
            foreach ($couponUserList as $uItem) {
                if ($item->id == $uItem->integral_mall_coupon_id) {
                    $getNum += 1;
                    if ($uItem->userCoupon->is_use == 1) {
                        // 使用量
                        $useNum += 1;
                    }
                }
            }
            $newItem['get_num'] = $getNum;
            $newItem['not_get_num'] = $item->send_count - $getNum;
            $newItem['use_num'] = $useNum;
            $newItem['page_url'] = '/pages/goods/list?coupon_id=' . $item->id;
            if ($item->coupon->appoint_type == 4) {
                $newItem['page_url'] = '/plugins/scan_code/index/index';
            }
            $newList[] = $newItem;
        }

        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }


    public function getCoupon($id)
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $query = IntegralMallCoupons::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
            'id' => $id
        ]);

        $detail = $query->with('coupon.couponCat', 'coupon.couponGoods')
            ->orderBy('created_at DESC')
            ->asArray()
            ->one();

        return $detail;
    }
}
