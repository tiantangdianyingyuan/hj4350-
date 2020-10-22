<?php

namespace app\plugins\fxhb\models;

use Yii;

/**
 * This is the model class for table "{{%fxhb_activity_cat_relation}}".
 *
 * @property int $id
 * @property int $activity_id 活动ID
 * @property int $cat_id 分类
 * @property int $is_delete 删除
 */
class FxhbActivityCatRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fxhb_activity_cat_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'cat_id'], 'required'],
            [['activity_id', 'cat_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动ID',
            'cat_id' => '分类',
            'is_delete' => '删除',
        ];
    }
}
