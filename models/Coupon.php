<?php

namespace app\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%coupon}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 优惠券名称
 * @property int $type 优惠券类型：1=折扣，2=满减
 * @property string $discount 折扣率
 * @property string $discount_limit 优惠上限
 * @property int $pic_url 未用
 * @property string $desc 未用
 * @property string $min_price 最低消费金额
 * @property string $sub_price 优惠金额
 * @property int $total_count 发放总数量
 * @property int $can_receive_count 可领取数量
 * @property int $sort 排序按升序排列
 * @property int $expire_type 到期类型：1=领取后N天过期，2=指定有效期
 * @property int $expire_day 有效天数，expire_type=1时
 * @property string $begin_time 有效期开始时间
 * @property string $end_time 有效期结束时间
 * @property int $appoint_type 类型
 * @property string $rule 使用说明
 * @property int $is_member 是否指定会员等级购买
 * @property int $is_delete 删除
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $app_share_title
 * @property string $app_share_pic
 * @property GoodsCats[] $cat
 * @property GoodsWarehouse[] $goods
 * @property GoodsWarehouse[] $goodsWarehouse
 * @property $couponCat
 * @property $couponGoods
 * @property string $appointTypeText
 */
class Coupon extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'type', 'min_price', 'sub_price', 'expire_type', 'appoint_type', 'deleted_at', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'type', 'pic_url', 'total_count', 'sort', 'expire_type', 'expire_day', 'appoint_type', 'is_member', 'is_delete', 'can_receive_count'], 'integer'],
            [['discount', 'discount_limit', 'min_price', 'sub_price'], 'number'],
            [['begin_time', 'end_time', 'deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['name', 'app_share_title', 'app_share_pic'], 'string', 'max' => 255],
            [['desc', 'rule'], 'string', 'max' => 2000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '优惠券名称',
            'type' => '优惠券类型：1=折扣，2=满减',
            'discount' => '折扣率',
            'discount_limit' => '优惠上限',
            'pic_url' => '未用',
            'desc' => '未用',
            'min_price' => '最低消费金额',
            'sub_price' => '优惠金额',
            'total_count' => '发放总数量',
            'can_receive_count' => '可领取数量',
            'sort' => '排序按升序排列',
            'expire_type' => '到期类型',
            'expire_day' => '有效天数',
            'appoint_type' => '指定方式',
            'begin_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'rule' => '使用说明',
            'is_member' => '是否指定会员等级领取',
            'is_delete' => '删除',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'app_share_title' => 'App Share Title',
            'app_share_pic' => 'App Share Pic',
        ];
    }

    public function getCat()
    {
        return $this->hasMany(GoodsCats::className(), ['id' => 'cat_id'])->where(['is_delete' => 0])
            ->via('couponCat');
    }

    public function getGoodsWarehouse()
    {
        return $this->hasMany(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('couponGoods');
    }

    public function getGoods()
    {
        return $this->hasMany(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('couponGoods');
    }

    public function getCouponCat()
    {
        return $this->hasMany(CouponCatRelation::className(), ['coupon_id' => 'id'])->where(['is_delete' => 0]);
    }

    public function getCouponGoods()
    {
        return $this->hasMany(CouponGoodsRelation::className(), ['coupon_id' => 'id'])->where(['is_delete' => 0]);
    }

    public function getCouponMember()
    {
        return $this->hasMany(CouponMemberRelation::className(), ['coupon_id' => 'id']);
    }

    public function getCouponCenter()
    {
        return $this->hasOne(CouponCenter::className(), ['coupon_id' => 'id']);
    }

    public function getBeginTime()
    {
        return date('Y-m-d', strtotime($this->begin_time));
    }

    public function getEndTime()
    {
        return date('Y-m-d', strtotime($this->end_time));
    }

    /**
     * @param $num integer 修改的数量
     * @param $type string 增加add|减少sub
     * @param null|integer $id 优惠券ID
     * @return Coupon|null
     * @throws Exception
     */
    public function updateCount($num, $type, $id = null)
    {
        if ($id) {
            $coupon = self::findOne(['id' => $id, 'is_delete' => 0]);
        } else {
            $coupon = $this;
        }
        if (!$coupon || !$coupon->id) {
            throw new Exception('错误的优惠券信息');
        }
        if ($coupon->total_count == -1) {
            return $coupon;
        }
        if ($type === 'add') {
            $coupon->total_count += $num;
        } elseif ($type === 'sub') {
            if ($coupon->total_count < $num) {
                throw new Exception('优惠券库存不足');
            }
            $coupon->total_count -= $num;
        } else {
            throw new Exception('错误的$type');
        }
        if ($coupon->save()) {
            return $coupon;
        } else {
            throw new Exception($coupon->errors[0]);
        }
    }

    public function getAppointTypeText()
    {
        switch ($this->appoint_type) {
            case 1:
                $text = '指定商品';
                break;
            case 2:
                $text = '指定商品类别';
                break;
            case 3:
                $text = '全场通用';
                break;
            case 4:
                $text = '仅限当面付';
                break;
            default:
                $text = '';
        }
        return $text;
    }
}
