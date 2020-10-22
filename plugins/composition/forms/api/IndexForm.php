<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-19
 * Time: 10:49
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\api;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\forms\common\CommonSetting;
use app\plugins\composition\models\Composition;
use app\plugins\composition\models\CompositionGoods;

class IndexForm extends Model
{
    public $page;
    public $keyword;
    public $composition_id;

    public function rules()
    {
        return [
            [['page', 'composition_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
        ];
    }

    public function getConfig()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'title' => $setting['title'],
                'rule' => $setting['rule'],
                'activityBg' => $setting['activityBg'],
            ]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $compositionId = null;
            if ($this->keyword !== '') {
                $goodsWarehouseId = GoodsWarehouse::find()
                    ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                    ->andWhere(['like', 'name', $this->keyword])
                    ->select('id');
                $goodsId = Goods::find()
                    ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => ''])
                    ->andWhere(['goods_warehouse_id' => $goodsWarehouseId])->select('id');
                $compositionId = CompositionGoods::find()
                    ->where(['goods_id' => $goodsId, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                    ->select('model_id')->column();
            }
            $list = Composition::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
                    'status' => 1
                ])->keyword($compositionId !== null, ['id' => $compositionId])
                ->apiPage(5, $this->page)
                ->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods', 'compositionGoods.goods.attr'])
                ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all();
            $newList = [];
            /* @var Composition[] $list */
            foreach ($list as $composition) {
                $newList[] = $this->getOne($composition);
            }


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $newList
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var Composition $composition */
            $composition = Composition::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
                    'status' => 1, 'id' => $this->composition_id
                ])
                ->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods', 'compositionGoods.goods.attr'])
                ->one();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'composition' => $this->getOne($composition)
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
     * @param Composition $composition
     * @return array
     * @throws \Exception
     */
    protected function getOne($composition)
    {
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
        return $newItem;
    }
}
