<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/2
 * Time: 4:14 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\send_type;

use app\models\Mall;
use app\models\Model;

/**
 * Class SendType
 * @package app\forms\common\send_type
 * @property Mall $mall
 */
class SendType extends Model
{
    public const EXPRESS = 'express';
    public const CITY = 'city';
    public const OFFLINE = 'offline';
    public const NONE = 'none';

    public $mall;

    public function getInfo()
    {
        return [
            self::EXPRESS => '快递配送',
            self::CITY => '同城配送',
            self::OFFLINE => '到店自提',
            self::NONE => '无配送',
        ];
    }

    /**
     * @param $sendType
     * @return array
     */
    public function getNewSendType($sendType)
    {
        $list = [];
        foreach ($sendType as $item) {
            switch ($item) {
                case self::CITY:
                    // 适配旧版没有同城配送的小程序端
                    // 字节跳动小程序不支持同城配送
                    if (
                        version_compare(\Yii::$app->appVersion, '4.1.5', '<')
                        || in_array(\Yii::$app->appPlatform, ['ttapp'])
                    ) {
                        continue;
                    }
                    break;
                default:
                    $list[] = $item;
            }
        }
        // 默认支持快递配送
        if (count($list) == 0) {
            $list[] = 'express';
        }
        return $list;
    }
}
