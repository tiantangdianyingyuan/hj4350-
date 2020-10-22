<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 15:22
 */


namespace app\helpers;


class PluginHelper
{
    public static function getPluginBaseAssetsUrl($pluginId = null)
    {
        if (\Yii::$app->request->baseUrl == '/web') {
            $rootUrl = '';
        } else {
            $rootUrl = rtrim(dirname(\Yii::$app->request->baseUrl), '/');
        }
        return \Yii::$app->request->hostInfo . $rootUrl
            . '/plugins/'
            . ($pluginId ? $pluginId : \Yii::$app->controller->module->id) . '/assets';
    }

    public static function getPluginAssetsPath($pluginId = null)
    {
        return \Yii::$app->basePath
            . '/plugins/'
            . ($pluginId ? $pluginId : \Yii::$app->controller->module->id)
            . '/assets';
    }
}
