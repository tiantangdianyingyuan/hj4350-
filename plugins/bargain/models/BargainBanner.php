<?php

namespace app\plugins\bargain\models;

use app\models\Banner;
use Yii;

/**
 * This is the model class for table "{{%bargain_banner}}".
 *
 * @property int $id
 * @property int $banner_id
 * @property int $mall_id
 * @property int $is_delete
 * @property string $created_at
 * @property Banner $banner
 */
class BargainBanner extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bargain_banner}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banner_id', 'mall_id', 'is_delete', 'created_at'], 'required'],
            [['banner_id', 'mall_id', 'is_delete'], 'integer'],
            [['created_at'], 'safe'],
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
        ];
    }

    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }
}
