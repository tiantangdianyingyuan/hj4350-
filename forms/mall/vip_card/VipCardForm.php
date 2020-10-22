<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/13
 * Time: 15:10
 */

namespace app\forms\mall\vip_card;

class VipCardForm
{
    public static function check()
    {
        $res['is_vip_card'] = 0;
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!in_array('vip_card', $permission)) {
            return $res;
        }

        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $setting = $plugin->getSetting();
        } catch (\Exception $e) {
            return $res;
        }

        try {
            if (!method_exists($plugin, 'getRules')) {
                throw new \Exception('请升级超级会员卡插件');
            }
            $rules = $plugin->getRules();
        } catch (\Exception $e) {
            return $res;
        }

        $p = \Yii::$app->request->get('plugin', '');
        $rules['rules'][] = 'mall';
        if ($p && !in_array($p, $rules['rules'])) {
            return $res;
        }

        if ($setting['is_vip_card'] == 0) {
            return $res;
        }

        $card = $plugin->getCard();
        if (!$card) {
            return $res;
        }

        $res['is_vip_card'] = 1;
        return $res;
    }
}
