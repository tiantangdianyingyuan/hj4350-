<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 11:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\dianqilai;


use app\forms\common\template\TemplateList;
use app\plugins\dianqilai\forms\common\CommonTemplate;
use app\plugins\dianqilai\forms\TemplateForm;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'dianqilai';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '客服系统';
    }

    public function getMenus()
    {
        return [
            [
                'name' => '客服设置',
                'route' => 'plugin/dianqilai/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/dianqilai/mall/template/template',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/dianqilai/mall/index/index';
    }

    public function getTemplateForm()
    {
        return new TemplateForm();
    }

    public function templateList()
    {
        return [
            'contact_tpl' => CommonTemplate::class
        ];
    }

    public function getAppConfig()
    {
        try {
            $list['template_message_captain'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                'contact_tpl',
            ]);
        } catch (\Exception $exception) {
            $list['template_message_captain'] = [];
        }
        return $list;
    }
}
