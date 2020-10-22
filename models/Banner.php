<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $pic_url 图片
 * @property string $title 标题
 * @property string $page_url 页面路径
 * @property string $open_type 打开方式
 * @property string $params 导航参数
 * @property int $is_delete
 * @property string $created_at 创建时间
 * @property string $deleted_at 删除时间
 * @property string $updated_at 修改时间
 * @property string $sign
 */
class Banner extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%banner}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'is_delete'], 'integer'],
            [['pic_url', 'params', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['params'], 'string'],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['page_url', 'pic_url'], 'string', 'max' => 2048],
            [['open_type', 'sign'], 'string', 'max' => 65],
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
            'pic_url' => '图片',
            'title' => '标题',
            'page_url' => '页面路径',
            'open_type' => '打开方式',
            'params' => '导航参数',
            'is_delete' => 'Is Delete',
            'created_at' => '创建时间',
            'deleted_at' => '删除时间',
            'updated_at' => '修改时间',
            'sign' => '插件标识',
        ];
    }

    public function getMallBanner()
    {
        return $this->hasOne(MallBannerRelation::className(), ['banner_id' => 'id']);
    }
}
