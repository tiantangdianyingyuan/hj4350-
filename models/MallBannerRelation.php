<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mall_banner_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $banner_id 轮播图id
 * @property string $created_at
 * @property string $deleted_at
 * @property int $is_delete 删除
 * @property Banner $banner
 */
class MallBannerRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall_banner_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'banner_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'banner_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'banner_id' => '轮播图id',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => '删除',
        ];
    }

    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }
}
