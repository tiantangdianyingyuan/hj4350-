<?php

namespace app\plugins\diy\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%diy_page_nav}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $page_id
 * @property string $name
 * @property int $template_id
 * @property DiyTemplate $template
 */
class DiyPageNav extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_page_nav}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'page_id', 'name', 'template_id'], 'required'],
            [['mall_id', 'page_id', 'template_id'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'page_id' => 'Page ID',
            'name' => 'Name',
            'template_id' => 'Template ID',
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(DiyTemplate::class, ['id' => 'template_id']);
    }
}
