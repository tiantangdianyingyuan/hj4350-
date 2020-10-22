<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/8/20
 * Time: 15:05
 */

namespace app\forms\common\vip_card;

use yii\base\BaseObject;

class CommonVip extends BaseObject
{
    protected static $instance;
    protected $permission;
    protected $plugin;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->permission = $this->getPermission();
        $this->plugin = $this->getPlugin();
    }

    private function getPermission()
    {
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            return false;
        }
        return $permission;
    }

    private function getPlugin()
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            $plugin = false;
        }
        return $plugin;
    }
}
