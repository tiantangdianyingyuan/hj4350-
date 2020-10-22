<?php

namespace app\plugins\diy\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%diy_page}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title
 * @property int $show_navs 是否显示导航条：0=不显示，1=显示
 * @property int $is_disable 禁用状态：0=启用，1=禁用
 * @property int $is_home_page 是否是首页0--否 1--是
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property DiyPageNav[] $navs
 * @property DiyTemplate[] $template
 */
class DiyPage extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_page}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'title'], 'required'],
            [['mall_id', 'show_navs', 'is_disable', 'is_home_page', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'mall_id' => 'Mall ID',
            'title' => 'Title',
            'show_navs' => '是否显示导航条：0=不显示，1=显示',
            'is_disable' => '禁用状态：0=启用，1=禁用',
            'is_home_page' => '是否是首页0--否 1--是',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getNavs()
    {
        return $this->hasMany(DiyPageNav::class, ['page_id' => 'id']);
    }

    public function getTemplate()
    {
        return $this->hasMany(DiyTemplate::className(), ['id' => 'template_id'])
            ->via('navs');
    }
    public function getTemplateOne()
    {
        return $this->hasOne(DiyTemplate::className(), ['id' => 'template_id'])
            ->via('navs');
    }
}
