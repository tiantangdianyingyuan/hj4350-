<?php

namespace app\plugins\lottery\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%lottery_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type 0：分享即送 1： 被分享人参与抽奖
 * @property string $title 小程序标题
 * @property string $rule 规则
 * @property string $created_at
 * @property string $payment_type 支付方式
 * @property string $send_type 发货方式
 * @property string $goods_poster
 * @property int $is_sms 开启短信提醒
 * @property int $is_mail 开启邮件提醒
 * @property int $is_print 开启打印
 * @property int $cs_status 是否开启客服提示
 * @property string $cs_prompt_pic 客服提示图片
 * @property string $cs_wechat 客服微信号
 * @property string $cs_wechat_flock_qrcode_pic
 */
class LotterySetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lottery_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'rule', 'created_at', 'payment_type', 'send_type', 'goods_poster', 'cs_wechat', 'cs_wechat_flock_qrcode_pic'], 'required'],
            [['mall_id', 'type', 'is_sms', 'is_mail', 'is_print', 'cs_status'], 'integer'],
            [['rule', 'payment_type', 'send_type', 'goods_poster', 'cs_wechat', 'cs_wechat_flock_qrcode_pic'], 'string'],
            [['created_at'], 'safe'],
            [['title', 'cs_prompt_pic'], 'string', 'max' => 255],
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
            'type' => '0：分享即送 1： 被分享人参与抽奖',
            'title' => '小程序标题',
            'rule' => '规则',
            'created_at' => 'Created At',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'goods_poster' => 'Goods Poster',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'cs_status' => '是否开启客服提示',
            'cs_prompt_pic' => '客服提示图片',
            'cs_wechat' => '客服微信号',
            'cs_wechat_flock_qrcode_pic' => 'Cs Wechat Flock Qrcode Pic',
        ];
    }
}
