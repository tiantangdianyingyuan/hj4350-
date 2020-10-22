<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 9:33
 */

namespace app\forms\common\share;


use app\models\Model;
use app\models\ShareCash;
use app\models\ShareSetting;

class CommonShareConfig extends Model
{
    public static function config()
    {
        $time = strtotime(date('Y-m-d'));
        $config = ShareSetting::getDefaultList(\Yii::$app->mall->id);
        if (isset($config[ShareSetting::CASH_MAX_DAY]) && $config[ShareSetting::CASH_MAX_DAY] > -1) {
            $applyCash = ShareCash::find()
                ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'status' => [0, 1, 2]])
                ->andWhere(['>=', 'created_at', date('Y-m-d H:i:s', $time)])
                ->andWhere(['<=', 'created_at', date('Y-m-d H:i:s', $time + 86400)])
                ->sum('price');
            $surplusCash = $config[ShareSetting::CASH_MAX_DAY] - ($applyCash ? $applyCash : 0);
            $config['surplusCash'] = price_format(max($surplusCash, 0));
        }

        return $config;
    }
}
