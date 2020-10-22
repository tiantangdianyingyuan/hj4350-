<?php

namespace app\plugins\community\models;

use app\models\User;
use app\models\UserInfo;
use Yii;

/**
 * This is the model class for table "{{%community_middleman}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $money 可提现利润
 * @property string $total_money 累计利润
 * @property int $status 0--申请中 1--通过 2--拒绝 -1--未支付 3--已解除
 * @property string $apply_at 申请时间
 * @property string $become_at 通过审核时间
 * @property int $delete_first_show 删除后是否显示0--不显示 1--显示
 * @property string $reason 审核结果原因
 * @property string $content 备注
 * @property string $name 收货人
 * @property string $mobile 联系电话
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $pay_price 支付的金额
 * @property string $token
 * @property int $pay_type 支付方式
 * @property string $pay_time 支付时间
 * @property string $total_price 销售总额
 * @property int $order_count 订单总数
 * @property CommunityAddress $address
 * @property User $user
 * @property UserInfo $userInfo
 * @property CommunityRelations[] $children
 */
class CommunityMiddleman extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_middleman}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'delete_first_show', 'is_delete', 'pay_type', 'order_count'], 'integer'],
            [['money', 'total_money', 'pay_price', 'total_price'], 'number'],
            [['apply_at', 'become_at', 'created_at', 'updated_at', 'deleted_at', 'pay_time'], 'safe'],
            [['reason', 'content', 'name', 'mobile', 'token'], 'string', 'max' => 255],
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
            'money' => '可提现利润',
            'total_money' => '累计利润',
            'status' => '0--申请中 1--通过 2--拒绝 -1--未支付 3--已解除',
            'apply_at' => '申请时间',
            'become_at' => '通过审核时间',
            'delete_first_show' => '删除后是否显示0--不显示 1--显示',
            'reason' => '审核结果原因',
            'content' => '备注',
            'name' => '收货人',
            'mobile' => '联系电话',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'pay_price' => '支付的金额',
            'token' => 'Token',
            'pay_type' => '支付方式',
            'pay_time' => '支付时间',
            'total_price' => '销售总额',
            'order_count' => '订单总数',
        ];
    }

    public function getAddress()
    {
        return $this->hasOne(CommunityAddress::className(), ['user_id' => 'user_id']);
    }

    public function getStatusText()
    {
        switch ($this->status) {
            case -1:
                $text = '待支付';
                break;
            case 0:
                $text = '待审核';
                break;
            case 1:
                $text = '已通过';
                break;
            case 2:
                $text = '已拒绝';
                break;
            case 3:
                $text = '已解除';
                break;
            default:
                $text = '';
        }
        return $text;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(CommunityRelations::className(), ['middleman_id' => 'user_id']);
    }
}
