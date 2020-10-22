<?php

namespace app\plugins\shopping\models;

use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%shopping_likes}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $is_delete
 * @property string $created_at
 */
class ShoppingLikes extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shopping_likes}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'created_at'], 'required'],
            [['mall_id', 'goods_id', 'is_delete'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
