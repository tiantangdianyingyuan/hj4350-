<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $article_cat_id 分类id：1=关于我们，2=服务中心 , 3=拼团
 * @property string $title 标题
 * @property string $content 内容
 * @property int $sort 排序
 * @property int $status 状态
 * @property int $is_delete 删除
 * @property string $deleted_at 删除时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Article extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'article_cat_id', 'sort', 'is_delete', 'status'], 'integer'],
            [['article_cat_id', 'content', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['content'], 'string'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
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
            'article_cat_id' => '分类id：1=关于我们，2=服务中心 , 3=拼团',
            'status' => '状态',
            'title' => '标题',
            'content' => '内容',
            'sort' => '排序',
            'is_delete' => '删除',
            'deleted_at' => '删除时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
