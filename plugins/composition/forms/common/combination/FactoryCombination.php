<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-14
 * Time: 10:00
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\common\combination;


use app\models\Model;

class FactoryCombination extends Model
{
    public $combination;

    public static $instance;

    public $mall;

    public static function getCommon($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    /**
     * @param $type
     * @param array $config
     * @return BaseCombination|null
     * @throws \Exception
     */
    public function getCombination($type, $config = [])
    {
        $model = null;
        switch ($type) {
            case 1:
                $model = new FixedCombination($config);
                $model->mall = $this->mall;
                break;
            case 2:
                $model = new GoodsCombination($config);
                $model->mall = $this->mall;
                break;
            default:
                $model = null;
        }
        if (!$model) {
            throw new \Exception('无效的套餐组合');
        }
        return $model;
    }

    public $combinationList = [];
    public function setCombinationList($compositionId, $combination)
    {
        if (!isset($this->combinationList[$compositionId])) {
            $this->combinationList[$compositionId] = $combination;
        }
    }

    /**
     * @param $compositionId
     * @param $type
     * @param array $config
     * @return BaseCombination|null
     * @throws \Exception
     */
    public function getCombinationList($compositionId, $type, $config = [])
    {
        if (!isset($this->combinationList[$compositionId])) {
            $combination = $this->getCombination($type, $config);
            $this->combinationList[$compositionId] = $combination;
        }
        return $this->combinationList[$compositionId];
    }
}
