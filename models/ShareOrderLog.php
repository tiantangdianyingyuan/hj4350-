<?php

namespace app\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%share_order_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $share_setting 分销设置情况
 * @property string $order_share_info 订单分销情况
 * @property string $created_at
 */
class ShareOrderLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%share_order_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'share_setting', 'order_share_info', 'created_at'], 'required'],
            [['mall_id'], 'integer'],
            [['share_setting', 'order_share_info'], 'string'],
            [['created_at'], 'safe'],
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
            'share_setting' => '分销设置情况',
            'order_share_info' => '订单分销情况',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $shareSetting array|string 分销设置情况
     * @param $orderShareInfo array|string 订单分销情况
     * @param $mallId integer 商城ID
     * @return bool
     * @throws Exception
     */
    public static function create($shareSetting, $orderShareInfo, $mallId)
    {
        if (!$shareSetting) {
            throw new Exception('$shareSetting不能为空');
        }

        if (!$orderShareInfo) {
            throw new Exception('$shareSetting不能为空');
        }

        if (is_array($shareSetting)) {
            $shareSetting = Yii::$app->serializer->encode($shareSetting);
        }

        if (is_array($orderShareInfo)) {
            $orderShareInfo = Yii::$app->serializer->encode($orderShareInfo);
        }

        $model = new self();
        $model->mall_id = $mallId;
        $model->share_setting = $shareSetting;
        $model->order_share_info = $orderShareInfo;
        $model->created_at = mysql_timestamp();
        return $model->save();
    }
}
