<?php

namespace app\plugins\lottery\jobs;

use app\models\Mall;
use app\models\User;
use app\plugins\lottery\forms\common\CommonEcard;
use app\plugins\lottery\forms\common\LotteryTemplate;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\models\LotteryDefault;
use app\plugins\lottery\models\LotteryLog;
use yii\base\Component;
use yii\queue\JobInterface;

class LotteryJob extends Component implements JobInterface
{
    public $model;

    public function execute($queue)
    {
        try {
            $this->checkPrizeTimeout();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            \Yii::warning($e);
        }
    }

    /**
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function checkPrizeTimeout()
    {
        $mall_id = $this->model->mall_id;
        $mall = Mall::findOne(['id' => $mall_id]);
        \Yii::$app->setMall($mall);
        /* @var Lottery $lottery */
        $lottery = Lottery::find()->alias('l')->where([
            'l.mall_id' => $mall_id,
            'l.is_delete' => 0,
            'l.id' => $this->model->id,
            'l.type' => 0
        ])
            ->innerJoinWith(["goods g" => function($query) {
                $query->where(['g.is_delete' => 0])->with('goodsWarehouse');
            }])
            ->andWhere(['<=', 'l.end_at', date('Y-m-d H:i:s')])
            ->one();
        $commonEcard = CommonEcard::getCommon();
        if ($lottery) {
            $query = LotteryLog::find()->select('id,user_id')->where([
                'mall_id' => $mall_id,
                'lottery_id' => $lottery->id,
                'status' => 1,
                'child_id' => 0,
            ])->with('user');
            $count = $query->count();//参与人数

            if ($lottery->status == 0) {
                $lottery->type = 3;
                $lottery->save();

                $list = $query->all();
                $idList_err = array_column($list, 'id');
                LotteryLog::updateAll(['status' => 2, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList_err,
                ]);
                $this->otherRefundMsg($list, $lottery, '很抱歉，活动已关闭');
                if ($lottery->goods->goodsWarehouse->type == 'ecard') {
                    // 返还幸运抽占用的卡密数据
                    $commonEcard->refundEcard([
                        'type' => 'occupy',
                        'sign' => 'lottery',
                        'num' => $lottery->stock,
                        'goods_id' => $lottery->goods_id,
                    ]);
                }
                return;
            }


            if ($lottery->join_min_num > $count && $lottery->join_min_num > 0) {
                $lottery->type = 2;
                $lottery->save();

                $list = $query->all();
                $idList_err = array_column($list, 'id');
                LotteryLog::updateAll(['status' => 2, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList_err,
                ]);
                $this->otherRefundMsg($list, $lottery, '很抱歉，活动参与人数不足');
                if ($lottery->goods->goodsWarehouse->type == 'ecard') {
                    // 返还幸运抽占用的卡密数据
                    $commonEcard->refundEcard([
                        'type' => 'occupy',
                        'sign' => 'lottery',
                        'num' => $lottery->stock,
                        'goods_id' => $lottery->goods_id,
                    ]);
                }
                return;
            }

            $default = LotteryDefault::find()->select('user_id')->where([
                'mall_id' => $mall_id,
                'lottery_id' => $lottery->id,
                'is_delete' => 0,
            ])->column();

            $stock = $lottery->stock;//奖品数量

            if ($count > $stock) {
                $log = $query->asArray()->all();
                $logs = array_column($log, 'user_id', 'id'); //参与详情
                $same = array_intersect($logs, $default); //中奖名单1
                $ids_a = array_keys($same);

                $lucky_logs = LotteryLog::find()->select('id,user_id,child_id')->where([
                    'mall_id' => $mall_id,
                    'lottery_id' => $lottery->id,
                    'status' => 1,
                ])
                    ->andWhere(['not', ['in', 'child_id', $same]])
                    ->asArray()
                    ->all();

                $lucky_log = array_column($lucky_logs, 'user_id', 'id');

                if (count($same) > $stock) {
                    $new_ids = array_splice($ids_a, 0, $stock);
                } elseif (count($same) == $stock) {
                    $new_ids = $ids_a;
                } elseif (count($same) + 1 == $stock) {
                    //随机值
                    $new_logs = array_diff($lucky_log, $same);
                    $ids_b = array_rand($new_logs, 1);
                    array_push($ids_a, $ids_b);

                    $new_ids = $ids_a;
                } else {
                    $num = $stock - count($same);
                    //随机值
                    $new_logs = array_diff($lucky_log, $same);
                    $ids_b = [];
                    $array = [];
                    while ($num > count($ids_b)) {
                        $ids = array_rand($new_logs, 1);
                        $user_id = $new_logs[$ids];

                        if (in_array($user_id, $array)) {
                            continue;
                        } else {
                            $array[] = $user_id;
                            $ids_b[] = $ids;
                        }
                    }
                    $new_ids = array_merge($ids_a, $ids_b);
                }

                $t = \Yii::$app->db->beginTransaction();
                //批量修改
                $idList_info = LotteryLog::find()->select('id,user_id')
                    ->where([
                        'AND',
                        ['mall_id' => $mall_id],
                        ['lottery_id' => $lottery->id],
                        ['status' => 1],
                        ['in', 'id', $new_ids],
                    ])->asArray()->all();

                $idList = array_column($idList_info, 'id');
                $user_succ = array_column($idList_info, 'user_id');

                $res = LotteryLog::updateAll(['status' => 3, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList,
                ]);
                if ($lottery->goods->goodsWarehouse->type == 'ecard') {
                    if ($res <= $lottery->stock) {
                        // 返还幸运抽占用的卡密数据
                        $commonEcard->refundEcard([
                            'type' => 'occupy',
                            'sign' => 'lottery',
                            'num' => $lottery->stock - $res,
                            'goods_id' => $lottery->goods_id,
                        ]);
                    }
                    // 添加中奖的卡密数据订单
                    $commonEcard->setEcardLottery($lottery, $idList_info);
                }

                //无获奖
                $idList_info = LotteryLog::find()->select('id,user_id')
                    ->where([
                        'AND',
                        ['mall_id' => $mall_id],
                        ['lottery_id' => $lottery->id],
                        ['status' => 1],
                    ])->asArray()->all();

                $idList_err = array_column($idList_info, 'id');
                $user_err = array_column($idList_info, 'user_id');

                $user_err = array_diff(array_unique($user_err), $user_succ);

                LotteryLog::updateAll(['status' => 2, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList_err,
                ]);
                $lottery->type = 1;
                if ($lottery->save()) {
                    $t->commit();

                    $name = $lottery->goods->goodsWarehouse->name;
                    $this->successMsg($user_succ, $name);
                    $this->refundMsg($user_err, $name);
                } else {
                    $t->rollBack();
                }
            } else {
                $t = \Yii::$app->db->beginTransaction();

                //批量修改
                $idList_info = LotteryLog::find()->select(['id', 'user_id'])
                    ->where([
                        'AND',
                        ['mall_id' => $mall_id],
                        ['lottery_id' => $lottery->id],
                        ['status' => 1],
                        ['child_id' => 0],
                    ])->asArray()->all();

                $idList = array_column($idList_info, 'id');
                $user_succ = array_column($idList_info, 'user_id');

                $res = LotteryLog::updateAll(['status' => 3, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList,
                ]);
                if ($lottery->goods->goodsWarehouse->type == 'ecard') {
                    if ($res <= $lottery->stock) {
                        // 返还幸运抽占用的卡密数据
                        $commonEcard->refundEcard([
                            'type' => 'occupy',
                            'sign' => 'lottery',
                            'num' => $lottery->stock - $res,
                            'goods_id' => $lottery->goods_id,
                        ]);
                    }
                    // 添加中奖的卡密数据订单
                    $commonEcard->setEcardLottery($lottery, $idList_info);
                }

                //增加无获奖
                $idList_info = LotteryLog::find()->select('id,user_id')
                    ->where([
                        'AND',
                        ['mall_id' => $mall_id],
                        ['lottery_id' => $lottery->id],
                        ['status' => 1],
                    ])->asArray()->all();
                $idList_err = array_column($idList_info, 'id');
                LotteryLog::updateAll(['status' => 2, 'obtain_at' => date('Y-m-d H:i:s')], [
                    'id' => $idList_err,
                ]);

                $lottery->type = 1;
                if ($lottery->save()) {
                    $t->commit();

                    $name = $lottery->goods->goodsWarehouse->name;
                    $this->successMsg($user_succ, $name);
                } else {
                    $t->rollBack();
                }
            }
        } else {
            return false;
        }
    }

    public function successMsg($ids, $name)
    {
        $user = User::find()->where(['in', 'id', $ids])->all();
        if (!$user) {
            return false;
        }

        foreach ($user as $item) {
            try {
                $tplMsg = new LotteryTemplate();
                $tplMsg->activityName = '幸运抽奖';
                $tplMsg->goodsName = $name;
                $tplMsg->result = '已中奖';
                $tplMsg->remark = '尽快来领取属于你的专属礼品！';
                $tplMsg->user = $item;
                $tplMsg->page = 'plugins/lottery/prize/prize?type=2';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }

    public function refundMsg($ids, $name)
    {
        $user = User::find()->where(['in', 'id', $ids])->all();
        if (!$user) {
            return false;
        }

        foreach ($user as $item) {
            try {
                $tplMsg = new LotteryTemplate();
                $tplMsg->activityName = '幸运抽奖';
                $tplMsg->goodsName = $name;
                $tplMsg->result = '未中奖';
                $tplMsg->remark = '很抱歉，本次尚未中奖！';
                $tplMsg->user = $item;
                $tplMsg->page = 'plugins/lottery/index/index';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }

    public function otherRefundMsg($list, $lottery, $remark)
    {
        foreach ($list as $v) {
            try {
                $tplMsg = new LotteryTemplate();
                $tplMsg->activityName = '幸运抽奖';
                $tplMsg->goodsName = $lottery->goods->goodsWarehouse->name;
                $tplMsg->result = '未中奖';
                $tplMsg->remark = $remark;
                $tplMsg->user = $v->user;
                $tplMsg->page = 'plugins/lottery/index/index';
                $tplMsg->send();
            } catch (\Exception $exception) {
                \Yii::warning($exception->getMessage());
            }
        }
    }
}
