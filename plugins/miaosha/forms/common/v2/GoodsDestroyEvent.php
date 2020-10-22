<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/11
 * Time: 9:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\miaosha\forms\common\v2;

use app\models\Model;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;

class GoodsDestroyEvent extends Model
{
    public $goods;

    public function destroy()
    {
        try {
            if ($this->goods->sign) {
                return false;
            }

            $list = MiaoshaGoods::find()->andWhere(['goods_warehouse_id' => $this->goods->goods_warehouse_id])->groupBy('activity_id')->all();

            $activityIds = [];
            foreach ($list as $key => $value) {
                $activityIds[] = $value->activity_id;
            }

            $res = MiaoshaActivitys::updateAll([
                'is_delete' => 1,
            ], [
                'id' => $activityIds,
            ]);

            \Yii::warning('秒杀活动删除成功数量:' . $res);

            $res = MiaoshaGoods::updateAll([
                'is_delete' => 1,
            ], [
                'activity_id' => $activityIds,
                'is_delete' => 0,
            ]);

            \Yii::warning('秒杀活动场次删除成功数量:' . $res);

        } catch (\Exception $exception) {
            \Yii::error('秒杀商品删除事件出错：' . $exception->getMessage());
        }
    }
}
