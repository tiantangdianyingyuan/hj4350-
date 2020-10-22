<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/10
 * Time: 16:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard;


use app\handlers\HandlerBase;
use app\forms\common\ecard\CommonEcard;
use app\plugins\ecard\forms\mall\GoodsForm;
use app\plugins\ecard\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'ecard';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '电子卡密';
    }

    public function getMenus()
    {
        return [
            [
                'name' => '电子卡密',
                'route' => 'plugin/ecard/mall/index/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '新增卡密',
                        'route' => 'plugin/ecard/mall/index/edit'
                    ],
                    [
                        'name' => '卡密管理',
                        'route' => 'plugin/ecard/mall/index/list'
                    ],
                    [
                        'name' => '卡密编辑',
                        'route' => 'plugin/ecard/mall/index/list-edit'
                    ],
                    [
                        'name' => '卡密删除',
                        'route' => 'plugin/ecard/mall/index/ecard-destroy'
                    ],
                    [
                        'name' => '卡密数据删除',
                        'route' => 'plugin/ecard/mall/index/destroy'
                    ],
                    [
                        'name' => '导出卡密数据',
                        'route' => 'plugin/ecard/mall/index/export'
                    ],
                    [
                        'name' => '导入卡密数据',
                        'route' => 'plugin/ecard/mall/index/import'
                    ]
                ]
            ]
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/ecard/mall/index/index';
    }

    /**
     * 获取商品配置
     */
    public function getGoodsConfig()
    {
        return CommonEcard::getCommon()->getGoodsConfig();
    }

    /**
     * @param $params
     * @return array
     * @throws \Exception
     * 获取商品信息
     */
    public function getGoodsPlugin($params)
    {
        $form = new GoodsForm();
        $form->attributes = $params;
        return $form->getInfo();
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    public function getIsTypePlugin()
    {
        return true;
    }

    public function getTypeData($order)
    {
        return CommonEcard::getCommon()->getTypeData($order);
    }
}
