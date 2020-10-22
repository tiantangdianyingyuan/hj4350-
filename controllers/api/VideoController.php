<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\VideoForm;
use app\forms\common\video\Video;

class VideoController extends ApiController
{
    public function actionIndex()
    {
        $form = new VideoForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionPlay($url)
    {
        $url = Video::getUrl($url);
        return $this->asJson([
            'code' => 0,
            'data' => [
                'url' => $url
            ]
        ]);
    }
}
