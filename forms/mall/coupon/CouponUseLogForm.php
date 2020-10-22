<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\coupon;

use app\core\response\ApiCode;
use app\forms\mall\export\CouponUseLogExport;
use app\models\Order;
use app\models\User;
use app\plugins\check_in\forms\Model;

class CouponUseLogForm extends Model
{
    public $page;
    public $keyword;
    public $time;

    public $flag;
    public $fields;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword', 'flag'], 'string'],
            [['time'], 'trim'],
            [['fields'], 'safe'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = Order::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['is_delete' => 0],
                ['is_recycle' => 0],
                ['<>', 'use_user_coupon_id', 0],
            ]);
            if ($this->keyword) {
                $userIds = User::find()->where([
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['is_delete' => 0],
                    ['like', 'nickname', $this->keyword],
                ])->select('id');
                $query->andWhere([
                    'OR',
                    ['user_id' => $userIds],
                    ['like', 'order_no', $this->keyword],
                ]);
            }

            if (!empty($this->time)) {
                $query->andWhere([
                    'AND',
                    ['>=', 'created_at', current($this->time)],
                    ['<=', 'created_at', next($this->time)],
                ]);
            }

            if ($this->flag == "EXPORT") {
                $new_query = clone $query;
                $export = new CouponUseLogExport();
                $export->page = $this->page;
                $export->fieldsKeyList = $this->fields;
                $result = $export->export($new_query);
                return $result;
            }

            $list = $query->with(['user', 'userCoupon'])
                ->orderBy(['id' => SORT_DESC])
                ->page($pagintaion, 50)
                ->all();


            $data = array_map(function ($item) {
                /** @var $Order $item */
                return [
                    'coupon_status' => $item->cancel_status == 1 ? 'cancel' : 'ok',
                    'platform' => $item['user']['userInfo']['platform'],
                    'avatar' => $item['user']['userInfo']['avatar'],
                    'nickname' => $item['user']['nickname'],
                    'user_id' => $item['user']['id'],
                    'order_no' => $item->order_no,
                    'created_at' => $item->created_at,

                    'coupon_type' => $item->userCoupon->type,
                    'coupon_name' => \yii\helpers\BaseJson::decode($item->userCoupon->coupon_data)['name'],
                    'coupon_min_price' => $item->userCoupon->coupon_min_price,
                    'coupon_discount' => $item->userCoupon->discount,
                    'coupon_sub_price' => $item->userCoupon->sub_price,
                    'coupon_discount_limit' => $item->userCoupon->discount_limit,
                ];
            }, $list);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $data,
                    'pagination' => $pagintaion,
                    'export_list' => (new CouponUseLogExport())->fieldsList()
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}