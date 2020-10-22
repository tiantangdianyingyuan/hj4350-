<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/6/26
 * Time: 18:42
 */

namespace app\forms\admin;


use app\core\cloud\CloudException;
use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\models\Model;

class PluginUpdateDataForm extends Model
{
    public function search()
    {
        $list = \Yii::$app->plugin->getList();
        foreach ($list as &$item) {
            $item = $item->attributes;
            $item['plugin'] = null;
            $item['icon_url'] = null;
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item['name']);
                $detail = $this->getCloudPluginDetail($plugin->getName(), $plugin->getVersionFileContent());
                $item['plugin'] = [
                    'id' => $detail['id'],
                    'name' => $plugin->getName(),
                    'version' => $plugin->getVersionFileContent(),
                    'new_version' => $detail['new_version'],
                ];
                $item['icon_url'] = $plugin->getIconUrl();
            } catch (ClassNotFoundException $exception) {
            } catch (CloudException $exception) {
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    private function getCloudPluginDetail($name, $localVersion)
    {
        $cacheKey = md5('CLOUD_PLUGIN_DETAIL_OF_' . $name . '_v' . $localVersion);
        $data = \Yii::$app->cache->get($cacheKey);
        if ($data) {
            return $data;
        }
        $data = \Yii::$app->cloud->plugin->getDetail([
            'name' => $name,
            'version' => $localVersion,
        ]);
        \Yii::$app->cache->set($cacheKey, $data, 300);
        return $data;
    }
}