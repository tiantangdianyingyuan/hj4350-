<?php

namespace app\plugins\region\models;

use app\models\User;

/**
 * This is the model class for table "{{%region_user_info}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $phone
 * @property string $all_bonus 累计分红
 * @property string $total_bonus 当前分红
 * @property string $out_bonus 已提现分红
 * @property string $remark 备注
 * @property string $reason 理由
 * @property string $created_at
 * @property string $updated_at
 */
class RegionUserInfo extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_user_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['all_bonus', 'total_bonus', 'out_bonus'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 11],
            [['remark'], 'string', 'max' => 200],
            [['reason'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => '姓名',
            'phone' => '手机号',
            'all_bonus' => '累计分红',
            'total_bonus' => '当前分红',
            'remark' => '备注',
            'reason' => '理由',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
