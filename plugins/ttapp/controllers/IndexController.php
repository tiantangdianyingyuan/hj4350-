<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/14
 * Time: 9:20
 */

namespace app\plugins\ttapp\controllers;

use app\core\response\ApiCode;
use app\plugins\ttapp\forms\JumpAppidForm;
use app\plugins\ttapp\forms\SettingForm;
use app\plugins\Controller;
use app\plugins\ttapp\models\TtappConfig;
use app\plugins\ttapp\models\TtappJumpAppid;

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
                $config = TtappConfig::find()
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
        $config = TtappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config) {
            throw new \Exception('请先填写小程序的基础配置');
        }


        $jumpList = TtappJumpAppid::find()->where([
            'mall_id' => \Yii::$app->mall->id,
        ])->all();
        $newJumpList = [];
        foreach ($jumpList as $item) {
            $newJumpList[] = $item->appid;
        }

        $projectConfigJson = <<<EOF
{
	"setting": {
		"urlCheck": true,
		"es6": true,
		"postcss": false,
		"minified": false,
		"newFeature": true
	},
	"appid": "{$config->app_key}",
	"projectname": "zjhj_mall_ttapp_v{$version}"
}
EOF;

        $zipArchive = new \ZipArchive();
        $zipArchive->open($appSrcFile);
        $zipArchive->addFromString('siteinfo.js', $siteInfoContent);
        $zipArchive->addFromString('project.config.json', $projectConfigJson);

        $appJson = $zipArchive->getFromName('app.json');
        $appJson = json_decode($appJson, true);
        $appJson['navigateToMiniProgramAppIdList'] = $newJumpList;
        $appJson = json_encode($appJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $zipArchive->addFromString('app.json', $appJson);

        $zipArchive->close();

        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($appSrcFile));
        header("Content-Disposition: attachment; filename=zjhj_mall_ttapp_v{$version}.zip");
        $file = fopen($appSrcFile, "r");
        echo fread($file, filesize($appSrcFile));
        fclose($file);
    }


    public function actionJumpAppid()
    {
        if (\Yii::$app->request->isPost) {
            $form = new JumpAppidForm();
            $form->appid_list = \Yii::$app->request->post('appid_list');
            return $form->getResponseData();
        } else {
            $list = TtappJumpAppid::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])->all();
            $newList = [];
            foreach ($list as $item) {
                $newList[] = $item->appid;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                ],
            ];
        }
    }

}