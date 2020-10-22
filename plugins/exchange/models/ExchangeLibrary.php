<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%exchange_library}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 名称
 * @property string $remark 说明
 * @property string $expire_type all 永久 fixed 固定 relatively相对
 * @property string $expire_start_time 固定开始
 * @property string $expire_end_time 固定开始
 * @property int $expire_start_day 相对开始
 * @property int $expire_end_day 相对结束
 * @property int $mode 0 全部 1 份
 * @property string $code_format english_num, num
 * @property string $rewards 奖励品
 * @property string $rewards_s 奖励品类型 后台搜索使用
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_recycle
 * @property string $recycle_at
 */
class ExchangeLibrary extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_library}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'rewards', 'rewards_s'], 'required'],
            [['mall_id', 'expire_start_day', 'expire_end_day', 'mode', 'is_delete', 'is_recycle'], 'integer'],
            [['remark', 'rewards'], 'string'],
            [['expire_start_time', 'expire_end_time', 'created_at', 'updated_at', 'deleted_at', 'recycle_at'], 'safe'],
            [['name', 'rewards_s'], 'string', 'max' => 255],
            [['expire_type', 'code_format'], 'string', 'max' => 100],
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
            'name' => '名称',
            'remark' => '说明',
            'expire_type' => 'all 永久 fixed 固定 relatively相对',
            'expire_start_time' => '固定开始',
            'expire_end_time' => '固定开始',
            'expire_start_day' => '相对开始',
            'expire_end_day' => '相对结束',
            'mode' => '0 全部 1 份',
            'code_format' => 'english_num, num',
            'rewards' => '奖励品',
            'rewards_s' => '奖励品类型 后台搜索使用',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_recycle' => '是否加入回收站',
            'recycle_at' => 'Recycle At',
        ];
    }

    public static function defaultType()
    {
        $all = [
            'goods' => [
                'name' => '商品',
                'type' => 'goods',
                'goods_id' => 0,
                'goods_num' => 0,
                'attr_id' => 0,
                'token' => '',
            ],
            'coupon' => [
                'name' => '优惠券',
                'type' => 'coupon',
                'coupon_id' => 0,
                'coupon_num' => 0,
                'token' => '',
            ],
            'integral' => [
                'name' => '积分',
                'type' => 'integral',
                'integral_num' => 0,
                'token' => '',
            ],
            'balance' => [
                'name' => '余额',
                'type' => 'balance',
                'balance' => 0,
                'token' => '',
            ],
            'svip' => [
                'name' => '超级会员卡',
                'type' => 'svip',
                'child_id' => '',
                'token' => '',
            ],
            'card' => [
                'name' => '卡券',
                'type' => 'card',
                'card_id' => '',
                'card_num' => 0,
                'token' => '',
            ],
        ];
        try {
            \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            unset($all['svip']);
        }
        return $all;
    }
}
