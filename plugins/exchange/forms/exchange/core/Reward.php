<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\core;

use app\models\User;
use app\plugins\exchange\forms\common\CommonResult;
use app\plugins\exchange\forms\exchange\basic\BaseAbstract;
use app\plugins\exchange\forms\exchange\exception\RollBackException;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeLibrary;

class Reward
{
    private function method($type): string
    {
        $className = array_map(function ($item) {
            return ucfirst($item);
        }, explode("_", $type));

        $class = 'app\\plugins\\exchange\\forms\\exchange\\basic\\' . implode($className);
        if (!class_exists($class)) {
            die('CLASS EXISTS问题');
        }
        return $class;
    }


    public function reward(ExchangeCode $codeModel, User $user, $result_token, $token = '', $extra_info = [])
    {
        /** @var ExchangeLibrary $libraryModel */
        $libraryModel = $codeModel->library;
        $rewards = json_decode($codeModel->r_rewards, true);
        $send_num = 0;
        foreach ($rewards as $key => $reward) {
            //未领取
            if ($reward['is_send'] == 1) {
                $send_num++;
                continue;
            }
            //单选处理??????
            if ($token && $reward['token'] !== $token) {
                continue;
            }
            $method = $this->method($reward['type']);

            /** @var BaseAbstract $class */
            $class = new $method($reward, $user, $codeModel, $extra_info);
            if ($class->exchange($message)) {
                $send_num++;
                $rewards[$key]['is_send'] = 1;
            } else {
                $rewards[$key]['is_send'] = 0;
                //后台发放失败 阻断
                if ($extra_info['origin'] === ExchangeCode::ORIGIN_ADMIN) {
                    throw new RollBackException($message, $reward['token']);
                }
                CommonResult::save($result_token, $reward['token'], $message ?: '');
            }
        }

        $mode = $libraryModel->mode;
        if ($mode) {
            $status = $mode <= $send_num ? 3 : 2;
        } else {
            $status = count($rewards) <= $send_num ? 3 : 2;
        }

        $codeModel->r_rewards = json_encode($rewards, JSON_UNESCAPED_UNICODE);
        //dd($rewards);
        $codeModel->status = $status;
        $codeModel->save();
    }
}
