<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;

class GoodsListForm extends Model
{
    public $cat_id;
    public $sort;
    public $sort_type;
    public $keyword;
    public $page;
    public $mch_id;
    public $coupon_id;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['mch_id'], 'integer'],
        ];
    }

    public function search()
    {
        try {
            $form = new CommonGoodsList();
            if ($this->coupon_id && is_numeric($this->coupon_id)) {
                $commonCoupon = new CommonCoupon([
                    'mall' => \Yii::$app->mall,
                ], false);
                $commonCoupon->coupon_id = $this->coupon_id;
                $coupon = $commonCoupon->getDetail();
                if ($coupon->appoint_type == 2) {
                    $goodsWarehouseList = $coupon->goods;
                    $goodsWarehouseId = [];
                    foreach ($goodsWarehouseList as $goodsWarehouse) {
                        $goodsWarehouseId[] = $goodsWarehouse->id;
                    }
                    $form->goodsWarehouseId = $goodsWarehouseId;
                } elseif ($coupon->appoint_type == 1) {
                    $catList = $coupon->cat;
                    $this->cat_id = [];
                    foreach ($catList as $cats) {
                        $this->cat_id[] = $cats->id;
                    }
                }
                $form->cat_id = $this->cat_id;
            } else {
                $form->cat_id = is_numeric($this->cat_id) ? $this->cat_id : 0;
            }
            $form->sort = $this->sort;
            $form->status = 1;
            $form->sort_type = $this->sort_type;
            $form->keyword = $this->keyword;
            $form->page = $this->page;
            $form->mch_id = $this->mch_id ?: 0;
            $form->is_array = true;
            $form->mch_id && $this->sign = 'mch';
            $form->sign = $this->sign ? $this->sign : ['mch', ''];
            $form->isSignCondition = true;
            $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
            $form->relations = ['goodsWarehouse', 'mallGoods', 'attr'];
            $list = $form->getList();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
