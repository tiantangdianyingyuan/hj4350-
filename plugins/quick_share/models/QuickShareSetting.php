<?php

namespace app\plugins\quick_share\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%quick_share_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type 发圈对象 仅素材 1全部商品
 * @property string $goods_poster
 * @property string $created_at
 * @property string $updated_at
 */
class QuickShareSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%quick_share_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_poster', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'type'], 'integer'],
            [['goods_poster'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
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
            'type' => '发圈对象 仅素材 1全部商品',
            'goods_poster' => '海报',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
