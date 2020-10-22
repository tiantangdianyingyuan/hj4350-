<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/8
 * Time: 11:01
 */

namespace app\forms\common\activity;

trait Activity
{
    /**
     * 判断活动所处时间段
     * @param $activity
     * @return string
     * 1:未开始
     * 2:进行中
     * 3:已结束
     * 0:下架中
     */
    public static function timeSlot($activity)
    {
        if ($activity['status'] == 0) {
            return '0';
        }
        $now = time();
        $start = strtotime($activity['start_at']);
        $end = strtotime($activity['end_at']);
        if ($now < $start) {
            return '1';
        } elseif ($now >= $start && $now <= $end) {
            return '2';
        } elseif ($now > $end) {
            return '3';
        }
        return '3';
    }

    public static function check($model, $id, $start_at, $end_at)
    {
        return $model::find()->where(
            [
                'mall_id' => \Yii::$app->mall->id,
                'status' => 1,
                'is_delete' => 0,
            ]
        )
            ->andWhere(
                [
                    '>=',
                    'end_at',
                    mysql_timestamp()
                ]
            )
            ->andWhere(
                [
                    'or',
                    ['between', 'start_at', $start_at, $end_at],
                    ['between', 'end_at', $start_at, $end_at],
                    [
                        'and',
                        [
                            '<=',
                            'start_at',
                            $start_at
                        ],
                        [
                            '>=',
                            'end_at',
                            $end_at
                        ]
                    ]
                ]
            )
            ->andWhere(['!=', 'id', $id])
            ->one();
    }
}
