<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zjhj_bd_finance".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $order_no 订单号
 * @property string $price 提现金额
 * @property string $service_charge 提现手续费（%）
 * @property string $type 提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额
 * @property string $extra 额外信息 例如微信账号、支付宝账号等
 * @property int $status 提现状态 0--申请 1--同意 2--已打款 3--驳回
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $content
 * @property string $name 真实姓名
 * @property string $model 提现插件(share,bonus,stock,region,mch)
 * @property int $transfer_status 0.待转账 | 1.已转账 | 2.拒绝转账
 * @property string $phone 手机号
 * @property User $user
 */
class Finance extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%finance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'is_delete', 'transfer_status'], 'integer'],
            [['price', 'service_charge'], 'number'],
            [['extra', 'content'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['order_no', 'type', 'name', 'model', 'phone'], 'string', 'max' => 255],
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
            'order_no' => 'Order No',
            'price' => 'Price',
            'service_charge' => 'Service Charge',
            'type' => 'Type',
            'extra' => 'Extra',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'content' => 'Content',
            'name' => 'Name',
            'model' => 'Model',
            'transfer_status' => 'Transfer Status',
            'phone' => 'Phone',
        ];
    }

    const PAY_TYPE = 'pay_type'; // 提现方式
    const PAY_TYPE_LIST = ['auto' => '自动打款', 'wechat' => '微信线下转账', 'alipay' => '支付宝线下转账',
        'bank' => '银行线下转账', 'balance' => '提现到余额'];
    const CASH_SERVICE_CHARGE = 'cash_service_charge'; // 提现手续费

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getStatusText($status)
    {
        $text = ['申请中', '同意申请，待打款', '已打款', '驳回'];
        return isset($text[$status]) ? $text[$status] : '未知状态' . $status;
    }

    public function getTypeText($type)
    {
        $typeList = [
            'auto' => '自动打款',
            'wechat' => '微信打款',
            'alipay' => '支付宝打款',
            'bank' => '银行转账',
            'balance' => '打款到余额'
        ];
        return isset($typeList[$type]) ? $typeList[$type] : '未知类型：' . $type;
    }

    public function getTypeText2($type)
    {
        $typeList = [
            'auto' => '自动打款',
            'wechat' => '微信钱包',
            'alipay' => '支付宝',
            'bank' => '银行卡',
            'balance' => '余额'
        ];
        return isset($typeList[$type]) ? $typeList[$type] : '未知类型：' . $type;
    }
}
