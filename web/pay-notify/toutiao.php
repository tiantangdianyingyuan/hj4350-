<?php
/**
 * @copyright 浙江禾匠信息科技有限公司
 * @author 禾匠开发团队
 * @link https://www.zjhejiang.com/
 *
 */

$_GET['r'] = 'system/pay-notify/toutiao';

error_reporting(E_ALL);

// 注册 Composer 自动加载器
require(__DIR__ . '/../../vendor/autoload.php');

// 创建、运行一个应用
$application = new \app\core\WebApplication();
$application->run();
