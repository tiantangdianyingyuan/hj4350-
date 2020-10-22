<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%postage_rules}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property string $detail 规则详情
 * @property int $status 是否默认
 * @property int $type 计费方式【1=>按重计费、2=>按件计费】
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class PostageRules extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%postage_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'detail', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'type', 'is_delete', 'mch_id'], 'integer'],
            [['detail'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 65],
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
            'mch_id' => 'Mch ID',
            'name' => 'Name',
            'detail' => 'Detail',
            'status' => 'Status',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function decodeDetail()
    {
        $detail = Yii::$app->serializer->decode($this->detail);
        foreach ($detail as &$item) {
            foreach ($item as &$value) {
                if (is_numeric($value)) {
                    $value = floatval($value);
                }
            }
            unset($value);
        }
        unset($item);
        return $detail;
    }
}
