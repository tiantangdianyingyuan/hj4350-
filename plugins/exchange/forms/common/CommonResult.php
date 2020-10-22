<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;

use app\plugins\exchange\models\ExchangeRewardResult;

class CommonResult
{
    public static function save($result_token, $code_token, $message)
    {
        $rewardResult = new ExchangeRewardResult();
        $rewardResult->token = $result_token;
        $rewardResult->code_token = $code_token;
        $rewardResult->data = $message;
        $rewardResult->save();
    }

    public static function get($result_token): array
    {
        $list = ExchangeRewardResult::find()->where([
            'token' => $result_token,
        ])->all();
        $list = array_map(function (ExchangeRewardResult $item) {
            return [
                'token' => $item->code_token,
                'data' => $item->data,
            ];
        }, $list);
        return $list;
    }
}
