<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_services}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 服务名称
 * @property string $pic 商品服务标识
 * @property string $remark 备注、描述
 * @property string $sort
 * @property int $is_default 默认服务
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class GoodsServices extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_services}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'is_default', 'is_delete', 'sort'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 65],
            [['pic'], 'string', 'max' => 2048],
            [['remark'], 'string', 'max' => 255],
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
            'name' => '服务名称',
            'remark' => '备注',
            'sort' => '排序',
            'pic' => '商品服务标识',
            'is_default' => '是否默认',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getNewPic()
    {
        if (empty($this->pic)) {
            return \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
                "/statics/img/mall/goods/guarantee/service-pic.png";
        }
        return $this->pic;
    }
}
