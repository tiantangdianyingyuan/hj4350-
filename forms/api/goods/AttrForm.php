<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-08-10
 * Time: 14:32
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\goods;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\Goods;
use app\models\Model;
use yii\db\Exception;

class AttrForm extends Model
{
    public $id;
    public $mch_id;

    public function rules()
    {
        return [
            [['id', 'mch_id'], 'integer'],
        ];
    }

    public function getAttr()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $form->mch_id = $this->mch_id;
            /** @var Goods $goods */
            $goods = Goods::find()->with(['attr', 'attr.memberPrice'])
                ->where([
                    'is_delete' => 0,
                    'id' => $this->id,
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => $this->mch_id
                ])->one();
            if (!$goods) {
                throw new Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new Exception('商品未上架');
            }

            $form->goods = $goods;
            $mallGoods = CommonGoods::getCommon()->getMallGoods($goods->id);
            $form->setMember($mallGoods->is_negotiable == 0);
            $form->setShare(false);
            $res = $form->getAll(['attr', 'goods_num', 'attr_group', 'price_max', 'price_min']);
            $data = [
                'id' => $goods->id,
                'mall_id' => $goods->mall_id,
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'goods_type' => $goods->goodsWarehouse->type,
                'mch_id' => $goods->mch_id,
                'price' => $goods->price,
                'use_attr' => $goods->use_attr,
                'attr_groups' => $res['attr_groups'],
                'attr' => $res['attr'],
                'level_show' => $res['level_show'],
                'goods_num' => $res['goods_num'],
                'price_max' => $res['price_max'],
                'price_min' => $res['price_min'],
            ];
            if ($res['level_show'] != 0) {
                $data['price_member_max'] = $res['price_member_max'];
                $data['price_member_min'] = $res['price_member_min'];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}