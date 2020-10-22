<?php

namespace app\plugins\stock\models;

use app\models\Model;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%stock_setting}}".
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
class StockSetting extends \app\models\ModelActiveRecord
{
    const PAY_TYPE = 'pay_type'; // 提现方式
    const MIN_MONEY = 'min_money'; // 最少提现金额
    const FREE_CASH_MIN = 'free_cash_min'; //免手续费提现金额区间
    const FREE_CASH_MAX = 'free_cash_max'; //免手续费提现金额区间
    const CASH_SERVICE_CHARGE = 'cash_service_charge'; // 提现手续费
    const AGREE = 'agree'; // 申请协议
    const PAY_TYPE_LIST = ['auto' => '自动打款', 'wechat' => '微信转账', 'alipay' => '支付宝转账',
        'bank' => '银行转账', 'balance' => '提现到余额'];
    const APPLY_INFO_NEED_VERIFY = 1;
    const APPLY_INFO_NONEED_VERIFY = 2;
    const APPLY_NOINFO_NEED_VERIFY = 3;
    const APPLY_NOINFO_NONEED_VERIFY = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_setting}}';
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

    public static function strToNumber($key, $str)
    {
        $default = ['is_stock', 'stock_rate', 'condition', 'min_money', 'cash_service_charge', 'free_cash_min', 'free_cash_max', 'become_type', 'is_agreement'];
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
        $setList = [];
        //默认数据
        if (empty($list)) {
            $newList = [
                'is_stock' => '0',
                'stock_rate' => '10',
                'base_rate' => '10',
                'apply_type' => '1',
                'become_type' => '1',
                'condition' => '0',
                'is_agreement' => '0',
                'agreement_title' => '',
                'agreement_content' => '',
                'user_instructions' => '',
                'pay_type' => ["balance"],
                'min_money' => '0',
                'cash_service_charge' => '0',
                'free_cash_min' => '0',
                'free_cash_max' => '0',
                'form' => json_encode([
                    'bg_url' => '',
                    'fg_url' => '',
                    'text_1' => '股东分红',
                    'text_2' => '分红比例',
                    'text_3' => '可提现分红',
                    'text_4' => '已提现分红',
                    'text_5' => '累计提现分红',
                    'text_6' => '股东分红',
                    'text_7' => '分红明细',
                    'text_8' => '提现明细',
                    'text_9' => '分红金额',
                ])
            ];
            foreach ($newList as $index => $item) {
                $setList[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            self::setList(Yii::$app->mall->id, $setList);
            //第一次设置的时候，添加默认升级条件
            $model = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
            if (empty($model)) {
                $model = new StockLevelUp();
                $model->mall_id = Yii::$app->mall->id;
                $model->type = 1;
                $model->remark = '股东分红是基于分销商身份新建立起来的一种全新身份——股东。股东分红区别于团队分红的点在于股东分红的订单范围不再局限于某一个关系链的订单，而是全部自营商品的订单。（注意：不适用于多商户）商家设置订单实付金额的一定比例作为订单分红金额，这部分金额将被所有股东瓜分。各股东可瓜分的分红，取决于他的股东等级，等级不同，分红比例不同。分红比例越大的股东等级，股东获得的分红越高。

分红计算细则：
案例:过售后的订单实付金额为100元，订单分红比例为10%，则分红总金额为10元；
等级1股东的股东分红比例为10%，等级1共有2个股东；
等级2股东的股东分红比例为20%，等级2共有5个股东；
等级3股东的股东分红比例为30%，等级3共有10个股东；

等级1每个股东可得：
10%*10元/(10%*2+20%*5+30%*10)=0.24元
等级2每个股东可得：
20%*10元/(10%*2+20%*5+30%*10)=0.48元
等级3每个股东可得：
30%*10元/(10%*2+20%*5+30%*10)=0.71元';
                $model->save();
            }
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
            throw new \Exception((new Model())->getErrorMsg($model));
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
            if ($item['key'] == 'base_rate') {
                //默认等级分红比例
                $model = StockLevel::findOne(['is_default' => 1, 'is_delete' => 0, 'mall_id' => Yii::$app->mall->id]);
                if (empty($model)) {
                    $model = new StockLevel();
                    $model->mall_id = Yii::$app->mall->id;
                    $model->name = '默认等级';
                    $model->is_default = 1;
                }
                $model->bonus_rate = $item['value'];
                $model->save();
            }
        }

        return true;
    }
}
