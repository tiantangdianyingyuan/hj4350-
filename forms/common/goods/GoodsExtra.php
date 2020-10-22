<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/16
 * Time: 10:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;


use app\models\Mall;
use app\models\Model;
use app\plugins\Plugin;

/**
 * Class GoodsExtra
 * @package app\forms\common\goods
 * @property Mall $mall
 */
class GoodsExtra extends Model
{
    public $mall;

    public static function getCommon($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance = new self();
        $instance->mall = $mall;
        return $instance;
    }

    /**
     * @param string $type
     * @return null|Plugin
     * 获取商品类型所属的插件
     */
    public function getPlugin($type)
    {
        $plugin = null;
        try {
            switch ($type) {
                case 'ecard':
                    $plugin = \Yii::$app->plugin->getPlugin($type);
                    break;
                default:
                    $plugin = null;
            }
        } catch (\Exception $exception) {
            \Yii::warning('商品类型错误');
            \Yii::warning($type);
        }
        return $plugin;
    }
}
