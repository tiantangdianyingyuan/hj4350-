<?php

namespace app\plugins\advance\models;

use app\models\Banner;
use Yii;

/**
 * This is the model class for table "{{%advance_banner}}".
 *
 * @property int $id
 * @property int $banner_id
 * @property int $mall_id
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 */
class AdvanceBanner extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_banner}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banner_id', 'mall_id', 'is_delete', 'created_at', 'deleted_at'], 'required'],
            [['banner_id', 'mall_id', 'is_delete'], 'integer'],
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
            'banner_id' => 'Banner ID',
            'mall_id' => 'Mall ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }
}
