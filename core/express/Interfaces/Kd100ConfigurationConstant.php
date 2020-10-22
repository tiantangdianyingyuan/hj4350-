<?php

namespace app\core\express\Interfaces;

interface Kd100ConfigurationConstant
{
    public const PROVIDER_NAME = 'kd100';

    public const LOGISTICS_COM_CODE_URL = 'http://www.kuaidi100.com/autonumber/auto';     //智能单号识别

    public const SELECT_URL = 'http://poll.kuaidi100.com/poll/query.do';

    public const SUCCESS_STATUS = 200;

    public const Kd100_STATUS_ON_THE_WAY = 0;

    public const Kd100_STATUS_PACKAGE = 1;

    public const Kd100_STATUS_DIFFICULT = 2;

    public const Kd100_STATUS_SIGNING = 3;

    public const Kd100_STATUS_REFUND = 4;

    public const Kd100_STATUS_PIECE = 5;

    public const Kd100_STATUS_RETURN = 6;

    public const Kd100_STATUS_TRANSFER_ORDER = 7;

    public const Kd100_STATUS_RETURN_TO_BE_CLEARED = 10;

    public const Kd100_STATUS_CLEARANCE = 11;

    public const Kd100_STATUS_CLEARED = 12;

    public const Kd100_STATUS_CUSTOMS_CLEARANCE_ABNORMALITY = 13;

    public const Kd100_STATUS_RECIPIENT_REFUSAL = 14;

    const Kd100_STATUS_LABELS = [
        self::Kd100_STATUS_ON_THE_WAY => '在途',
        self::Kd100_STATUS_PACKAGE => '揽件',
        self::Kd100_STATUS_DIFFICULT => '疑难',
        self::Kd100_STATUS_SIGNING => '签收',
        self::Kd100_STATUS_REFUND => '退签',
        self::Kd100_STATUS_PIECE => '派件',
        self::Kd100_STATUS_RETURN => '退回',
        self::Kd100_STATUS_RETURN_TO_BE_CLEARED => '待清关',
        self::Kd100_STATUS_CLEARANCE => '清关中',
        self::Kd100_STATUS_CLEARED => '已清关',
        self::Kd100_STATUS_CUSTOMS_CLEARANCE_ABNORMALITY => '清关异常',
        self::Kd100_STATUS_RECIPIENT_REFUSAL => '收件人拒签',
        self::Kd100_STATUS_TRANSFER_ORDER => '转单',
    ];
}
