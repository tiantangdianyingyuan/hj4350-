<?php

namespace app\plugins\mch\models;

use app\models\Store;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%mch}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $status 是否营业0.否|1.是
 * @property int $is_recommend 好店推荐：0.不推荐|1.推荐
 * @property int $review_status 审核状态：0=待审核，1.审核通过.2=审核不通过
 * @property string $review_remark 审核结果、备注
 * @property string $review_time 审核时间
 * @property string $realname 真实姓名
 * @property string $wechat 微信号
 * @property string $mobile 手机号码
 * @property int $mch_common_cat_id 商户所属类目
 * @property int $transfer_rate 商户手续费
 * @property string $account_money 账户余额
 * @property int $sort 店铺排序|升序
 * @property int $form_data 自定义表单
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property Store $store
 * @property User $mchUser
 * @property User $user
 * @property MchCommonCat $category
 */
class Mch extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mch}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'mch_common_cat_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'status', 'is_recommend', 'review_status', 'mch_common_cat_id', 'transfer_rate',
                'sort', 'is_delete'], 'integer'],
            [['review_time', 'created_at', 'updated_at', 'deleted_at', 'form_data'], 'safe'],
            [['account_money'], 'number'],
            [['review_remark', 'mobile'], 'string', 'max' => 255],
            [['realname', 'wechat'], 'string', 'max' => 65],
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
            'user_id' => 'User ID',
            'status' => '是否营业0.否|1.是',
            'is_recommend' => '好店推荐：0.不推荐|1.推荐',
            'review_status' => '审核状态：0=待审核，1.审核通过.2=审核不通过',
            'review_remark' => '审核结果、备注',
            'review_time' => '审核时间',
            'realname' => '真实姓名',
            'wechat' => '微信号',
            'mobile' => '手机号码',
            'mch_common_cat_id' => '商户所属类目',
            'transfer_rate' => '商户手续费',
            'account_money' => '账户余额',
            'sort' => '店铺排序|升序',
            'form_data' => '自定义表单',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getMchUser()
    {
        return $this->hasOne(User::className(), ['mch_id' => 'id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['mch_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(MchCommonCat::className(), ['id' => 'mch_common_cat_id']);
    }
}
