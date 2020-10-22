<?php
/**
 * @copyright ©2018 人人禾匠商城
 * @author Lu Wei
 * @link 域名
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\copy;


use app\helpers\PluginHelper;
use app\plugins\copy\forms\common\CommonShopping;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '采集平台商户商品',
                'route' => 'plugin/copy/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '采集平台首页模板',
                'route' => 'plugin/copy/mall/index/home',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function handler()
    {

    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'copy';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '导入商家商品';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/copy/mall/index/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        return [
        ];
    }

    public function install()
    {
        $sql = <<<EOF
-- v1.0.5
CREATE TABLE `zjhj_bd_copy_store` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
   `store_name` varchar(100) DEFAULT '' COMMENT '商城原名',
   `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'url',
   `store_id` int(10) NOT NULL DEFAULT '0' COMMENT '店铺id',
   `ver` int(10) NOT NULL DEFAULT '3' COMMENT '版本',
   `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `is_delete` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
 
 CREATE TABLE `zjhj_bd_copy_store_cat` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '名称',
   `cat_id` INT(10) NOT NULL DEFAULT 0 COMMENT '分类id',
   `store_id` INT(10) NOT NULL DEFAULT 0  COMMENT '店铺id',
   `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `deleted_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `is_delete` TINYINT(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
 ) ENGINE=INNODB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
 


  CREATE TABLE `zjhj_bd_copy_store_goods` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '名称',
   `cat_id` INT(10) NOT NULL DEFAULT 0 COMMENT '分类id',
   `goods_id` INT(10) NOT NULL DEFAULT 0 COMMENT '分类id',
   `pic_url` VARCHAR(266) NOT NULL DEFAULT '' COMMENT 'url',
   `store_id` INT(10) NOT NULL DEFAULT 0  COMMENT '店铺id',
   `goods_info` LONGTEXT NOT NULL  COMMENT '原始数据',
   `is_copy` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否已经copy',
   `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `deleted_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
   `is_delete` TINYINT(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
 ) ENGINE=INNODB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
EOF;
        sql_execute($sql);
        return parent::install();
    }
}
