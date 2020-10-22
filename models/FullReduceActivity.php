<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%full_reduce_activity}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 活动标题
 * @property string $content
 * @property int $status 状态 0下架 1上架
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $start_at
 * @property string $end_at
 * @property int $appoint_type 1:全部商品
 2:全部自营商品
 3:指定商品参加
 4:指定商品不参加
 * @property int $rule_type 1:阶梯满减
 2:循环满减
 * @property string $discount_rule 阶梯满减规则
 * @property string $loop_discount_rule 循环满减规则
 * @property string $appoint_goods
 * @property string $noappoint_goods
 * @property int $is_delete
 */
class FullReduceActivity extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%full_reduce_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'created_at', 'updated_at', 'deleted_at', 'start_at', 'end_at', 'appoint_type', 'rule_type', 'appoint_goods', 'noappoint_goods'], 'required'],
            [['mall_id', 'status', 'appoint_type', 'rule_type', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'start_at', 'end_at'], 'safe'],
            [['appoint_goods', 'noappoint_goods'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['content'], 'string', 'max' => 8192],
            [['discount_rule'], 'string', 'max' => 512],
            [['loop_discount_rule'], 'string', 'max' => 128],
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
            'name' => 'Name',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'appoint_type' => 'Appoint Type',
            'rule_type' => 'Rule Type',
            'discount_rule' => 'Discount Rule',
            'loop_discount_rule' => 'Loop Discount Rule',
            'appoint_goods' => 'Appoint Goods',
            'noappoint_goods' => 'Noappoint Goods',
            'is_delete' => 'Is Delete',
        ];
    }

    /**
     * 获取当前正在进行中的活动
     * @param string $select
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getNowActivity($select = '*')
    {
        return self::find()->where([
              'mall_id' => \Yii::$app->mall->id,
              'is_delete' => 0,
              'status' => 1
          ])->select($select)
            ->andWhere(['<=', 'start_at', date('y-m-d H:i:s')])
            ->andWhere(['>=', 'end_at', date('y-m-d H:i:s')])
            ->limit(1)
            ->one();
    }
}
