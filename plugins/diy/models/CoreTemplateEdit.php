<?php

namespace app\plugins\diy\models;

use Yii;

/**
 * This is the model class for table "{{%core_template_edit}}".
 *
 * @property int $id
 * @property int $template_id 模板id
 * @property string $name 修改后名称
 * @property string $price 修改后价格
 */
class CoreTemplateEdit extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_template_edit}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id'], 'integer'],
            [['price'], 'number'],
            [['name'], 'string', 'max' => 255],
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
            'name' => '修改后名称',
            'price' => '修改后价格',
        ];
    }
}
