<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderSubmitForm;
use app\plugins\pond\forms\common\CommonPond;
use app\plugins\pond\models\PondLog;

class PondOrderSubmitForm extends OrderSubmitForm
{
    public $form_data;
    public $pondLog;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
    }

    public function checkGoodsStock($goodsList)
    {
        return true;
    }

    public function checkGoods($goods, $item)
    {
        $pond_id = $this->form_data->list[0]['pond_id'];

        $pondLog = PondLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'status' => 0,
            'id' => $pond_id,
            'type' => 4,
        ])->one();
        if (!$pondLog) {
            throw new OrderException('奖品已过期或不存在');
        }
        $this->pondLog = $pondLog;
    }


    protected function getGoodsItemData($item)
    {
        $itemData = parent::getGoodsItemData($item);
        $itemData['num'] = 1;
        $itemData['forehead_integral'] = 0;
        $itemData['forehead_integral_type'] = 0;
        $itemData['accumulative'] = 0;
        $itemData['pieces'] = 0;
        $itemData['forehead'] = 0;

        $itemData['total_original_price'] = 0;
        $itemData['total_price'] = 0;

        $itemData['discounts'] = [];
        $itemData['is_level_alone'] = 0;
        return $itemData;
    }

    public function getSendType($mchItem)
    {
        $setting = CommonPond::getSetting();
        if ($setting) {
            $sendType = $setting['send_type'];
        } else {
            $sendType = ['express', 'offline'];
        }
        return $sendType;
    }

    public function getToken()
    {
        return $this->pondLog->token ?: parent::getToken();
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
