<?php

namespace app\plugins\check_in\models;

use Yii;

/**
 * This is the model class for table "{{%check_in_award_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $number 奖励数量
 * @property int $day 领取奖励的天数
 * @property string $type 奖励类型integral--积分|balance--余额
 * @property int $status 领取类型1--普通签到领取|2--连续签到领取|3--累计签到领取
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class CheckInAwardConfig extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_award_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'day', 'status', 'is_delete'], 'integer'],
            [['number'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'number' => '奖励数量',
            'day' => '领取奖励的天数',
            'type' => '奖励类型integral--积分|balance--余额',
            'status' => '领取类型1--普通签到领取|2--连续签到领取|3--累计签到领取',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getExplain()
    {
        $type = '';
        switch ($this->type) {
            case 'integral':
                $type = '积分';
                break;
            case 'balance':
                $type = '元';
                break;
            default:
        }
        return round($this->number, 2) . $type;
    }
}
