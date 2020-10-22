<?php
error_reporting(E_ALL & ~E_NOTICE);
defined('IN_IA') or exit('Access Denied');

class Zjhj_bdModule extends WeModule
{
    public function welcomeDisplay()
    {
        $entry = '/web/index.php';
        if (file_exists(__DIR__ . $entry)) {
            global $_W;
            $wUser = [
                'uid' => $_W['user']['uid'],
                'name' => $_W['user']['name'],
                'username' => $_W['user']['username'],
            ];
            $wAccount = [
                'acid' => $_W['account']['acid'],
                'name' => $_W['account']['name'],
            ];

            if ($wUser['name'] === null || $wUser['name'] === '') $wUser['name'] = $wUser['username'];

            require __DIR__ . '/vendor/autoload.php';
            $app = new app\core\WebApplication();

            $app->session->set('we7_user', $wUser);
            $app->session->set('we7_account', $wAccount);

            $uri = $_W['siteroot'] . 'addons/' . $_W['current_module']['name'] . $entry . '?r=mall/we7-entry/login';
            header('Location: ' . $uri);
            die();
        } else {
            die('应用入口文件缺失，请联系开发者处理！');
        }
    }
}
