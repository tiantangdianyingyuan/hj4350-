<?php

namespace app\plugins\stock\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%stock_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $level_id 对应等级表ID
 * @property int $status 0审核中，1同意，2拒绝
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $applyed_at 申请时间
 * @property string $agreed_at 审核时间
 * @property StockUserInfo stockInfo
 * @property User user
 * @property StockLevel level
 */
class StockUser extends \app\models\ModelActiveRecord
{
    public const EVENT_BECOME = 'becomeStock';
    public const EVENT_REMOVE = 'removeStock';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'level_id', 'status', 'is_delete'], 'integer'],
            [['deleted_at', 'created_at', 'updated_at', 'applyed_at', 'agreed_at'], 'safe'],
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
            'level_id' => '对应等级表ID',
            'status' => '0审核中，1同意，2拒绝',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'applyed_at' => '申请时间',
            'agreed_at' => '审核时间',
        ];
    }

    public function getStockInfo()
    {
        return $this->hasone(StockUserInfo::className(), ['user_id' => 'user_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getStatusText($status)
    {
        $text = ['申请中', '通过申请', '拒绝申请'];
        return isset($text[$status]) ? $text[$status] : '未知状态' . $status;
    }

    public function getLevel()
    {
        return $this->hasOne(StockLevel::className(), ['id' => 'level_id']);
    }
}
