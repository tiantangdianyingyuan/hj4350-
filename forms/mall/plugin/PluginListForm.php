<?php

namespace app\forms\mall\plugin;

use app\core\response\ApiCode;
use app\forms\permission\menu\MenusForm;
use app\plugins\Plugin;

class PluginListForm extends PluginCatBaseForm
{
    public $cat_name;

    public function rules()
    {
        return [
            [['cat_name'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $this->getErrorMsg($this),
            ];
        }
        if ($this->cat_name) {
            $this->searchOtherPlugins = false;
            $this->catCondition = [
                'name' => $this->cat_name,
            ];
        }

        $this->baseSearch();
        if ($this->otherPlugins && count($this->otherPlugins)) {
            $this->cats[] = [
                'name' => 'other',
                'display_name' => '未分组',
                'plugins' => $this->otherPlugins,
            ];
        }
        foreach ($this->cats as $cIndex => &$cat) {
            foreach ($cat['plugins'] as $pIndex => &$plugin) {
                if (!\Yii::$app->role->isSuperAdmin && intval($plugin['is_delete']) === 1) {
                    unset($cat['plugins'][$pIndex]);
                }
                $plugin['show_detail'] = \Yii::$app->role->showDetail;
                $PluginClass = "app\\plugins\\{$plugin['name']}\\Plugin";
                if (class_exists($PluginClass)) {
                    /** @var Plugin $pluginObject */
                    $pluginObject = new $PluginClass();
                    if (!\Yii::$app->role->checkPlugin($pluginObject)) {
                        unset($cat['plugins'][$pIndex]);
                    }

                    $plugin['pic_url'] = $pluginObject->getIconUrl();
                    $plugin['route'] = $this->getPluginIndexRoute($pluginObject);
                    $this->getPluginIndexRoute($pluginObject);
                } elseif (!\Yii::$app->role->isSuperAdmin) {
                    unset($cat['plugins'][$pIndex]);
                }
            }
            if (empty($cat['plugins'])) {
                unset($this->cats[$cIndex]);
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'cats' => $this->cats,
            ],
        ];
    }

    private function getPluginIndexRoute($plugin)
    {
        $form = new MenusForm();
        $form->isExist = true;
        $form->pluginObject = $plugin;
        $res = $form->getMenus('plugin');
        $jumpRoute = '';
        if (isset($res['menus']) && is_array($res['menus']) && count($res['menus'])) {
            $sign = true;
            foreach ($res['menus'] as $key => $value) {
                if (isset($value['route']) && $value['route'] == $plugin->getIndexRoute()) {
                    $jumpRoute = isset($value['is_jump']) && $value['is_jump'] == 0 ? '' : $value['route'];
                    $sign = false;
                    break;
                }
            }
            if ($sign) {
                $jumpRoute = isset($res['menus'][0]['is_jump']) && $res['menus'][0]['is_jump'] == 0 ? '' : (isset($res['menus'][0]['route']) ? $res['menus'][0]['route'] : '');
            }
        }

        return $jumpRoute;
    }
}
