<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/17
 * Time: 10:26
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\common;


use app\forms\common\CommonAppConfig;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\DiyAlonePage;

/**
 * @property Mall $mall
 */
class CommonAlonePage extends Model
{
    public $mall;

    public static function getCommon($mall)
    {
        $form = new self();
        $form->mall = $mall;
        return $form;
    }

    /**
     * @param $type
     * @return DiyAlonePage|null
     * 获取指定的单独页面对象
     */
    public function getAlonePage($type = 'auth')
    {
        $model = DiyAlonePage::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'type' => $type]);
        if (!$model) {
            $model = new DiyAlonePage();
            $model->mall_id = $this->mall->id;
            $model->is_delete = 0;
            $model->is_open = 0;
        }
        if ($model->params) {
            $model->params = \Yii::$app->serializer->decode($model->params);
        }
        return $model;
    }

    /**
     * @param array $attributes
     * @return bool|string
     * 保存对象
     */
    public function saveAlonePage($attributes)
    {
        $model = $this->getAlonePage();
        $model->attributes = $attributes;
        if (!$model->save()) {
            return $this->getErrorMsg($model);
        }
        return true;
    }

    /**
     * @param $type
     * @return array
     * 获取指定页面的配置
     */
    public function getPage($type)
    {
        $model = $this->getAlonePage($type);
        if (!$model->params || $model->is_open == 0) {
            $result = $this->getDefaultPage($type);
        } else {
            $result = $model->params;
        }

        return $result;
    }

    /**
     * @param $type
     * @return mixed|string
     * 获取指定页面的默认配置
     */
    public function getDefaultPage($type)
    {
        $defaultList = CommonAppConfig::getDefaultPageList();
        return isset($defaultList[$type]) ? $defaultList[$type] : '';
    }

    public function plottingScale($params)
    {
        if (is_numeric($params)) {
            $params = $params * 2;
        } elseif (is_array($params) || is_object($params)) {
            foreach ($params as &$item) {
                $item = $this->plottingScale($item);
            }
        }
        return $params;
    }
}
