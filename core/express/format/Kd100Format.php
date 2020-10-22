<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\format;

use app\core\express\Interfaces\Kd100ConfigurationConstant;
use app\core\express\Interfaces\LogisticsStatus;

class Kd100Format extends BaseFormat implements Kd100ConfigurationConstant, LogisticsStatus
{
    public function injection(array $data)
    {
        $state = $data['state'];
        list($status, $status_text) = $this->claimLogisticsStatus($state);
        return new self([
            self::F_STATE => $state,
            self::F_STSTUS => $status,
            self::F_STSTUS_TEXT => $status_text,
            self::F_STSTUS_LIST => array_map(function ($item) {
                return [
                    self::T_ITEM_DESC => $item['context'],
                    self::T_ITEM_DATETIME => $item['ftime'],
                    self::T_ITEM_MEMO => '',
                ];
            }, $data['data']),
        ]);
    }

    public function claimLogisticsStatus($status)
    {
        switch ($status) {
            case self::Kd100_STATUS_PACKAGE:
                $status = self::LOGISTICS_STATUS_COURIER_RECEIPT;
                break;
            case self::Kd100_STATUS_DIFFICULT:
                $status = self::LOGISTICS_STATUS_TROUBLESOME;
                break;
            case self::Kd100_STATUS_SIGNING:
                $status = self::LOGISTICS_STATUS_SIGNED;
                break;
            case self::Kd100_STATUS_REFUND:
                $status = self::LOGISTICS_STATUS_RETURN_RECEIPT;
                break;
            case self::Kd100_STATUS_PIECE:
                $status = self::LOGISTICS_STATUS_DELIVERING;
                break;
            case self::Kd100_STATUS_RETURN:
                $status = self::LOGISTICS_STATUS_SEND_BACK;
                break;
            case self::Kd100_STATUS_RETURN_TO_BE_CLEARED:
                $status = self::LOGISTICS_STATUS_TO_BE_CLEARED;
                break;
            case self::Kd100_STATUS_CLEARANCE:
                $status = self::LOGISTICS_STATUS_CLEARANCE;
                break;
            case self::Kd100_STATUS_CLEARED:
                $status = self::LOGISTICS_STATUS_CLEARED;
                break;
            case self::Kd100_STATUS_CUSTOMS_CLEARANCE_ABNORMALITY:
                $status = self::LOGISTICS_STATUS_CUSTOMS_CLEARANCE_ABNORMALITY;
                break;
            case self::Kd100_STATUS_RECIPIENT_REFUSAL:
                $status = self::LOGISTICS_STATUS_REJECTED;
                break;
            case self::Kd100_STATUS_ON_THE_WAY:
                $status = self::LOGISTICS_STATUS_IN_TRANSIT;
                break;
            case self::Kd100_STATUS_TRANSFER_ORDER:
                $status = self::LOGISTICS_TRANSFER_ORDER;
                break;
            default:
                $status = self::LOGISTICS_STATUS_ERROR;
                break;
        }
        return [$status, self::LOGISTICS_STATUS_LABELS[$status]];
    }

}