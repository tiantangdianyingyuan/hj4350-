<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\handlers;

use app\models\Goods;
use app\models\GoodsHotSearch;

class GoodsStatusHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Goods::EVENT_STATUS, function ($event) {
            if (empty($id = $event->id)) {
                \Yii::error('数据处理错误');
            }
            // 删除热搜
            if (intval($after = $event->status_after) === 0 && !empty($ids = is_array($id) ? $id : [$id])) {
                //真删
                GoodsHotSearch::deleteAll([
                    'goods_id' => $ids,
                    'is_delete' => 0,
                ]);
            }
        });
    }
}
