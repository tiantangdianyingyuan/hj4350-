<?php

namespace app\plugins\pond\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%pond_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title 小程序标题
 * @property int $type 1.天 2 用户
 * @property int $probability 概率
 * @property int $oppty 抽奖次数
 * @property string $start_at 开始时间
 * @property string $end_at 结束时间
 * @property int $deplete_integral_num 消耗积分
 * @property string $rule 规则
 * @property string $created_at
 * @property string $updated_at
 * @property string $payment_type 支付方式
 * @property string $send_type 发货方式
 * @property int $is_sms 开启短信提醒
 * @property int $is_mail 开启邮件提醒
 * @property int $is_print 开启打印
 * @property string $bg_pic 背景图
 * @property string $bg_color 背景颜色
 * @property string $bg_color_type 背景颜色配置
 * @property string $bg_gradient_color 背景渐变颜色
 */
class PondSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'start_at', 'end_at', 'rule', 'created_at', 'updated_at', 'payment_type', 'send_type'], 'required'],
            [['mall_id', 'type', 'probability', 'oppty', 'deplete_integral_num', 'is_sms', 'is_mail', 'is_print'], 'integer'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
            [['rule', 'payment_type', 'send_type'], 'string'],
            [['title', 'bg_pic', 'bg_color', 'bg_color_type', 'bg_gradient_color'], 'string', 'max' => 255],
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
            'title' => '小程序标题',
            'type' => '1.天 2 用户',
            'probability' => '概率',
            'oppty' => '抽奖次数',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'deplete_integral_num' => '消耗积分',
            'rule' => '规则',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'payment_type' => '支付方式',
            'send_type' => '发货方式',
            'is_sms' => '开启短信提醒',
            'is_mail' => '开启邮件提醒',
            'is_print' => '开启打印',
            'bg_pic' => '背景图',
            'bg_color' => '背景颜色',
            'bg_color_type' => '背景颜色类型',
            'bg_gradient_color' => '背景渐变颜色',
        ];
    }
}
