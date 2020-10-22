<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%printer}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $type 类型
 * @property string $name 名称
 * @property string $setting 设置
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Printer extends ModelActiveRecord
{
    const P_360_KDT2 = '360-kdt2';
    const P_YILIANYUN_K4 = 'yilianyun-k4';
    const P_FEIE = 'feie';
    const P_GAINSCHA_GP = 'gainscha-gp';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'name', 'setting', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'mch_id'], 'integer'],
            [['setting'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type', 'name'], 'string', 'max' => 255],
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
            'type' => '类型',
            'name' => '名称',
            'setting' => '设置',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
