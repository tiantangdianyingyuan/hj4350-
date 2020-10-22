<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 11:42
 */


namespace app\controllers\api;


use app\forms\AttachmentUploadForm;
use yii\web\UploadedFile;

class AttachmentController extends ApiController
{
    public function actionUpload($name = 'file')
    {
        if (mb_stripos(\Yii::$app->request->referrer, 'toutiao') !== false) {
            if (!empty($_FILES[$name])) {
                $fName = $_FILES[$name]['name'];
                $qPosition = mb_stripos($fName, '?');
                if ($qPosition !== false) {
                    $fName = mb_substr($fName, 0, $qPosition);
                    $_FILES[$name]['name'] = $fName;
                }
            }
        }

        $form = new AttachmentUploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        if (\Yii::$app->request->post('file_name') && \Yii::$app->request->post('file_name') !== 'null') {
            $form->file->name = \Yii::$app->request->post('file_name');
        }

        $mchId = \Yii::$app->request->post('mch_id');
        if ($mchId && is_numeric($mchId)) {
            \Yii::$app->setMchId($mchId);
        }

        return $this->asJson($form->save());
    }
}
