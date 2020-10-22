<?php
namespace app\plugins\pond\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\pond\forms\common\CommonPond;
use app\plugins\pond\models\PondLog;

class PondLogForm extends Model
{
    public function search()
    {
        $log = PondLog::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['user_id' => \Yii::$app->user->id],
            ['NOT', 'type = 5'],
        ])
            ->page($pagination)
            ->with(['goods.goodsWarehouse', 'coupon', 'goods.attr'])
            ->orderBy('id DESC')
            ->asArray()
            ->all();
        array_walk($log, function (&$item) {
            if($item['type'] == 4) {
                $goods = Goods::find()->select(['g.*', 'a.sign_id', 'a.id as attr_id'])->alias('g')->where([
                    'g.id' => $item['goods_id'],
                    'g.mall_id' => \Yii::$app->mall->id
                ])->leftJoin(['a' => GoodsAttr::tableName()], 'a.goods_id = g.id')->asArray()->one();
                if (!$goods) {
                    throw new \Exception('数据异常,该条数据不存在');
                }

                $item['attr_list'] = (new Goods())->signToAttr($goods['sign_id'], $goods['attr_groups']);
                $item['attr_id'] = $goods['attr_id'];
            }
            $item['name'] = '';
            $item['name'] = CommonPond::getNewName($item, 'end');
        });
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $log
            ],
        ];
    }
}
