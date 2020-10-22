<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/16
 * Time: 16:22
 */

namespace app\plugins\bonus\forms\common;

use app\models\Model;
use app\plugins\bonus\models\BonusCaptainLog;

class CommonCaptainLog extends Model
{
    public static function create($event, $user_id, array $content)
    {
        try {
            $mallId = \Yii::$app->mall->id;
        } catch (\Exception $e) {
            $mallId = 0;
        }

        try {
            $handler = \Yii::$app->user->id;
        } catch (\Exception $e) {
            $handler = 0;
        }

        try {
            $log = new BonusCaptainLog();
            $log->mall_id = $mallId;
            $log->handler = $handler;
            $log->user_id = $user_id;
            $log->event = $event;
            $log->content = \Yii::$app->serializer->encode($content);
            $log->create_at = mysql_timestamp();
            $res = $log->save();
            return $res;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            return false;
        }
    }
}