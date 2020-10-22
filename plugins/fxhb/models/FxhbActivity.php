<?php

namespace app\plugins\fxhb\models;

use app\models\Goods;
use app\models\GoodsCats;
use Yii;

/**
 * This is the model class for table "{{%fxhb_activity}}".
 *
 * @property int $id
 * @property int $status 是否开启活动：0.关闭|1.开启
 * @property int $type 红包分配方式：1.随机|2.平均
 * @property int $number 拆包人数
 * @property string $count_price 红包总金额
 * @property string $least_price 最低消费金额
 * @property int $effective_time 代金券有效期
 * @property int $open_effective_time 拆红包有效期
 * @property int $coupon_type 代金券使用场景：1.指定分类|2.指定商品|3.全场通用
 * @property int $sponsor_num 该用户可发起活动的次数
 * @property int $help_num 帮拆的次数
 * @property int $sponsor_count 此活动可发红包总次数
 * @property int $sponsor_count_type 次数扣除方式：0.活动成功扣除|1.活动发起就扣除
 * @property string $start_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property string $remark 活动规则
 * @property string $pic_url 活动图片
 * @property string $share_title 分享标题
 * @property string $share_pic_url 分享图片
 * @property string $created_at
 * @property string $updated_at
 * @property int $is_delete
 * @property string $deleted_at
 * @property int $mall_id
 * @property string $name 活动名称
 * @property int $start
 * @property int $end
 * @property GoodsCats[] cats
 * @property Goods[] $goods
 * @property int $is_home_model
 */
class FxhbActivity extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fxhb_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'type', 'number', 'effective_time', 'open_effective_time', 'coupon_type', 'sponsor_num',
                'help_num', 'sponsor_count', 'sponsor_count_type', 'is_delete', 'mall_id'], 'integer'],
            [['number', 'effective_time', 'open_effective_time', 'coupon_type', 'start_time', 'end_time', 'remark',
                'share_title', 'created_at', 'updated_at', 'name'], 'required'],
            [['count_price', 'least_price', 'is_home_model'], 'number'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['remark', 'share_title'], 'string'],
            [['pic_url', 'share_pic_url', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => '是否开启活动：0.关闭|1.开启',
            'type' => '红包分配方式：1.随机|2.平均',
            'number' => '拆包人数',
            'count_price' => '红包总金额',
            'least_price' => '最低消费金额',
            'effective_time' => '代金券有效期',
            'open_effective_time' => '拆红包有效期',
            'coupon_type' => '代金券使用场景：1.指定分类|2.指定商品|3.全场通用',
            'sponsor_num' => '该用户可发起活动的次数',
            'help_num' => '帮拆的次数',
            'sponsor_count' => '此活动可发红包总次数',
            'sponsor_count_type' => '次数扣除方式：0.活动成功扣除|1.活动发起就扣除',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'remark' => '活动规则 ',
            'pic_url' => '活动图片',
            'share_title' => '分享标题',
            'share_pic_url' => '分享图片',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'mall_id' => 'Mall ID',
            'name' => '活动名称',
            'is_home_model' => '首页弹窗开关',
        ];
    }

    public function getCats()
    {
        return $this->hasMany(GoodsCats::className(), ['id' => 'cat_id'])
            ->viaTable(FxhbActivityCatRelation::tableName(), ['activity_id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(), ['id' => 'goods_warehouse_id'])
            ->viaTable(FxhbActivityGoodsRelation::tableName(), ['activity_id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getStart()
    {
        return strtotime($this->start_time);
    }

    public function getEnd()
    {
        return strtotime($this->end_time);
    }

    public static function lock($flag)
    {
        $lockKey = 'fxhb_activity_save_' . Yii::$app->mall->id;
        Yii::$app->cache->set($lockKey, $flag);
    }

    public static function checkLock()
    {
        $lockKey = 'fxhb_activity_save_' . Yii::$app->mall->id;
        return Yii::$app->cache->get($lockKey);
    }
}
