<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_comments_templates}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $type 模板类型:1.好评|2.中评|3.差评
 * @property string $title 标题
 * @property string $content 内容
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class OrderCommentsTemplates extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_comments_templates}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'type', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title'], 'string', 'max' => 65],
            [['content'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'type' => '模板类型:1.好评|2.中评|3.差评',
            'title' => '标题',
            'content' => '内容',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
