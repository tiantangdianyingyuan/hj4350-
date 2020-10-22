<?php

namespace app\plugins\bargain\models;

use Yii;

/**
 * This is the model class for table "{{%bargain_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $key
 * @property string $value
 * @property string $created_at
 */
class BargainSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bargain_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'key', 'value', 'created_at'], 'required'],
            [['mall_id'], 'integer'],
            [['value'], 'string'],
            [['created_at'], 'safe'],
            [['key'], 'string', 'max' => 255],
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
            'key' => 'Key',
            'value' => 'Value',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return array
     * 砍价基本设置（默认）
     */
    public static function getDefault()
    {
        return [
            'is_share' => Code::CLOSED, // 是否开启分销
            'is_sms' => Code::CLOSED, // 是否开启短信提醒
            'is_mail' => Code::CLOSED, // 是否开启邮件提醒
            'is_print' => Code::CLOSED, // 是否开启打印
            'payment_type' => ['online_pay', 'huodao', 'balance'], // 支付方式
            'send_type' => Code::CLOSED, // 发货方式
            'rule' => '', // 活动规则
            'title' => '', // 活动标题
        ];
    }

    /**
     * @param integer $mallId
     * @param null|array $params
     * @return array
     */
    public function getSetting($mallId, $params = null)
    {
        $setting = self::find()->where(['mall_id' => $mallId])->keyword(!empty($params), ['key' => $params])->all();
        $list = [];
        $default = self::getDefault();
        if ($setting) {
            foreach ($setting as $item) {
                if (isset($item['key'])) {
                    $value = Yii::$app->serializer->decode($item['value']);
                    $list[$item['key']] = is_numeric($value) ? floatval($value) : $value;
                } else {
                    $list[$item['key']] = null;
                }
            }
            foreach ($default as $key => $value) {
                if (!isset($list[$key])) {
                    $list[$key] = $value;
                }
            }
        } else {
            $list = $default;
        }
        if ($params) {
            foreach ($params as $item) {
                if (isset($list[$item])) {
                    continue;
                } elseif (isset($default[$item])) {
                    $list[$item] = $default[$item];
                } else {
                    $list[$item] = null;
                }
            }
        }
        return $list;
    }
}
