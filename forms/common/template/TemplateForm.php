<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/2
 * Time: 16:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\Plugin;

abstract class TemplateForm extends Model
{
    public $list;
    public $mall;
    protected $templateInfo;
    private $url; // 只读属性
    private $addUrl;
    private $submitUrl;

    /**
     * @return array
     * 获取默认模板消息设置
     * example：[['name' => '签到提醒(模板编号: AT0264 )','tpl_name' => 'check_in_tpl','check_in_tpl' => '','img_url' => $iconUrlPrefix . 'order_pay_tpl.png','platform' => ['wxapp', 'aliapp']]]
     */
    abstract protected function getDefault();

    /**
     * @return mixed
     * 获取微信、百度小程序模板配置
     * example: ['check_in_tpl' => ['id' => 'AT0264','keyword_id_list' => [0, 1, 5],'title' => '签到提醒'],]
     */
    abstract protected function getTemplateInfo();

    public function rules()
    {
        return [
            [['list'], 'required']
        ];
    }

    /**
     * @return array
     * 调取平台插件接口保存模板消息
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $list = $this->list;
            foreach ($list as $item) {
                $plugin = \Yii::$app->plugin->getPlugin($item['key']);
                if (!$plugin || !isset($item['list'])) {
                    continue;
                }
                if (method_exists($plugin, 'addTemplateList')) {
                    $res = $plugin->addTemplateList($item['list']);
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getDetail($add, $platform = null)
    {
        $list = $this->getList($add, null, $platform);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }

    /**
     * @param bool $add 是否从微信小程序平台拉取模板消息
     * @param null $list
     * @param $platform
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     * 获取所有平台插件下默认或特定的模板消息
     */
    public function getList($add = false, $list = null, $platform = null)
    {
        /* @var Plugin[] $pluginList */
        // 获取所有平台插件
        $pluginList = \Yii::$app->plugin->getAllPlatformPlugins();
        $default = [];
        foreach ($pluginList as $plugin) {
            // 判断平台是否开放模板消息
            if (method_exists($plugin, 'getTemplateList')) {
                $default[] = [
                    'name' => $plugin->getDisplayName(),
                    'key' => $plugin->getName(),
                    'list' => []
                ];
                if ($add && $platform) {
                    // 注：此接口暂时只支持微信,百度
                    if (method_exists($plugin, 'addTemplate') && isset($this->getTemplateInfo()[$plugin->getName()])) {
                        try {
                            $list[$plugin->getName()] = $plugin->addTemplate($this->getTemplateInfo()[$plugin->getName()]);
                        } catch (\Exception $exception) {
                            $list[$plugin->getName()] = $plugin->getTemplateList();
                        }
                    }
                } else {
                    $list[$plugin->getName()] = $plugin->getTemplateList();
                }
            }
        }
        $newDefault = $this->getDefault();
        foreach ($newDefault as $item) {
            foreach ($default as &$value) {
                if (in_array($value['key'], $item['platform'])) {
                    $value['list'][] = $item;
                }
            }
            unset($value);
        }
        foreach ($default as $index => $item) {
            if (count($item['list']) <= 0) {
                unset($default[$index]);
            }
        }
        $default = array_values($default);

        if (!$list) {
            return $default;
        }

        foreach ($default as $k => &$item) {
            foreach ($item['list'] as &$value) {
                if (isset($value['tpl_number'])) {
                    $value['name'] .= $value['tpl_number'][$item['key']];
                }
                if (is_array($value['img_url'])) {
                    $value['img_url'] = $value['img_url'][$item['key']];
                }
            }
            unset($value);

            if (isset($list[$item['key']])) {
                foreach ($item['list'] as $k2 => &$item2) {
                    foreach ($list[$item['key']] as $item3) {
                        if ($item3['tpl_name'] == $item2['tpl_name']) {
                            $item2[$item2['tpl_name']] = $item3['tpl_id'];
                        }
                    }
                }
            }
            unset($item2);
        }
        unset($item);

        return $default;
    }

    public function getUrl()
    {
        return '';
    }

    public function getAddUrl()
    {
        return '';
    }

    public function getSubmitUrl()
    {
        return '';
    }
}
