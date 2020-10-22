<?php

namespace app\plugins\region\models;

use app\models\User;

/**
 * This is the model class for table "{{%region_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $area_id 区域ID
 * @property int $province_id 所属省
 * @property int $level 1:省代理  2:市代理 3:区代理
 * @property int $status -2被拒或移除后再次申请没提交 -1移除 0审核中，1同意，2拒绝
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $applyed_at 申请时间
 * @property string $agreed_at 审核时间
 * @property RegionUserInfo regionInfo
 * @property RegionArea area
 * @property RegionLevelUp levelUp
 * @property RegionRelation regionRelation
 * @property User user
 */
class RegionUser extends \app\models\ModelActiveRecord
{
    const EVENT_BECOME = 'becomeregion';
    const EVENT_REMOVE = 'removeregion';
    const EVENT_LEVEL_UP = 'levelupregion';

    const STATUS_REAPPLYING = -2;
    const STATUS_REMOVE = -1;
    const STATUS_APPLYING = 0;
    const STATUS_BECOME = 1;
    const STATUS_REJECT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'area_id', 'province_id', 'level', 'status', 'is_delete'], 'integer'],
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
            'area_id' => '区域ID',
            'province_id' => '所属省',
            'level' => '1:省代理  2:市代理 3:区代理',
            'status' => '-2被拒或移除后再次申请没提交 -1移除 0审核中，1同意，2拒绝',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'applyed_at' => '申请时间',
            'agreed_at' => '审核时间',
        ];
    }


    public function getRegionInfo()
    {
        return $this->hasone(RegionUserInfo::className(), ['user_id' => 'user_id']);
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

    public function getArea()
    {
        return $this->hasOne(RegionArea::className(), ['id' => 'area_id']);
    }

    public function getRegionRelation()
    {
        return $this->hasMany(RegionRelation::className(), ['user_id' => 'user_id']);
    }

    public function getLevelUp()
    {
        return $this->hasOne(RegionLevelUp::className(), ['user_id' => 'user_id']);
    }
}
