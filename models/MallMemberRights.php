<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mall_member_rights}}".
 *
 * @property int $id
 * @property int $member_id
 * @property string $title
 * @property string $content
 * @property string $pic_url
 * @property int $is_delete
 */
class MallMemberRights extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall_member_rights}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'is_delete'], 'integer'],
            [['title'], 'string', 'max' => 65],
            [['content', 'pic_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'title' => 'Title',
            'content' => 'Content',
            'pic_url' => 'Pic Url',
            'is_delete' => 'Is Delete',
        ];
    }
}
