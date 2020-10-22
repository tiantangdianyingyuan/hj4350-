<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_info}}".
 *
 * @property string $id
 * @property int $user_id
 * @property string $avatar 头像
 * @property string $platform_user_id 用户所属平台的用户id
 * @property int $integral 积分
 * @property int $total_integral 最高积分
 * @property string $balance 余额
 * @property string $total_balance 总余额
 * @property int $parent_id 上级id
 * @property int $is_blacklist 是否黑名单
 * @property string $contact_way 联系方式
 * @property string $remark 备注
 * @property int $is_delete
 * @property string $junior_at 成为下级时间
 * @property string $platform 用户所属平台标识
 * @property int $temp_parent_id 临时上级
 * @property string $remark_name 备注名
 * @property User $parent 父级
 * @property UserInfo[] $firstChildren 一级下级
 * @property UserInfo[] $secondChildren 二级下级
 * @property UserInfo[] $thirdChildren 三级下级
 */
class UserInfo extends ModelActiveRecord
{
    const PLATFORM_WXAPP = 'wxapp';
    const PLATFORM_ALIAPP = 'aliapp';
    const PLATFORM_BDAPP = 'bdapp';
    const PLATFORM_TTAPP = 'ttapp';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'integral', 'total_integral', 'parent_id', 'is_blacklist', 'is_delete', 'temp_parent_id'], 'integer'],
            [['balance', 'total_balance'], 'number'],
            [['junior_at'], 'safe'],
            [['avatar', 'platform_user_id', 'contact_way', 'remark', 'platform', 'remark_name'], 'string', 'max' => 255],
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
            'avatar' => '头像',
            'platform_user_id' => '用户所属平台的用户id',
            'integral' => '积分',
            'total_integral' => '最高积分',
            'balance' => '余额',
            'total_balance' => '总余额',
            'parent_id' => '上级id',
            'is_blacklist' => '是否黑名单',
            'contact_way' => '联系方式',
            'remark' => '备注',
            'is_delete' => 'Is Delete',
            'junior_at' => '成为下级时间',
            'platform' => '用户所属平台标识',
            'temp_parent_id' => '临时上级',
            'remark_name' => '备注名',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(User::className(), ['id' => 'parent_id']);
    }

    public function getFirstChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id']);
    }

    public function getSecondChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id'])
            ->via('firstChildren');
    }

    public function getThirdChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'user_id'])
            ->via('secondChildren');
    }

    public function getIdentity()
    {
        return $this->hasOne(UserIdentity::className(), ['user_id' => 'user_id']);
    }

    public static function getPlatformText($platform)
    {
        switch ($platform) {
            case self::PLATFORM_WXAPP:
                $text = '微信';
                break;
            case self::PLATFORM_ALIAPP:
                $text = '支付宝';
                break;
            case self::PLATFORM_BDAPP:
                $text = '百度';
                break;
            case self::PLATFORM_TTAPP:
                $text = '抖音/头条';
                break;
            default:
                $text = '未知';
                break;

        }

        return $text;
    }
}
