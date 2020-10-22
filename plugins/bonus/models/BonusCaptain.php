<?php

namespace app\plugins\bonus\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%bonus_captain}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 队长姓名
 * @property string $mobile 队长手机
 * @property int $user_id
 * @property string $all_bonus 累计分红
 * @property string $total_bonus 已分红
 * @property string $expect_bonus 预计分红，未到账分红
 * @property string $reason
 * @property string $remark 描述
 * @property string $level 等级
 * @property int $status -1重新申请未提交 0--申请中 1--成功 2--失败 3--处理中
 * @property int $all_member 团员数量
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $apply_at
 * @property int $is_delete
 */
class BonusCaptain extends \app\models\ModelActiveRecord
{
    /** @var string 成为队长 */
    const EVENT_BECOME = 'bonusBecomeCaptain';

    /** @var string 移除队长 */
    const EVENT_REMOVE = 'bonusRemoveCaptain';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_captain}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'all_member', 'is_delete', 'level'], 'integer'],
            [['all_bonus', 'total_bonus', 'expect_bonus'], 'number'],
            [['created_at', 'updated_at', 'deleted_at', 'apply_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 64],
            [['reason', 'remark'], 'string', 'max' => 255],
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
            'name' => '队长姓名',
            'mobile' => '队长手机',
            'user_id' => 'User ID',
            'all_bonus' => '累计分红',
            'total_bonus' => '总分红',
            'expect_bonus' => '预计分红',
            'remark' => '备注',
            'level' => '等级',
            'status' => 'Status',
            'all_member' => '团员数量',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'apply_at' => 'Apply At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLevel()
    {
        return $this->hasOne(BonusMembers::className(), ['id' => 'level'])->andWhere(['is_delete' => 0, 'status' => 1]);
    }

    public function getStatusText($status)
    {
        $text = ['申请中', '通过申请', '拒绝申请'];
        return isset($text[$status]) ? $text[$status] : '未知状态' . $status;
    }
}
