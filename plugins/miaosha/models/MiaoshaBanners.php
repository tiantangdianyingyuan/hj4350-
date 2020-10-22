<?php

namespace app\plugins\miaosha\models;

use app\models\Banner;
use Yii;

/**
 * This is the model class for table "{{%miaosha_banners}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $banner_id
 * @property string $created_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class MiaoshaBanners extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%miaosha_banners}}';
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
            'banner_id' => 'Banner ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }
}
