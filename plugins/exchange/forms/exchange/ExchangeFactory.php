<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange;

use app\core\express\exception\Exception;
use app\core\response\ApiCode;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\common\CommonResult;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\forms\exchange\exception\ExchangeException;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use app\plugins\exchange\jobs\CovertJob;
use app\plugins\exchange\jobs\ExchangeJob;
use app\plugins\exchange\models\ExchangeCode;

class ExchangeFactory
{
    public $is_anti_brush = 0;//防刷
    public $anti_brush_minute = 0;//N分
    public $exchange_error = 0;//兑换次数
    public $freeze_hour = 0;//冻结小时
    public $is_limit = 0;//限制兑换次数
    public $limit_user_num = 0;//每位用户每天限制兑换成功次数
    public $limit_user_success_num = 0;// 永久兑换成功次数字
    public $limit_user_type = 'day'; //限制方式

    public $code = '';    //验证码
    public $user_id = ''; //用户id
    public $origin = '';//admin app
    public $token = '';
    public $result_token;
    public $extra_info;

    private $user;

    public function __construct($code, $user_id, $token, $origin, $extra_info = [], ...$setting)
    {
        if (empty($setting)) {
            $setting = (new CommonSetting())->get();
        }
        $setting['code'] = $code;
        $setting['user_id'] = $user_id;
        $setting['origin'] = $origin;
        $setting['token'] = $token;
        $setting['extra_info'] = $extra_info;
        $setting['result_token'] = \Yii::$app->security->generateRandomString();

        $class = new \ReflectionClass($this);
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (isset($setting[$property->getName()])) {
                $property->setValue($this, $setting[$property->getName()]);
            }
        }
    }

    //不走队列
    public function showInfo()
    {
        $e = null;
        $data = [];
        try {
            $f = new FacadeAdmin();
            $model = $f->validate;
            if ($this->origin !== ExchangeCode::ORIGIN_ADMIN) {
                $f->user($this->user_id);
                $this->user = $model->user;
                $model->hasExchangeSetting([
                    'is_anti_brush' => $this->is_anti_brush,
                    'anti_brush_minute' => $this->anti_brush_minute,
                    'exchange_error' => $this->exchange_error,
                    'freeze_hour' => $this->freeze_hour,
                    'is_limit' => $this->is_limit,
                    'limit_user_num' => $this->limit_user_num,
                    'limit_user_success_num' => $this->limit_user_success_num,
                    'limit_user_type' => $this->limit_user_type,
                ]);
            }
            $f->admin(\Yii::$app->mall->id, $this->code);
            $mode = intval($model->libraryModel->mode);
            $rewards = CommonModel::getFormatRewards($model->libraryModel->rewards);
            /**
             * 查询购买用户id
             */
            //if (intval($model->codeModel->status) === 1) {
            //    $o = ExchangeOrder::findOne([
            //        'code_id' => $model->codeModel->id,
            //    ]);
            //    $buy_user_id = $o->user_id;
            //}

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'list' => [
                    'mode' => $mode,
                    'rewards' => $rewards
                ]
            ];
        } catch (ExchangeException $e) {
            if ($this->origin !== ExchangeCode::ORIGIN_ADMIN) {
                $log = new CreatdCodeLog(
                    $this->user->mall_id,
                    $this->user->id,
                    $this->origin,
                    $this->code,
                    0,
                    $e->getMessage()
                );
                $log->save();
            }
            //未加词典处理
            if ($e->getMessage() === '该兑换码未到使用时间!') {
                $data['valid_start_time'] = (new \DateTime($model->codeModel->valid_start_time))->format('Y.m.d H:i:s');
                $data['valid_end_time'] = (new \DateTime($model->codeModel->valid_end_time))->format('Y.m.d H:i:s');
            }
        } catch (\Throwable $e) {
        }
        empty($e) and die('未知错误');
        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => $e->getMessage(),
            'data' => $data
        ];
    }

    public function unite()
    {
        try {
            $has_imitate = $this->origin === ExchangeCode::ORIGIN_ADMIN ? !$this->user_id : false;

            $f = new FacadeAdmin();
            $model = $f->validate;
            //$f->hasImitateUser($this->extra_info, $has_imitate);//后台
            $f->user($this->user_id, $has_imitate);

            $this->user = $model->user;
            //小程序 /
            $this->origin === ExchangeCode::ORIGIN_ADMIN || $model->hasExchangeSetting([
                'is_anti_brush' => $this->is_anti_brush,
                'anti_brush_minute' => $this->anti_brush_minute,
                'exchange_error' => $this->exchange_error,
                'freeze_hour' => $this->freeze_hour,
                'is_limit' => $this->is_limit,
                'limit_user_num' => $this->limit_user_num,
                'limit_user_success_num' => $this->limit_user_success_num,
                'limit_user_type' => $this->limit_user_type,
            ]);
            $f->admin(\Yii::$app->mall->id, $this->code);
            $f->imitateUser($model->libraryModel->rewards, $model->libraryModel->mode, $this->token, $has_imitate);//后台
            $model->libraryModel->mode > 0 && $f->token($model->libraryModel->rewards, $this->token);   //简单检测

            $queueId = \Yii::$app->queue->delay(0)->push(new ExchangeJob([
                'user' => $model->user,
                'origin' => $this->origin,
                'code' => $this->code,
                'token' => $this->token,
                'result_token' => $this->result_token,
                'extra_info' => array_merge($this->extra_info, [
                    'has_imitate' => $has_imitate,
                ])
            ]));
            $status = \Yii::$app->queue->isDone($queueId);
            $t1 = microtime(true);
            while (!$status) {
                sleep(0.25);
                $status = \Yii::$app->queue->isDone($queueId);
                $t2 = microtime(true);
                if (round($t2 - $t1, 3) > 10) {
                    throw new \Exception('队列处理失败');
                }
            }
            //核实
            $codeModel = ExchangeCode::find()->where([
                'AND',
                ['code' => $this->code],
                ['in', 'status', [2, 3]]
            ])->one();
            if ($codeModel) {
                return [
                    'msg_info' => (CommonResult::get($this->result_token)),
                ];
            } else {
                if ($this->origin === ExchangeCode::ORIGIN_ADMIN) {
                    $msgInfo = (CommonResult::get($this->result_token));
                    /*
                        筛选相依商品
                        $reward = \yii\helpers\BaseJson::decode($model->libraryModel->rewards);
                        $token = current($msgInfo)['token'];
                        $t = array_column($reward, null, 'token');
                        isset($t[$token]) && $msg = $t[$token]['name'];
                    */
                    if (!empty($msgInfo)) {
                        $msg = current($msgInfo)['data'];
                        throw new \Exception($msg);
                    }
                }
                throw new \Exception('兑换失败');
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function convert()
    {
        try {
            $f = new FacadeAdmin();
            $f->user($this->user_id);
            $f->cover(\Yii::$app->mall->id, $this->code);
            $model = $f->validate;
            $codeModel = $model->codeModel;

            //token检测
            $f->token($codeModel->r_rewards, $this->token);
            $queueId = \Yii::$app->queue->delay(0)->push(new CovertJob([
                'user' => $model->user,
                'code' => $this->code,
                'token' => $this->token,
                'origin' => $this->origin,
                'result_token' => $this->result_token
            ]));
            $status = \Yii::$app->queue->isDone($queueId);
            $t1 = microtime(true);
            while (!$status) {
                sleep(0.25);
                $status = \Yii::$app->queue->isDone($queueId);
                $t2 = microtime(true);
                if (round($t2 - $t1, 3) > 10) {
                    throw new \Exception('队列处理失败');
                }
            }

            //错误只有一种情况
            foreach (CommonResult::get($this->result_token) as $item) {
                if ($this->token === $item['token']) {
                    $msg = $item['data'];
                }
            }
            if (isset($msg)) {
                throw new \Exception($msg);
            }
            return [];
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
