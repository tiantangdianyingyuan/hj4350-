<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;
use app\models\Banner;

/**
 * This is the model class for table "{{%step_banner_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $banner_id 轮播图id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class StepBannerRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_banner_relation}}';
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
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }
}
