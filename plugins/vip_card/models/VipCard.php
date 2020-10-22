<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 会员卡名称
 * @property string $cover 卡片样式
 * @property int $type 0:指定商品类别  1:指定商品 2:全场通用
 * @property string $type_info
 * @property string $discount 折扣
 * @property int $is_discount 0:关闭 1开启
 * @property int $is_free_delivery 0:不包邮 1:包邮
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class VipCard extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'type', 'is_discount', 'is_free_delivery', 'status', 'is_delete'], 'integer'],
            [['discount'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['cover', 'type_info'], 'string', 'max' => 2048],
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
            'name' => 'Name',
            'cover' => 'Cover',
            'type' => 'Type',
            'type_info' => 'Type Info',
            'discount' => 'Discount',
            'is_discount' => 'Is Discount',
            'is_free_delivery' => 'Is Free Delivery',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getDetail()
    {
        return $this->hasMany(VipCardDetail::className(), ['vip_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
