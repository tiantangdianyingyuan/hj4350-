<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $convert_max 每日最高兑换数
 * @property int $convert_ratio 兑换比率
 * @property string $currency_name 活力币别名
 * @property string $activity_pic 活动背景
 * @property string $ranking_pic 排行榜背景
 * @property string $qrcode_pic 海报缩略图
 * @property int $invite_ratio 邀请比率
 * @property string $remind_at 提醒时间
 * @property string $rule 活动规则
 * @property string $activity_rule 活动规则
 * @property int $ranking_num 全国排行限制
 * @property string $title 小程序标题
 * @property string $share_title 转发标题
 * @property string $share_pic 转发图片
 * @property string $qrcode_title 海报文字
 * @property string $created_at
 * @property string $updated_at
 * @property string $payment_type 支付方式
 * @property string $send_type 发货方式
 * @property int $is_share 是否开启分销
 * @property int $is_sms 开启短信提醒
 * @property int $is_mail 开启邮件提醒
 * @property int $is_print 开启打印
 * @property int $is_territorial_limitation 是否开启区域允许购买
 * @property string $goods_poster;
 * @property string $step_poster;
 */
class StepSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'qrcode_pic', 'rule', 'activity_rule', 'created_at', 'updated_at', 'payment_type', 'send_type', 'goods_poster', 'step_poster'], 'required'],
            [['mall_id', 'convert_max', 'convert_ratio', 'invite_ratio', 'ranking_num', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation'], 'integer'],
            [['qrcode_pic', 'rule', 'activity_rule', 'payment_type', 'send_type', 'goods_poster', 'step_poster'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['currency_name', 'activity_pic', 'ranking_pic', 'remind_at', 'title', 'share_title', 'qrcode_title', 'share_pic'], 'string', 'max' => 255],
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
            'convert_max' => '每日最高兑换数',
            'convert_ratio' => '兑换比率',
            'currency_name' => '活力币别名',
            'activity_pic' => '活动背景',
            'ranking_pic' => '排行榜背景',
            'qrcode_pic' => '海报缩略图',
            'invite_ratio' => '邀请比率',
            'remind_at' => '提醒时间',
            'rule' => '活动规则',
            'activity_rule' => '活动规则',
            'ranking_num' => '全国排行限制',
            'title' => '小程序标题',
            'share_title' => '转发标题',
            'share_pic' => '转发图片',
            'qrcode_title' => '海报文字',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'is_share' => '是否开启分销',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'is_territorial_limitation' => '是否开启区域允许购买',
            'goods_poster' => '商品海报',
            'step_poster' => '步数宝海报',
        ];
    }
}
