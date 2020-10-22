<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-13
 * Time: 09:34
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\mall;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\composition\forms\common\combination\FactoryCombination;

class GoodsForm extends Model
{
    public $name;
    public $list;
    public $price;
    public $type;
    public $id;
    public $hostId;

    public function rules()
    {
        return [
            [['name', 'price'], 'required'],
            [['name'], 'trim'],
            [['name'], 'string'],
            [['price'], 'number', 'min' => 0, 'max' => 999999],
            [['type', 'id'], 'integer'],
            [['price'], 'default', 'value' => 0],
            [['list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '套餐名称',
            'price' => '套餐优惠',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $combination = FactoryCombination::getCommon()->getCombination($this->type, $this->attributes);
            if ($combination->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getOne()
    {
        try {
            $combination = FactoryCombination::getCommon()
                ->getCombination($this->type, ['id' => $this->id, 'type' => $this->type]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $combination->getOne()
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getGoods($keyword)
    {
        try {
            // 获取商品库商品
            $combination = FactoryCombination::getCommon()->getCombination($this->type, ['hostId' => $this->hostId]);
            $goodsWarehouseId = GoodsWarehouse::find()->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
            ])->keyword($keyword, ['like', 'name', $keyword])
                ->andWhere(['!=', 'type', 'ecard'])
                ->select('id');
            // 获取商品库商品
            $list = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 1,
                'mch_id' => 0,
                'sign' => '',
            ])->keyword($goodsWarehouseId !== null, ['goods_warehouse_id' => $goodsWarehouseId])
                ->keyword($this->hostId, ['!=', 'id', $this->hostId])->with(['goodsWarehouse', 'attr'])
                ->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->page($pagination)
                ->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $combination->getGoods($list),
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
