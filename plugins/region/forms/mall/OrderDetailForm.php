<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\mch\MchSettingForm;
use app\models\Model;
use app\models\OrderRefund;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\region\models\Order;
use app\plugins\region\models\RegionOrder;
use app\plugins\region\models\RegionSetting;

class OrderDetailForm extends \app\forms\mall\order\OrderDetailForm
{
    public function search()
    {
        $data = parent::search();
        if (!empty($data['data']['order'])) {
            //分红数据
            $region_order = RegionOrder::findOne(['order_id' => $data['data']['order']['id']]);
            $setting = RegionSetting::getList(\Yii::$app->mall->id);
            $data['data']['order']['regionOrder']['bonus_rate'] = $region_order->bonus_rate != 0 ? $region_order->bonus_rate : $setting['region_rate'];
            $data['data']['order']['regionOrder']['bonus_price'] = bcmul(
                $region_order->total_pay_price ?? 0,
                $data['data']['order']['regionOrder']['bonus_rate']
            );
        }
        return $data;
    }
}
