<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/14
 * Time: 9:20
 */

namespace app\plugins\bdapp\controllers;

use app\core\response\ApiCode;
use app\plugins\bdapp\forms\SettingForm;
use app\plugins\bdapp\models\BdappConfig;
use app\plugins\Controller;

class IndexController extends Controller
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $config = BdappConfig::find()
                    ->where([
                        'mall_id' => \Yii::$app->mall->id,
                    ])->asArray()->one();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $config ? $config : null,
                ];
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionPackage()
    {
        return $this->render('package');
    }

    public function actionPackageDownload()
    {
        $appSrcFile = __DIR__ . '/../app.zip';
        if (!file_exists($appSrcFile)) {
            throw new \Exception('app.zip文件不存在。');
        }
        $apiRoot = \Yii::$app->request->hostInfo
            . rtrim(\Yii::$app->request->baseUrl, '/')
            . '/index.php?_mall_id='
            . \Yii::$app->mall->id;
        $apiRoot = str_replace('http://', 'https://', $apiRoot);
        $version = app_version();
        $siteInfoContent = <<<EOF
module.exports = {
    'acid': -1,
    'version': '{$version}',
    'apiroot': '{$apiRoot}',
};

EOF;
        $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config) {
            throw new \Exception('请先填写小程序的基础配置');
        }
        $swanJson = <<<EOF
{
  "appInfo": {},
  "appid": "{$config->app_id}",
  "appkey": "",
  "compilation-args": {
    "common": {
      "ignorePrefixCss": false,
      "ignoreTransJs": false,
      "ignoreUglify": false,
      "imgCompress": true
    }
  },
  "projectname": "zjhj_mall_bdapp_v{$version}",
  "setting": {
    "urlCheck": true
  }
}
EOF;

        $zipArchive = new \ZipArchive();
        $zipArchive->open($appSrcFile);
        $zipArchive->addFromString('siteinfo.js', $siteInfoContent);
        $zipArchive->addFromString('project.swan.json', $swanJson);
        $zipArchive->close();

        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($appSrcFile));
        header("Content-Disposition: attachment; filename=zjhj_mall_bdapp_v{$version}.zip");
        $file = fopen($appSrcFile, "r");
        echo fread($file, filesize($appSrcFile));
        fclose($file);
    }

}