<?php
namespace app\plugins\scratch\forms\api;

use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\scratch\forms\common\CommonScratch;
use app\plugins\scratch\models\ScratchLog;
use app\core\response\ApiCode;

class ScratchLogForm extends Model
{
    public function search()
    {
        $log = ScratchLog::find()->andWhere([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['user_id' => \Yii::$app->user->id],
                ['not', 'type = 5'],
                ['not', 'status = 0']
            ])
            ->with(['goods.goodsWarehouse', 'coupon', 'goods.attr'])
            ->page($pagination)
            ->orderBy('created_at DESC,id DESC')
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
            $item['name'] = CommonScratch::getNewName($item, 'end');
        });
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $log,
        ];
    }

    public function record()
    {
        $limit = 2;
        $log = ScratchLog::find()->select(["*, date_format(created_at, '%m-%d %H:%i') create_time"])->andWhere([
                'and',
                ['mall_id' =>  \Yii::$app->mall->id],
                ['not', 'type = 5'],
                ['not', 'status = 0']
            ])
            ->with(['goods.goodsWarehouse', 'user', 'coupon'])
            ->page($pagination, $limit)
            ->orderBy('created_at DESC,id DESC')
            ->asArray()
            ->all();
        array_walk($log, function (&$item) {
            $item['user'] = $item['user']['nickname'];
            $item['name'] = CommonScratch::getNewName($item, 'end');
            unset($item['detail']);
        });
        unset($item);
            
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $log
        ];
    }
}
