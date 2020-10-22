<?php

namespace app\plugins\region\models;

use app\models\User;

/**
 * This is the model class for table "{{%region_cash_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $type 类型 1--收入 2--支出
 * @property string $price 变动佣金
 * @property string $desc
 * @property string $custom_desc
 * @property int $level_id 当时的区域等级
 * @property string $level_name
 * @property int $order_num
 * @property string $bonus_rate 当时的分红比例
 * @property int $bonus_id 区域完成分红记录ID
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $province_id
 * @property int $city_id
 * @property int $district_id
 */
class RegionCashLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_cash_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [
                [
                    'mall_id',
                    'user_id',
                    'type',
                    'level_id',
                    'order_num',
                    'bonus_id',
                    'is_delete',
                    'province_id',
                    'city_id',
                    'district_id'
                ],
                'integer'
            ],
            [['price', 'bonus_rate'], 'number'],
            [['desc', 'custom_desc'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['level_name'], 'string', 'max' => 100],
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
            'user_id' => 'User ID',
            'type' => '类型 1--收入 2--支出',
            'price' => '变动佣金',
            'desc' => 'Desc',
            'custom_desc' => 'Custom Desc',
            'level_id' => '当时的区域等级',
            'level_name' => 'Level Name',
            'order_num' => 'Order Num',
            'bonus_rate' => '当时的分红比例',
            'bonus_id' => '区域完成分红记录ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getRegionUser()
    {
        return $this->hasOne(RegionUserInfo::class, ['user_id' => 'user_id']);
    }

    public function getBonus()
    {
        return $this->hasOne(RegionBonusLog::class, ['id' => 'bonus_id']);
    }
}
