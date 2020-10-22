<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-20
 * Time: 09:47
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;

class GoodsForm extends Model
{
    public $page;
    public $goods_id;
    public $composition_id;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'goods_id', 'composition_id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = $this->getComposition($this->goods_id);
            $otherList = [];
            if ($this->page == 1 && count($list) >= 1) {
                $otherList = [array_shift($list)];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'other_list' => $otherList,
                    'list' => array_values($list),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $goodsId
     * @return array
     * @throws \Exception
     */
    public function getList($goodsId)
    {
        return [
            'list' => $this->getComposition($goodsId),
            'url' => '/plugins/composition/detail/detail'
        ];
    }

    /**
     * @param $goodsId
     * @return array
     * @throws \Exception
     * 根据商品id获取包含该商品的套餐列表
     */
    public function getComposition($goodsId)
    {
        $orderBy = [];
        if ($this->composition_id) {
            $orderBy['`id`=' . $this->composition_id] = SORT_DESC;
        }
        $orderBy['sort'] = SORT_ASC;
        $orderBy['sort_price'] = SORT_ASC;
        $orderBy['created_at'] = SORT_DESC;
        $compositionId = CompositionGoods::find()
            ->where(['goods_id' => $goodsId, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->select('model_id')->column();
        $list = Composition::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
                'status' => 1, 'id' => $compositionId
            ])->apiPage(5, $this->page)
            ->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods', 'compositionGoods.goods.attr'])
            ->orderBy($orderBy)
            ->all();
        $newList = [];
        /* @var Composition[] $list */
        foreach ($list as $composition) {
            $compositionClass = FactoryCombination::getCommon()->getCombination($composition->type);
            $compositionClass->composition = $composition;
            $goodsList = $compositionClass->getGoodsList($composition);
            $newItem = [
                'id' => $composition->id,
                'name' => $composition->name,
                'type' => $composition->type,
                'type_text' => $composition->typeText,
                'price' => $composition->price,
                'max_discount' => $compositionClass->getMaxDiscount(),
            ];
            $newItem = array_merge($newItem, $goodsList);
            $newList[] = $newItem;
        }
        return $newList;
    }
}
