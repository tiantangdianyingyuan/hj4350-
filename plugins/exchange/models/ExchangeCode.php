<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;
use app\models\User;

/**
 * This is the model class for table "{{%exchange_code}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $library_id
 * @property int $type 0 后台 1礼品卡
 * @property string $code
 * @property int $status 状态开关 0禁用 1 启用 2 兑换
 * @property string $validity_type
 * @property string $valid_end_time
 * @property string $valid_start_time
 * @property string $created_at
 * @property int $r_user_id
 * @property string $r_raffled_at
 * @property string $r_origin
 * @property string $r_rewards
 * @property string $name
 * @property string $mobile
 */
class ExchangeCode extends ModelActiveRecord
{
    const TYPE_APP = 1;
    const TYPE_ADMIN = 0;
    const ORIGIN_ADMIN = 'admin';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_code}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'library_id', 'code'], 'required'],
            [['mall_id', 'library_id', 'type', 'status', 'r_user_id'], 'integer'],
            [['valid_end_time', 'valid_start_time', 'created_at', 'r_raffled_at'], 'safe'],
            [['r_rewards'], 'string'],
            [['code', 'validity_type', 'name', 'mobile', 'r_origin'], 'string', 'max' => 100],
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
            'library_id' => 'Library ID',
            'type' => '0 后台 1礼品卡',
            'code' => 'Code',
            'status' => '状态开关 0禁用 1 启用 2 兑换 ',
            'validity_type' => 'Validity Type',
            'valid_end_time' => 'Valid End Time',
            'valid_start_time' => 'Valid Start Time',
            'created_at' => 'Created At',
            'r_user_id' => 'R User ID',
            'r_raffled_at' => 'R Raffled At',
            'r_rewards' => 'R Rewards',
            'r_origin' => '兑换来源',
            'name' => '后台联系人',
            'mobile' => '后台手机号码',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'r_user_id']);
    }

    public function getLibrary()
    {
        return $this->hasOne(ExchangeLibrary::className(), ['id' => 'library_id']);
    }
}
