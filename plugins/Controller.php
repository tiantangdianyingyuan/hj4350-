<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */


namespace app\plugins;

use app\controllers\mall\MallController;
use app\models\Mall;

class Controller extends MallController
{
    public $layout = '/plugin';

    public function init()
    {
        parent::init();
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->headers
                ->set('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->set('Expires', 'Thu, 19 Nov 1981 08:00:00 GMT')
                ->set('Pragma', 'no-cache');
        }
        $this->loadMall();
    }

    /**
     * @return $this
     */
    private function loadMall()
    {
        $id = \Yii::$app->getSessionMallId();
        $url = \Yii::$app->urlManager->createUrl('admin/index/index');
        if (!$id) {
            return $this->redirect($url)->send();
        }
        $mall = Mall::find()->where(['id' => $id, 'is_delete' => 0])->with('option')->one();
        if (!$mall) {
            return $this->redirect($url)->send();
        }
        if ($mall->is_delete !== 0 || $mall->is_recycle !== 0) {
            return $this->redirect($url)->send();
        }

        $newOptions = [];
        foreach ($mall['option'] as $item) {
            $newOptions[$item['key']] = $item['value'];
        }
        $mall->options = (object)$newOptions;

        \Yii::$app->mall = $mall;
        return $this;
    }

    public function render($view, $params = [])
    {
        if (mb_stripos($view, '@') !== 0 && mb_stripos($view, '/') !== 0) {
            $view = '@app/plugins/' . $this->module->id . '/views/' . mb_strtolower($this->id) . '/' . $view;
        }
        return parent::render($view, $params);
    }
}
