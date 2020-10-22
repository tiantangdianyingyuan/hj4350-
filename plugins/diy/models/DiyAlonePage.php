<?php

namespace app\plugins\diy\models;

use Yii;

/**
 * This is the model class for table "{{%diy_alone_page}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $type 类型 auth--授权页面
 * @property string $params 参数
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_open 是否显示 0--不显示 1--显示
 */
class DiyAlonePage extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_alone_page}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'is_delete', 'is_open'], 'integer'],
            [['params'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'params' => 'Params',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_open' => 'Is Open',
        ];
    }
}
