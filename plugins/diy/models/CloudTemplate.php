<?php

namespace app\plugins\diy\models;

use Yii;

/**
 * This is the model class for table "{{%cloud_template}}".
 *
 * @property int $id 云模板id
 * @property string $name 云模板名称
 * @property string $pics 云模板图片
 * @property string $detail 云模板详情
 * @property string $price 云模板价格
 * @property string $type home:首页布局 diy:DIY模板
 * @property string $version 云模板版本号
 * @property string $package 云模板资源包
 */
class CloudTemplate extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cloud_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['price'], 'number'],
            [['pics', 'detail'], 'required'],
            [['pics', 'detail'], 'string'],
            [['name', 'version', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '云模板ID',
            'name' => '云模板名称',
            'pics' => '云模板图片',
            'detail' => '云模板详情',
            'price' => '云模板价格',
            'type' => 'home:首页布局 diy:DIY模板',
            'version' => '云模板版本号',
            'package' => '云模板资源包'
        ];
    }
}
