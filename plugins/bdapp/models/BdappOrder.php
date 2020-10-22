<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/9
 * Time: 10:38
 */

namespace app\plugins\bdapp\models;

use Yii;

/**
 * This is the model class for table "{{%bdapp_order}}".
 *
 * @property int $id
 * @property string $order_no 订单号
 * @property string $bd_user_id 百度用户id
 * @property string $bd_order_id 百度平台订单ID
 * @property string $bd_refund_batch_id 百度平台退款批次号
 * @property int $bd_refund_money
 * @property string $refund_money
 * @property int $is_refund
 * @property string $created_at
 * @property string $updated_at
 */
class BdappOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bdapp_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bd_refund_money', 'is_refund'], 'integer'],
            [['refund_money'], 'number'],
            [['created_at', 'updated_at',], 'required'],
            [['created_at', 'updated_at', 'bd_refund_batch_id',], 'safe'],
            [['order_no', 'bd_user_id', 'bd_order_id', ], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'bd_order_id' => 'Bd Order ID',
            'bd_refund_batch_id' => 'Bd Refund Batch ID',
            'bd_refund_money' => 'Bd Refund Money',
            'refund_money' => 'Refund Money',
            'is_refund' => 'Is Refund',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
