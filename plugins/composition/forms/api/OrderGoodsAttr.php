<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/2/26
 * Time: 15:50
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\api;

use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\models\Composition;
use yii\helpers\ArrayHelper;

/**
 * Class OrderGoodsAttr
 * @package app\plugins\composition\forms\api
 * @property Composition $composition
 */
class OrderGoodsAttr extends \app\forms\api\order\OrderGoodsAttr
{
    public $composition_price;
    public $composition_goods_id;
    protected $composition;
    public $composition_data;

    public function setGoodsAttr($goodsAttr)
    {
        parent::setGoodsAttr($goodsAttr);
        $model = FactoryCombination::getCommon()->getCombinationList($this->composition->id, $this->composition->type);
        $model->composition = $this->composition;
        $this->composition_goods_id = $model->getCompositionGoods($this->goods_id);
    }

    public function setComposition($composition)
    {
        $this->composition = $composition;
        $this->composition_data = ArrayHelper::toArray($composition);
    }
}
