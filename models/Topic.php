<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%topic}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type
 * @property string $title 名称
 * @property string $sub_title 副标题
 * @property string $content 专题内容
 * @property int $layout 布局方式：0=小图，1=大图模式
 * @property int $sort 排序：升序
 * @property int $cover_pic
 * @property int $read_count 阅读量
 * @property int $agree_count 点赞数
 * @property int $virtual_read_count 虚拟阅读量
 * @property int $virtual_agree_count 虚拟点赞数
 * @property int $virtual_favorite_count 虚拟收藏量
 * @property int $is_chosen
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property int $qrcode_pic 自定义分享图片
 * @property int $app_share_title 自定义分享标题
 * @property string $pic_list
 * @property string $detail
 * @property string $abstract 摘要
 * @property TopicType $topicType
 * @property TopicFavorite $favorite
 */
class Topic extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%topic}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'title', 'content', 'cover_pic', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'type', 'layout', 'sort', 'read_count', 'agree_count', 'virtual_read_count', 'virtual_agree_count', 'virtual_favorite_count', 'is_chosen', 'is_delete'], 'integer'],
            [['content', 'pic_list', 'detail'], 'string'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['title', 'sub_title', 'cover_pic', 'qrcode_pic', 'abstract'], 'string', 'max' => 255],
            [['app_share_title'], 'string', 'max' => 65],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'type' => '分类',
            'title' => '名称',
            'sub_title' => '副标题（未用）',
            'content' => '专题内容',
            'layout' => '布局方式：0=小图，1=大图模式',
            'sort' => '排序：升序',
            'cover_pic' => '封面图',
            'read_count' => '阅读量',
            'agree_count' => '点赞数（未用）',
            'virtual_read_count' => '虚拟阅读量',
            'virtual_agree_count' => '虚拟点赞数（未用）',
            'virtual_favorite_count' => '虚拟收藏量',
            'qrcode_pic' => '自定义分享图片',
            'is_chosen' => '是否精选',
            'is_delete' => '删除',
            'app_share_title' => '自定义分享标题',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'pic_list' => '多图模式  图片列表',
            'detail' => '新版专题详情',
            'abstract' => '摘要',
        ];
    }

    public function getTopicType()
    {
        return $this->hasOne(TopicType::className(), ['id' => 'type']);
    }

    public function getFavorite()
    {
        return $this->hasOne(TopicFavorite::className(), ['topic_id' => 'id']);
    }
}
