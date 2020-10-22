<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api;

use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\models\User;
use app\plugins\integral_mall\forms\common\SettingForm;
use app\plugins\integral_mall\models\IntegralMallGoods;
use app\plugins\integral_mall\models\IntegralMallGoodsAttr;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $cat_id;

    public function rules()
    {
        return [
            [['page', 'cat_id', 'id'], 'integer'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\integral_mall\models\Goods';
        $form->cat_id = $this->cat_id;
        $form->page = $this->page;
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse', 'attr', 'integralMallGoods'];
        $form->status = 1;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;
        $query->leftJoin(['img' => IntegralMallGoods::tableName()], 'img.goods_id=g.id');
        if ($this->cat_id == 0) {
            $query->andWhere(['img.is_home' => 1]);
        }
        $pagination = null;
        $list = $query->orderBy('sort ASC')->page($pagination)->asArray()->all();

        $commonEcard = CommonEcard::getCommon();
        foreach ($list as &$item) {
            $attrList = IntegralMallGoodsAttr::find()->where([
                'goods_id' => $item['id'],
                'is_delete' => 0,
            ])->asArray()->all();

            $goodsStock = 0;
            foreach ($item['attr'] as &$aItem) {
                foreach ($attrList as $aLItem) {
                    if ($aItem['id'] == $aLItem['goods_attr_id']) {
                        $aItem['integral_num'] = $aLItem['integral_num'];
                    }
                }
                $goodsStock += $commonEcard->getEcardStockByArray($aItem['stock'], $item);
            }

            $item['goods_stock'] = $goodsStock;
            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->mall = \Yii::$app->mall;
            $form->user = User::findOne(\Yii::$app->user->id);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $goods = $form->getAll();


            $attrList = IntegralMallGoodsAttr::find()->where([
                'goods_id' => $goods['id'],
                'is_delete' => 0,
            ])->asArray()->all();
            foreach ($goods['attr'] as &$aItem) {
                foreach ($attrList as $alItem) {
                    if ($aItem['id'] == $alItem['goods_attr_id']) {
                        $aItem['integral_num'] = $alItem['integral_num'];
                    }
                }
            }

            $integralMallGoods = IntegralMallGoods::findOne(['goods_id' => $goods['id']]);
            $goods = ArrayHelper::toArray($goods);
            $goods['integralMallGoods'] = $integralMallGoods;

            $setting = (new SettingForm())->search();
            $goods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $goods['goods_marketing']['limit'] : '';
            foreach ($goods['attr'] as &$aItem) {
                $aItem['extra'] = [
                    'value' => $aItem['integral_num'],
                    'name' => '积分'
                ];
            }
            unset($aItem);

            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $goods['share'] = 0;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
