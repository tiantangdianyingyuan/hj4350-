<?php

namespace app\plugins\gift\models;

use app\models\Option;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%gift_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title
 * @property string $type 玩法
 * @property int $auto_refund 自动退款天数
 * @property int $auto_remind 送礼失败提醒天数
 * @property string $bless_word 祝福语
 * @property string $ask_gift 求礼物
 * @property int $is_share
 * @property int $is_sms
 * @property int $is_mail
 * @property int $is_print
 * @property string $payment_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $poster 海报
 * @property string $background 自定义背景
 * @property string $theme 主题色
 * @property string $send_type
 * @property string $explain 规则说明
 */
class GiftSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'auto_refund', 'auto_remind', 'is_share', 'is_sms', 'is_mail', 'is_print', 'is_delete'], 'integer'],
            [['title', 'bless_word', 'ask_gift', 'payment_type', 'poster', 'theme', 'explain'], 'required'],
            [['payment_type', 'poster', 'theme', 'explain'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'type', 'bless_word', 'ask_gift', 'background', 'send_type'], 'string', 'max' => 200],
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
            'title' => '页面标题',
            'type' => '玩法',
            'auto_refund' => '自动退款时间',
            'auto_remind' => '未领提醒时间',
            'bless_word' => '送礼祝福语',
            'ask_gift' => '求礼物话术',
            'is_share' => 'Is Share',
            'is_sms' => 'Is Sms',
            'is_mail' => 'Is Mail',
            'is_print' => 'Is Print',
            'payment_type' => 'Payment Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'poster' => '海报',
            'background' => '自定义背景',
            'theme' => '主题色',
            'send_type' => 'Send Type',
            'explain' => '规则说明',
        ];
    }

    public static function search()
    {
        $setting = \app\forms\common\CommonOption::get('gift_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = self::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }
        if (!isset($setting['is_full_reduce'])) {
            $setting['is_full_reduce'] = true;
        }
        return $setting;
    }

}
