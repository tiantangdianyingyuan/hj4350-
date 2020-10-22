<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%video}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title 标题
 * @property int $type 视频来源 0--源地址 1--腾讯视频
 * @property string $url 链接
 * @property string $content 详情介绍
 * @property int $sort 排序
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $pic_url;
 */
class Video extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'type', 'created_at', 'updated_at', 'deleted_at', 'pic_url'], 'required'],
            [['type', 'sort', 'is_delete', 'mall_id'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'pic_url'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 2048],
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
            'title' => '标题',
            'type' => '视频来源 0--源地址 1--腾讯视频',
            'url' => '链接',
            'content' => '详情介绍',
            'sort' => '排序',
            'pic_url' => '封面图',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
