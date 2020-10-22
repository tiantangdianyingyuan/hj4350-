<?php

namespace app\plugins\vip_card\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%vip_card_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $main_id
 * @property int $detail_id
 * @property int $image_type 0:指定商品类别  1:指定商品 2:全场通用
 * @property string $image_type_info
 * @property string $image_discount 折扣
 * @property int $image_is_free_delivery 0:不包邮 1:包邮
 * @property string $image_name 名称
 * @property string $image_main_name 主卡名称
 * @property string $all_send 所有赠送信息
 * @property string $data 额外信息字段
 * @property string $start_time
 * @property string $end_time
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class VipCardUser extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'detail_id', 'image_name', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'detail_id', 'image_type', 'image_is_free_delivery', 'is_delete', 'main_id'], 'integer'],
            [['image_discount'], 'number'],
            [['data', 'all_send'], 'string'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['image_type_info'], 'string', 'max' => 2048],
            [['image_name', 'image_main_name'], 'string', 'max' => 255],
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
            'main_id' => '主卡id',
            'detail_id' => 'Detail Id',
            'image_type' => '0:指定商品类别  1:指定商品 2:全场通用',
            'image_type_info' => 'Image Type Info',
            'image_discount' => '折扣',
            'image_is_free_delivery' => '0:不包邮 1:包邮',
            'image_name' => '名称',
            'image_main_name' => '主卡名称',
            'all_send' => '所有赠送信息',
            'data' => '额外信息字段',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCard()
    {
        return $this->hasOne(VipCardDetail::className(), ['id' => 'detail_id'])->andWhere(['is_delete' => 0]);
    }

    public function getOrder()
    {
        return $this->hasMany(VipCardOrder::className(), ['user_id' => 'user_id']);
    }

    public function getTypeInfo($type)
    {
        $text = ['指定商品', '指定分类', '全场通用'];
        return isset($text[$type]) ? $text[$type] : '未知状态' . $type;
    }
}
