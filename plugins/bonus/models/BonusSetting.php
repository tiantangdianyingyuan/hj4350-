<?php

namespace app\plugins\bonus\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%bonus_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $key
 * @property string $value
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_delete 是否删除 0--未删除 1--已删除
 * @property string $deleted_at 删除时间
 */
class BonusSetting extends \app\models\ModelActiveRecord
{
    const CONDITION = 'condition'; // 成为下线条件
    const PAY_TYPE = 'pay_type'; // 提现方式
    const MIN_MONEY = 'min_money'; // 最少提现金额
    const FREE_CASH_MIN = 'free_cash_min'; //免手续费提现金额区间
    const FREE_CASH_MAX = 'free_cash_max'; //免手续费提现金额区间
    const CASH_SERVICE_CHARGE = 'cash_service_charge'; // 提现手续费
    const AGREE = 'agree'; // 申请协议
    const PAY_TYPE_LIST = ['auto' => '自动打款', 'wechat' => '微信转账', 'alipay' => '支付宝转账',
        'bank' => '银行转账', 'balance' => '提现到余额'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'key', 'value', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete'], 'integer'],
            [['value'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = ['is_bonus', 'bonus_rate', 'condition', 'min_money', 'cash_service_charge', 'free_cash_min', 'free_cash_max', 'become_type', 'is_agreement'];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }

    /**
     * @param $mallId
     * @param $key
     * @param null $default
     * @return \ArrayObject|mixed|null
     */
    public static function get($mallId, $key, $default = null)
    {
        $model = self::findOne(['mall_id' => $mallId, 'key' => $key, 'is_delete' => 0]);
        if (!$model) {
            return $default;
        }
        return self::strToNumber($key, Yii::$app->serializer->decode($model->value));
    }

    /**
     * @param $mallId
     * @return array
     */
    public static function getList($mallId)
    {
        $list = self::find()->where(['mall_id' => $mallId, 'is_delete' => 0])->all();

        $newList = [];
        /* @var self[] $list */
        foreach ($list as $item) {
            $newList[$item->key] = self::strToNumber($item->key, Yii::$app->serializer->decode($item->value));
        }

        return $newList;
    }

    /**
     * @param $mallId
     * @param $key
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public static function set($mallId, $key, $value = '')
    {
        if (empty($key)) {
            return false;
        }
        $model = self::findOne(['mall_id' => $mallId, 'key' => $key, 'is_delete' => 0]);
        if (!$model) {
            $model = new self();
            $model->key = $key;
            $model->mall_id = $mallId;
        }
        $model->value = Yii::$app->serializer->encode(self::strToNumber($key, $value));
        if ($model->save()) {
            return true;
        } else {
            throw new Exception($model->errors[0]);
        }
    }

    /**
     * @param $mallId
     * @param $list
     * @return bool
     * @throws Exception
     */
    public static function setList($mallId, $list)
    {
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $item) {
            self::set(isset($item['mallId']) ? $item['mallId'] : $mallId, $item['key'], $item['value']);
        }
        return true;
    }
}
