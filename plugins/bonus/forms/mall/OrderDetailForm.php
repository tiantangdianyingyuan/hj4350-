<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusOrderLog;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends \app\forms\mall\order\OrderDetailForm
{

    public function search()
    {
        $data = parent::search();
        if (!empty($data['data']['order'])) {
            $log = BonusOrderLog::findOne(['order_id' => $data['data']['order']['id']]);
            $cp = BonusCaptain::findOne(['user_id' => $log->to_user_id]);

            $data['data']['order']['bonus_price'] = $log->bonus_price ?? 0;
            $data['data']['order']['bonus_status'] = $log->status ?? 0;
            $data['data']['order']['bonus_remark'] = $log->remark ?? '';

            $data['data']['order']['captain_name'] = $cp->name ?? '';
            $data['data']['order']['captain_mobile'] = $cp->mobile ?? 0;
        }
        return $data;
    }
}
