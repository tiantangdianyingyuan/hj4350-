<?php


namespace app\forms\mall\plugin;


use app\core\response\ApiCode;
use app\models\CorePlugin;
use app\models\Model;
use app\models\PluginCat;
use app\models\PluginCatRel;

class PluginCatSaveForm extends Model
{

    public $cats;

    public function rules()
    {
        return [
            [['cats'], 'string',],
            [['cats'], function ($attribute, $params) {
                $value = $this->$attribute;
                $this->cats = json_decode($value, true);
            },],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return [
                'code' => 1,
                'msg' => $this->getErrorMsg(),
            ];
        }
        $t = \Yii::$app->db->beginTransaction();
        foreach ($this->cats as $cat) {
            if (!$cat['id']) {
                $catModel = new PluginCat();
                $catModel->name = $cat['name'];
            } else {
                $catModel = PluginCat::findOne([
                    'id' => $cat['id'],
                ]);
                if (!$catModel) {
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '分类信息错误，请刷新页面再重试。',
                    ];
                }
                if (intval($cat['is_delete']) === 1) {
                    $catModel->is_delete = 1;
                    if (!$catModel->save()) {
                        $t->rollBack();
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => $this->getErrorMsg($catModel),
                        ];
                    }
                    continue;
                }
            }
            $catModel->display_name = $cat['display_name'];
            $catModel->color = $cat['color'] ? $cat['color'] : '';
            if (!$catModel->save()) {
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $this->getErrorMsg($catModel),
                ];
            }
            foreach ($cat['plugins'] as $plugin) {
                PluginCatRel::deleteAll([
                    'plugin_name' => $plugin['name']
                ]);
                $pluginCatRelModel = new PluginCatRel();
                $pluginCatRelModel->plugin_name = $plugin['name'];
                $pluginCatRelModel->plugin_cat_name = $cat['name'];
                if (!$pluginCatRelModel->save()) {
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $this->getErrorMsg($pluginCatRelModel),
                    ];
                }

                if (isset($plugin['sort'])
                    && is_numeric($plugin['sort'])
                    && $plugin['sort'] > 0
                    && $plugin['sort'] < 10000) {
                    CorePlugin::updateAll(['sort' => $plugin['sort']], ['name' => $plugin['name']]);
                }
            }

        }
        $t->commit();
        return [
            'code' => 0,
            'msg' => '保存成功。',
        ];
    }
}
