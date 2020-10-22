<?php

namespace app\models;

use app\forms\common\AppImg;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%share_setting}}".
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
class ShareSetting extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%share_setting}}';
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
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_delete' => '是否删除 0--未删除 1--已删除',
            'deleted_at' => '删除时间',
        ];
    }

    const LEVEL = 'level'; // 分销层级
    const IS_REBATE = 'is_rebate'; // 分销内购
    const PRICE_TYPE = 'price_type'; // 分销佣金类型
    const FIRST = 'first'; // 一级佣金
    const SECOND = 'second'; // 二级佣金
    const THIRD = 'third'; // 三级佣金
    const SHARE_CONDITION = 'share_condition'; // 成为分销商条件
    const CONDITION = 'condition'; // 成为下线条件
    const AUTO_SHARE_VAL = 'auto_share_val'; // 消费自动成为分销商
    const SHARE_GOODS_STATUS = 'share_goods_status'; // 购买商品自动成为分销商
    const SHARE_GOODS_WAREHOUSE_ID = 'share_goods_warehouse_id'; // 需要购买的商品ID
    const PAY_TYPE = 'pay_type'; // 提现方式
    const CASH_MAX_DAY = 'cash_max_day'; // 每日提现上限
    const MIN_MONEY = 'min_money'; // 最少提现金额
    const CASH_SERVICE_CHARGE = 'cash_service_charge'; // 提现手续费
    const AGREE = 'agree'; // 申请协议
    const CONTENT = 'content'; // 用户须知
    const PIC_URL_APPLY = 'pic_url_apply'; // 申请页面背景图片
    const PIC_URL_STATUS = 'pic_url_status'; // 审核页面背景图片
    const PAY_TYPE_LIST = ['auto' => '自动打款', 'wechat' => '微信线下转账', 'alipay' => '支付宝线下转账',
        'bank' => '银行线下转账', 'balance' => '提现到余额'];
    const BECOME_CONDITION = 'become_condition';
    const CAT_LIST = 'cat_list';
    const IS_SHOW_SHARE_LEVEL = 'is_show_share_level'; // 是否显示分销商等级升级入口
    const FORM_STATUS = 'form_status'; // 是否显示自定义表单
    const FORM = 'form'; // 是否显示自定义表单

    public static function getDefaultList($mallId)
    {
        $list = ShareSetting::getList($mallId);
        $default = self::getDefault();

        foreach ($list as $index => &$item) {
            if ($item == '' && isset($default[$index])) {
                $item = $default[$index];
            }
        }

        return $list;
    }

    public static function getDefault()
    {
        $appImg = AppImg::search();
        return [
            'pic_url_apply' => $appImg['share']['apply'],
            'pic_url_status' => $appImg['share']['status']
        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = ['level', 'is_rebate', 'price_type', 'share_condition', 'condition',
            'share_goods_status', 'first', 'second', 'third', 'auto_share_val', 'cash_max_day', 'min_money',
            'cash_service_charge', 'become_condition', 'is_show_share_level', 'form_status'];
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
        if ($key == ShareSetting::SHARE_GOODS_WAREHOUSE_ID && is_numeric($model->value)) {
            $model->value = Yii::$app->serializer->encode([$model->value]);
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
        if (!isset($newList[ShareSetting::BECOME_CONDITION])) {
            $newList[ShareSetting::BECOME_CONDITION] = 3;
            $newList[ShareSetting::SHARE_GOODS_STATUS] = 1;
        }
        if (!isset($newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID])
            || !$newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID]) {
            $newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID] = [];
        } elseif (is_numeric($newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID])) {
            $newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID] = [$newList[ShareSetting::SHARE_GOODS_WAREHOUSE_ID]];
        }

        if (!isset($newList[self::IS_SHOW_SHARE_LEVEL])) {
            $newList[self::IS_SHOW_SHARE_LEVEL] = 1;
        }

        if (!isset($newList[self::LEVEL])) {
            $newList[self::LEVEL] = 0;
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
