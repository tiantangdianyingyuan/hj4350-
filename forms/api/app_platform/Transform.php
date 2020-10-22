<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/20
 * Time: 15:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\app_platform;


use yii\base\BaseObject;

class Transform extends BaseObject
{
    protected $notSupport = [];

    public static function getInstance()
    {
        $instance = new self();
        try {
            $instance->notSupport = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform)->getNotSupport();
        } catch (\Exception $exception) {
            \Yii::error('平台差异数据调整');
            \Yii::error($exception);
        }
        return $instance;
    }

    /**
     * @param $navbar
     * @return array
     * 底部导航不支持的功能
     */
    public function transformNavbar($navbar)
    {
        if (!empty($this->notSupport) && isset($this->notSupport['navbar']) && !empty($this->notSupport['navbar'])) {
            $newNavs = [];
            foreach ($navbar['navs'] as $nav) {
                if ($nav['open_type'] === 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                    continue;
                }
                if (in_array($nav['url'], $this->notSupport['navbar'])) {
                    continue;
                } else {
                    $newNavs[] = $nav;
                }
            }
            $navbar['navs'] = $newNavs;
        }
        return $navbar;
    }

    /**
     * @param $homeNav
     * @return mixed
     * 导航图标不支持的功能
     */
    public function transformHomeNav($homeNav)
    {
        if (!empty($this->notSupport) && isset($this->notSupport['home_nav']) && !empty($this->notSupport['home_nav'])) {
            $newData = [];
            foreach ($homeNav as $item) {
                if (in_array($item['link_url'], $this->notSupport['home_nav'])) {
                    continue;
                } else {
                    $newData[] = $item;
                }
            }
            $homeNav = $newData;
        }
        return $homeNav;
    }

    /**
     * @param $userCenter
     * @return mixed
     * 用户中心不支持的功能
     */
    public function transformUserCenter($userCenter)
    {
        if (!empty($this->notSupport) && isset($this->notSupport['user_center']) && !empty($this->notSupport['user_center'])) {
            $newData = [];
            foreach ($userCenter as $item) {
                if (in_array($item['link_url'], $this->notSupport['user_center'])) {
                    continue;
                } else {
                    $newData[] = $item;
                }
            }
            $userCenter = $newData;
        }
        return $userCenter;
    }

    public function setNotSupport($list)
    {
        foreach ($list as $key => $item) {
            if (isset($this->notSupport[$key])) {
                $this->notSupport[$key] = array_merge($this->notSupport[$key], $item);
            } else {
                $this->notSupport[$key] = $item;
            }
        }
    }
}
