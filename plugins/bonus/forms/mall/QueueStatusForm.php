<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/12
 * Time: 15:53
 */

namespace app\plugins\bonus\forms\mall;

use app\models\Model;

class QueueStatusForm extends Model
{
    public $queue_id;
    public $type;

    public function rules()
    {
        return [
            [['queue_id','type'], 'required'],
            [['queue_id',], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'queue_id' => '队列id',
            'type' => '队列类型'
        ];
    }

    public function status()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!\Yii::$app->queue->isDone($this->queue_id)) {
            return [
                'code' => 0,
                'data' => [
                    'retry' => 1,
                ],
            ];
        }

        $method = $this->type;
        if ($method && method_exists($this, $method)) {
            return $this->$method();
        }

    }

    public function applyStatus()
    {
        return [
            'code' => 0,
            'msg' => '执行成功',
            'data' => ''
        ];
    }

    public function removeStatus()
    {
        return [
            'code' => 0,
            'msg' => '执行成功',
            'data' => ''
        ];
    }
}