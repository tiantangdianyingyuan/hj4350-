<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange;

use app\plugins\exchange\models\ExchangeCodeLog;
use yii\db\Exception;

//可微调
class CreatdCodeLog
{
    private $remake = '';
    private $code = '';
    private $is_success = '';
    private $origin = '';
    private $user_id = '';
    private $mall_id = '';

    public function __construct(...$params)
    {
        $this->mall_id = current($params);
        $this->user_id = next($params);
        $this->origin = next($params);
        $this->code = next($params);
        $this->is_success = next($params);
        $this->remake = next($params) ?: '';
    }


    public function save()
    {
        $model = new ExchangeCodeLog();
        $model->mall_id = $this->mall_id;
        $model->user_id = $this->user_id;
        $model->origin = $this->origin;
        $model->is_success = $this->is_success;
        $model->code = $this->code;
        $model->remake = $this->remake;
        if (!$model->save()) {
            throw new Exception(isset($model->errors) ? current($model->errors)[0] : '数据异常！');
        }
    }
}