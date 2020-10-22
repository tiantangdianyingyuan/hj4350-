<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/18
 * Time: 16:05
 */

namespace app\plugins\aliapp\controllers;


use app\core\response\ApiCode;
use app\models\AliappConfig;
use app\plugins\aliapp\forms\SettingForm;
use app\plugins\aliapp\forms\TemplateEditForm;
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
                $config = AliappConfig::find()
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
        $zipArchive = new \ZipArchive();
        $zipArchive->open($appSrcFile);
        $zipArchive->addFromString('siteinfo.js', $siteInfoContent);
        $zipArchive->close();

        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($appSrcFile));
        header("Content-Disposition: attachment; filename=zjhj_mall_aliapp_v{$version}.zip");
        $file = fopen($appSrcFile, "r");
        echo fread($file, filesize($appSrcFile));
        fclose($file);
    }
}
