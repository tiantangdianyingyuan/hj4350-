<?php

namespace app\models;

use app\events\ShareEvent;
use app\handlers\HandlerRegister;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%share}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $name 分销商名称
 * @property string $mobile 分销商手机号
 * @property int $status 用户申请分销商状态0--申请中 1--成功 2--失败
 * @property string $money 可提现佣金
 * @property string $total_money 累计佣金
 * @property string $content 备注
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $apply_at 申请时间
 * @property string $become_at 成为分销商时间
 * @property string $reason 审核原因
 * @property int $first_children 直接下级数量
 * @property int $all_children 所有下级数量
 * @property string $all_money 总佣金数量(包括已发放和未发放且未退款的佣金）
 * @property int $all_order 分销订单数量
 * @property int $level 分销商等级
 * @property string $level_at 成为分销商等级时间
 * @property int $delete_first_show 删除后是否第一次展示
 * @property User $user 分销商用户信息
 * @property UserInfo $userInfo 分销商用户信息
 * @property Order[] $order 分销商订单信息
 * @property UserInfo[] $firstChildren 一级下级
 * @property UserInfo[] $secondChildren 二级下级
 * @property UserInfo[] $thirdChildren 三级下级
 * @property ShareLevel $shareLevel 三级下级
 * @property string $form 自定义表单
 */
class Share extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%share}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'is_delete', 'first_children', 'all_children', 'all_order', 'level',
                'delete_first_show'], 'integer'],
            [['money', 'total_money', 'all_money'], 'number'],
            [['content', 'reason', 'form'], 'string'],
            [['created_at', 'updated_at', 'deleted_at', 'apply_at', 'become_at', 'level_at'], 'safe'],
            [['name', 'mobile'], 'string', 'max' => 255],
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
            'name' => '分销商名称',
            'mobile' => '分销商手机号',
            'status' => '用户申请分销商状态0--申请中 1--成功 2--失败',
            'money' => '可提现佣金',
            'total_money' => '累计佣金',
            'content' => '备注',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'apply_at' => '申请时间',
            'become_at' => '成为分销商时间',
            'reason' => '审核原因',
            'first_children' => '直接下级数量',
            'all_children' => '所有下级数量',
            'all_money' => '总佣金数量(包括已发放和未发放且未退款的佣金）',
            'all_order' => '分销订单数量',
            'level' => '分销商等级',
            'level_at' => '成为分销商等级时间',
            'delete_first_show' => '删除后是否第一次展示',
            'form' => '自定义表单',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    public function getOrder()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'user_id', 'is_delete' => 'is_delete']);
    }

    public function getStatusText($status)
    {
        $text = ['申请中', '成功', '拒绝'];
        return isset($text[$status]) ? $text[$status] : '未知状态' . $status;
    }

    public function getFirstChildren()
    {
        return $this->hasMany(UserInfo::className(), ['parent_id' => 'user_id']);
    }

    public function getSecondChildren()
    {
        return $this->hasMany(UserInfo::className(), ['parent_id' => 'user_id'])
            ->via('firstChildren');
    }

    public function getThirdChildren()
    {
        return $this->hasMany(UserInfo::className(), ['parent_id' => 'user_id'])
            ->via('secondChildren');
    }

    public function getShareLevel()
    {
        return $this->hasOne(ShareLevel::className(), ['level' => 'level', 'mall_id' => 'mall_id'])
            ->andWhere(['is_delete' => 0]);
    }
}
