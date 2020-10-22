<?php

namespace app\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%goods_cards}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property string $expire_type
 * @property string $expire_day
 * @property string $begin_time
 * @property string $end_time
 * @property int $total_count 卡券数量
 * @property string $pic_url
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $number
 * @property string $app_share_title
 * @property string $app_share_pic
 * @property int $is_allow_send 是否允许转赠
 */
class GoodsCards extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_cards}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'description', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'expire_type', 'expire_day', 'total_count', 'is_delete', 'number', 'is_allow_send'], 'integer'],
            [['description'], 'string'],
            [['begin_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 65],
            [['pic_url', 'app_share_title', 'app_share_pic'], 'string', 'max' => 255],
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
            'mch_id' => '多商户ID',
            'name' => '卡券名称',
            'expire_type' => '有效期类型',
            'expire_day' => '有效期天数',
            'begin_time' => '有效期区间',
            'end_time' => '有效期区间',
            'pic_url' => '卡券图标',
            'description' => '卡券描述',
            'total_count' => '卡券数量',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'number' => '卡券可核销总次数',
            'app_share_title' => 'App Share Title',
            'app_share_pic' => 'App Share Pic',
            'is_allow_send' => '是否允许转赠',
        ];
    }

    /**
     * @param $num integer 修改的数量
     * @param $type string 增加add|减少sub
     * @param null|integer $cardId 卡券ID
     * @return self|null
     * @throws Exception
     */
    public function updateCount($type, $num, $cardId = null)
    {
        if ($cardId) {
            $card = self::findOne(['id' => $cardId, 'is_delete' => 0]);
        } else {
            $card = $this;
        }
        if (!$card || !$card->id) {
            throw new Exception('错误的卡券信息');
        }

        if ($card->total_count == -1) {
            return $card;
        }
        if ($type === 'add') {
            $card->total_count += $num;
        } elseif ($type === 'sub') {
            if ($card->total_count < $num) {
                throw new Exception('卡券可发放数量不足');
            }
            $card->total_count -= $num;
        } else {
            throw new Exception('错误的$type');
        }
        if ($card->save()) {
            return $card;
        } else {
            throw new Exception($card->errors[0]);
        }
    }
}
