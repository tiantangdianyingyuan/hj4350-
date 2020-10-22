<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%core_queue_data}}".
 *
 * @property int $id
 * @property int $queue_id 队列返回值
 * @property string $token
 */
class CoreQueueData extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_queue_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id'], 'integer'],
            [['token'], 'required'],
            [['token'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'queue_id' => '队列返回值',
            'token' => 'Token',
        ];
    }

    /**
     * @param $queue_id
     * @param $token
     * 将队列id和token存数据表
     */
    public static function add($queue_id, $token)
    {
        $model = new self();
        $model->queue_id = $queue_id;
        $model->token = $token;
        $model->save();
    }

    /**
     * @param $token
     * @return int|null
     * 根据token获取队列的id
     */
    public static function select($token)
    {
        /* @var self $model*/
        $model = self::find()->where(['token' => $token])->orderBy(['id' => SORT_DESC])->one();
        if (!$model) {
            return null;
        }
        return $model->queue_id;
    }
}
