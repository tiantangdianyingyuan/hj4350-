<?php

namespace app\plugins\diy\models;

use Yii;

/**
 * This is the model class for table "{{%core_template}}".
 *
 * @property int $id
 * @property int $template_id 模板id
 * @property string $name 模板名称
 * @property string $author 作者
 * @property string $price 价格
 * @property string $pics
 * @property string $data 数据
 * @property string $order_no 订单号
 * @property string $version 版本号
 * @property string $type home--首页布局  diy--DIY模板
 * @property string $detail
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property CoreTemplateEdit $edit
 * @property CoreTemplateType[] $templateType
 */
class CoreTemplate extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['pics', 'data', 'detail', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['pics', 'data', 'detail'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'author', 'order_no', 'version', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => '模板id',
            'name' => '模板名称',
            'author' => '作者',
            'price' => '价格',
            'pics' => 'Pics',
            'data' => '数据',
            'order_no' => '订单号',
            'version' => '版本号',
            'type' => 'home--首页布局  diy--DIY模板',
            'detail' => 'Detail',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getEdit()
    {
        return $this->hasOne(CoreTemplateEdit::className(), ['template_id' => 'template_id']);
    }

    public function getTemplateType()
    {
        return $this->hasMany(CoreTemplateType::className(), ['template_id' => 'template_id']);
    }
}
