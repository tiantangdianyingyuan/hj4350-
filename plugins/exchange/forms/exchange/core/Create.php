<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\core;

use app\models\Model;
use app\models\User;
use app\plugins\exchange\forms\exchange\CreatdCodeLog;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeLibrary;

class Create extends Model
{
    public function start(ExchangeCode &$codeModel, ExchangeLibrary $libraryModel, User $user, $token, $code, $extra_info)
    {
        $rewards = json_decode($libraryModel->rewards, true);
        $r_rewards = [];
        foreach ($rewards as $reward) {
            //TYPE 合法性检测
            if (!isset($reward['type']) && !isset($reward['token'])) {
                continue;
            }

            $key = array_keys(ExchangeLibrary::defaultType());
            if (!in_array($reward['type'], $key)) {
                continue;
            }
            if ($libraryModel->mode > 0 && $token !== $reward['token']) {
                continue;
            }
            $reward['is_send'] = 0;
            array_push($r_rewards, $reward);
        }
        $codeModel->r_user_id = $user->id;
        $codeModel->r_origin = $extra_info['origin'];
        $codeModel->r_raffled_at = date('Y-m-d H:i:s');
        $codeModel->r_rewards = json_encode($r_rewards, JSON_UNESCAPED_UNICODE);
        $codeModel->status = 2;
        $codeModel->name = $extra_info['name'] ?? '';
        $codeModel->mobile = $extra_info['mobile'] ?? '';
        $codeModel->save();
        $this->getLog($user, $codeModel->r_origin, $code);
    }

    private function getLog($user, $origin, $code)
    {
        $log = new CreatdCodeLog($user->mall_id, $user->id, $origin, $code, 1, '兑换记录成功');
        $log->save();
    }
}
