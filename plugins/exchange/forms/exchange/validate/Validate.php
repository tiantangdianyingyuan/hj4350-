<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\validate;

use app\plugins\exchange\forms\exchange\exception\ExchangeException;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeCodeLog;
use yii\db\Exception;

class Validate extends BasicModel
{
    //可加词典
    public function hasImitateUser($extra_info)
    {
        if (!isset($extra_info['name']) || !$extra_info['name']) {
            throw new \Exception('匿名用户收件人不能为空');
        }
        if (!isset($extra_info['mobile']) || !$extra_info['mobile']) {
            throw new \Exception('匿名用户收件号码不能为空');
        }
    }

    public function hasImitate($rewards, $mode, $token)
    {
        if (is_string($rewards)) {
            $rewards = \yii\helpers\BaseJson::decode($rewards);
        }
        foreach ($rewards as $reward) {
            if ($mode > 0 && $token !== $reward['token']) {
                continue;
            }
            if ($reward['type'] === 'goods') {
                continue;
            }
            throw new \Exception('请选择小程序用户');
        }
    }

    public function hasUser()
    {
        if (!$this->user) {
            throw new Exception('用户不存在');
        }
    }

    public function hasLibrary()
    {
        if (!$this->libraryModel || $this->libraryModel->is_delete != 0) {
            throw new ExchangeException('兑换库不存在');
        }
    }
    public function hasDisableLibrary()
    {
        if (!$this->libraryModel || $this->libraryModel->is_recycle != 0) {
            throw new ExchangeException('兑换库已回收');
        }
    }

    public function hasExpireLibrary()
    {
        //if ($this->libraryModel->expire_type === 'all') {}
        if ($this->libraryModel->expire_type === 'fixed') {
            $date = date('Y-m-d H:i:s');
            //|| $this->libraryModel->expire_start_time > $date

            if ($this->libraryModel->expire_end_time < $date) {
                throw new \Exception('兑换码失效');
            }
        }
        if ($this->libraryModel->expire_type === 'relatively') {
            if ($this->libraryModel->expire_start_day < 1) {
                throw new \Exception('兑换码失效');
            }
        }
    }

    public function hasCode()
    {
        if (!$this->codeModel) {
            throw new ExchangeException('该兑换码不存在');
        }
    }

    public function hasDisable()
    {
        if ($this->codeModel->status == 0) {
            throw new ExchangeException('该兑换码已禁用');
        }
    }

    public function hasExchange()
    {
        if (in_array($this->codeModel->status, [2, 3])) {
            throw new ExchangeException('该兑换码已兑换');
        }
    }

    public function hasExchangeUser()
    {
        //参数补充
        if ($this->codeModel->status === 3) {
            throw new ExchangeException('该兑换码已兑换');
        }
        if ($this->codeModel->status !== 2) {
            throw new ExchangeException('该兑换码无法兑换');
        }
        if ($this->codeModel->r_user_id != $this->user->id) {
            throw new ExchangeException('非法用户');
        }
    }

    //send二次兑换
    public function hasExpireBefore()
    {
        if (
            $this->libraryModel->expire_type !== 'all'
            && $this->codeModel->valid_start_time > date('Y-m-d H:i:s')
        ) {
            throw new ExchangeException('该兑换码未到使用时间!');
        }
    }

    public function hasExpireAfter()
    {
        if (
            $this->libraryModel->expire_type !== 'all'
            && $this->codeModel->valid_end_time < date('Y-m-d H:i:s')
        ) {
            throw new ExchangeException('该兑换码已过期');
        }
    }

    public function hasToken($token)
    {
        if (empty($token)) {
            throw new ExchangeException('token不能为空');
        }
    }

    public function hasTokenLegal($rewards, $token, $type = [])
    {
        $sentinel = false;
        if (is_string($rewards)) {
            $rewards = \yii\helpers\BaseJson::decode($rewards);
        }
        foreach ($rewards as $item) {
            if (
                $item['token'] == $token
                &&
                (empty($type) || in_array($item['type'], $type))
            ) {
                $sentinel = true;
            }
        }
        if (!$sentinel) {
            throw new ExchangeException('找不到该奖品');
        }
    }

    public function hasExchangeSetting($setting)
    {
        if ($setting['is_anti_brush']) {
            $key = 'e-code-log-u:' . $this->user->id;
            if ($v = \Yii::$app->cache->get($key)) {
                $s = $v - strtotime("now");
                $hour = floor($s / 3600);
                $minute = floor(($s - 3600 * $hour) / 60);
                $second = floor((($s - 3600 * $hour) - 60 * $minute) % 60);

                $result = '';
                $hour > 0 && $result .= $hour . '时';
                ($hour > 0 || $minute > 0) && $result .= $minute . '分';
                ($hour > 0 || $minute > 0 || $second > 0) && $result .= $second . '秒';

                $text = sprintf('您兑换错误次数太多，%s请在%s后再试', "\n", $result);
                throw new ExchangeException($text);
            }
            $logCount = ExchangeCodeLog::find()->where([
                'AND',
                ['mall_id' => $this->user->mall_id],
                ['user_id' => $this->user->id],
                ['not in', 'origin', ExchangeCode::ORIGIN_ADMIN],
                ['is_success' => 0],
                ['>=', 'created_at', date('Y-m-d H:i:s', strtotime(sprintf('- %s minute', $setting['anti_brush_minute'])))],
            ])->count();
            if (intval($logCount) > $setting['exchange_error']) {
                $second = 60;
                $minute = 60;
                $s = $setting['freeze_hour'] * $second * $minute;
                if ($s) {
                    \Yii::$app->cache->set($key, strtotime(sprintf('+ %s second', $s)), $s);
                } else {
                    \Yii::$app->cache->delete($key);
                }
                $text = sprintf('您兑换错误次数太多，%s请在%s时后再试', "\n", $setting['freeze_hour']);
                throw new ExchangeException($text);
            }
        }

        if ($setting['is_limit']) {
            $logQuery = ExchangeCodeLog::find()->where([
                'AND',
                ['mall_id' => $this->user->mall_id],
                ['user_id' => $this->user->id],
                ['not in', 'origin', ExchangeCode::ORIGIN_ADMIN],
                ['is_success' => 1],
            ]);
            if ($setting['limit_user_type'] === 'all' && intval($logQuery->count()) >= $setting['limit_user_success_num']) {
                throw new ExchangeException('您兑换次数已达上限');
            }
            $logQuery->andWhere([
                'AND',
                ['>=', 'created_at', Date('Y-m-d 0:0:0')],
                ['<=', 'created_at', Date('Y-m-d 0:0:0', strtotime('+1 day'))],
            ]);
            if ($setting['limit_user_type'] === 'day' && intval($logQuery->count()) >= $setting['limit_user_num']) {
                $text = sprintf('您今天的兑换次数已达上限，%s请明天再来兑换', "\n");
                throw new ExchangeException($text);
            }
        }
    }
}
