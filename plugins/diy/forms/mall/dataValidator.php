<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;


use yii\validators\Validator;

class dataValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $value = \yii\helpers\BaseJson::decode($value);
        //请输入按钮文字
        $format = [
            'goods' => 'sGoods',
            //'mch' => 'sMch',
            'pintuan' => 'sPintuan',
            'booking' => 'sBooking',
            'miaosha' => 'sMiaosha',
            'bargain' => 'sBargain',
            'integral-mall' => 'sIntegralMall',
            'lottery' => 'sLottery',
            //'quick-nav' => 'sQuickNav',
            'advance' => 'sAdVance',
            'pick' => 'sPick',
            'gift' => 'sGift',
            'flash-sale' => 'sFlashSale',
            'composition' => 'sComposition',
        ];
        $format = array_keys($format);
        foreach ($value as $item) {
            if (isset($item['id']) && in_array($item['id'], $format)) {
                $showBuyBtn = $item['data']['showBuyBtn'] ?: false;
                $buyBtnText = $item['data']['buyBtnText'] ?: '';
                if($showBuyBtn && empty($buyBtnText)){
                    $model->addError($attribute, "购买按钮文字不能为空");
                }
            }
        }
    }
}