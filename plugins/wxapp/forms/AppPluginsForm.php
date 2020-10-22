<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/11
 * Time: 16:49
 */

namespace app\plugins\wxapp\forms;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\plugins\wxapp\models\WxappJumpAppid;

class AppPluginsForm extends Model
{
    public $plugins;

    public function rules()
    {
        return [
            ['plugins', 'each', 'rule' => ['trim']],
            ['plugins', 'each', 'rule' => ['string', 'max' => 128]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'plugins' => '插件',
        ];
    }

    public function search()
    {
        $list = (array)CommonOption::get('wxapp_enable_plugins', \Yii::$app->mall->id, 'plugin', []);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => is_array($list) && count($list) ? $list : [],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->plugins) {
            $this->plugins = [];
        }
        $plugins = [];
        foreach ($this->plugins as $plugin) {
            if (is_string($plugin)) {
                $plugins[] = $plugin;
            }
        }
        CommonOption::set('wxapp_enable_plugins', $plugins, \Yii::$app->mall->id, 'plugin');
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
        ];
    }
}
