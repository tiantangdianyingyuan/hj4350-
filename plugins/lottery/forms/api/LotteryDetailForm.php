<?php

namespace app\plugins\lottery\forms\api;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\forms\common\video\Video;
use app\models\Goods;
use app\models\Model;
use app\plugins\lottery\forms\common\CommonLottery;
use app\plugins\lottery\models\Lottery;
use app\plugins\lottery\models\LotteryLog;

class LotteryDetailForm extends Model
{
    public $lottery_id;
    public $user_id;
    public $page;

    public function rules()
    {
        return [
            [['lottery_id'], 'required'],
            [['lottery_id', 'user_id', 'page'], 'integer'],
        ];
    }


    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var Lottery $lottery */
            $lottery = Lottery::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->lottery_id,
                'is_delete' => 0
            ])->with(['log' => function ($query) {
                $query->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => \Yii::$app->user->id,
                    'child_id' => 0
                ]);
            }])->with(['goods.goodsWarehouse', 'goods.attr'])
                ->one();

            if ($lottery->log) {
                $id = $lottery->log[0]->id;
            } else {
                if ($lottery->end_at < date('Y-m-d H:i:s')) {
                    throw new \Exception('活动已结束');
                };
                if ($lottery->start_at > date('Y-m-d H:i:s')) {
                    throw new \Exception('活动尚未开始');
                }
                $model = new LotteryLog();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = \Yii::$app->user->id;
                $model->lottery_id = $lottery->id;
                $model->status = 1;
                $model->goods_id = $lottery->goods->id;

                $model->child_id = 0;
                $model->lucky_code = $this->getlucky($lottery->id);
                $lottery->participant += 1;
                $lottery->code_num += 1;
                if ($model->save()) {
                    $parent = LotteryLog::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'lottery_id' => $lottery->id,
                        'status' => 0,
                        'child_id' => \Yii::$app->user->id,
                    ]);
                    if ($parent) {
                        $parent->status = 1;
                        if (!$parent->save()) {
                            return $this->getErrorResponse($parent);
                        }
                        $lottery->code_num += 1;
                    };
                    if (!$lottery->save()) {
                        return $this->getErrorResponse($lottery);
                    }

                    $id = $model->id;
                } else {
                    return $this->getErrorResponse($model);
                }
            }

            list($list, $num, $user_list) = $this->getLog($id);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'num' => $num,
                    'user_list' => $user_list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function getLog($id)
    {
        $list = LotteryLog::find()->alias('l')->select(["l.*", "ll.parent_num"])->where([
            'l.mall_id' => \Yii::$app->mall->id,
            'l.id' => $id,
            'l.user_id' => \Yii::$app->user->id,
        ])
            ->leftJoin([
                'll' => LotteryLog::find()
                    ->select('lottery_id, COUNT(1) parent_num')
                    ->where([
                        'AND',
                        ['mall_id' => \Yii::$app->mall->id],
                        ['user_id' => \Yii::$app->user->id],
                        ['not', ['child_id' => 0]],
                        ['not', ['status' => 0]]
                    ])->groupBy('lottery_id'),
            ], 'll.lottery_id = l.lottery_id')
            ->with(['goods.goodsWarehouse', 'goods.attr'])
            ->with('lottery')
            ->asArray()
            ->one();

        $attr = $list['goods']['attr'][0];
        $list['goods']['attr_id'] = $attr['id'];
        $list['attr']['attr_list'] = (new Goods())->signToAttr($attr['sign_id'], $list['goods']['attr_groups']);
        $list['parent_num'] = $list['parent_num'] ?? 0;
        $list['time'] = date('m月d日 H:i开奖', strtotime($list['lottery']['end_at']));

        if ($list['status'] == 2) {
            $child = LotteryLog::find()->where([
                'and',
                ['mall_id' => \Yii::$app->mall->id],
                ['user_id' => \Yii::$app->user->id],
                ['lottery_id' => $list['lottery_id']],
                ['in', 'status', [3, 4]]
            ])->one();
            if ($child) {
                $list['id'] = $child->id;
                $list['status'] = $child->status;
                $list['lucky_code'] = $child['lucky_code'];
            }
        }

        $query = LotteryLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'lottery_id' => $list['lottery_id'],
            'child_id' => 0
        ]);
        $all_num = $query->count();

        if (in_array($list['status'], [2, 3, 4])) {
            $limit = 6;
            $query = LotteryLog::find()->where([
                'and',
                ['mall_id' => \Yii::$app->mall->id],
                ['lottery_id' => $list['lottery_id']],
                ['in', 'status', [3, 4]],
            ]);
            $list['ok_num'] = $query->count();
        } else {
            $limit = 30;
        }

        $user_list = $query->with('user.userInfo')
            ->apiPage($limit, $this->page)
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        return [$list, $all_num, $user_list];
    }

    private function getlucky($lottery_id)
    {
        $lucky_code = $this->random_num(6);
        $list = LotteryLog::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'lottery_id' => $lottery_id,
            'lucky_code' => $lucky_code,
        ]);
        if ($list) {
            $this->getlucky($lottery_id);
        } else {
            return (string)$lucky_code;
        }
    }

    private function random_num($length = 6)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }

    public function clerk()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->user_id == \Yii::$app->user->id) {
                throw new \Exception('本人不可参与');
            }

            $parent = LotteryLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'lottery_id' => $this->lottery_id,
                'user_id' => $this->user_id,
                'child_id' => 0,
            ]);
            if (!$parent) {
                throw new \Exception('邀请用户未参与');
            };

            $self = LotteryLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'lottery_id' => $this->lottery_id,
                'user_id' => \Yii::$app->user->id,
                'child_id' => 0,
            ]);
            if ($self) {
                throw new \Exception('用户已参与');
            };

            $model = LotteryLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'lottery_id' => $this->lottery_id,
                'child_id' => \Yii::$app->user->id,
            ])->andWhere(['in', 'status', [0, 1]])->one();

            if ($model) {
                if ($model->status == 1) {
                    throw new \Exception('用户已参与');
                }
            } else {
                $model = new LotteryLog();
            }

            /* @var Lottery $lottery */
            $lottery = Lottery::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->lottery_id,
                'status' => 1,
                'is_delete' => 0
            ])->with('goods')->one();

            $setting = CommonLottery::getSetting();
            $model->mall_id = \Yii::$app->mall->id;
            $model->user_id = $this->user_id;
            $model->lottery_id = $lottery->id;
            $model->goods_id = $lottery->goods->id;
            $model->child_id = \Yii::$app->user->id;
            $model->lucky_code = $this->getlucky($lottery->id);
            $model->status = $setting->type == 1 ? 0 : 1;
            if ($model->save()) {
                $lottery->invitee += 1;
                if ($model->status == 1) {
                    $lottery->code_num += 1;
                }
                if (!$lottery->save()) {
                    throw new \Exception($this->getErrorMsg($lottery));
                }
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '邀请成功'
                ];
            } else {
                throw new \Exception($this->getErrorMsg($model));
            }
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function goods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $lottery = Lottery::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['is_delete' => 0],
                //['status' => 1],
                ['id' => $this->lottery_id],
                //['<=', 'start_at', date('Y-m-d H:i:s')],
            ])->with(['goods.goodsWarehouse', 'goods.attr'])
                ->asArray()
                ->one();
            if(!$lottery) {
                throw new \Exception('抽奖活动已失效');
            }
            $num = LotteryLog::find()->where(['mall_id' => \Yii::$app->mall->id, 'lottery_id' => $lottery['id'], 'child_id' => 0])->count();
            $user_log = LotteryLog::find()->where(['mall_id' => \Yii::$app->mall->id, 'lottery_id' => $lottery['id'], 'child_id' => 0, 'user_id' => \Yii::$app->user->id])->one();

            $lucky_list = LotteryLog::find()
                ->select('count(user_id) as lucky_num,user_id,child_id,status,lucky_code,created_at,lottery_id')
                ->with('user.userInfo')
                ->where(['mall_id' => \Yii::$app->mall->id, 'lottery_id' => $lottery['id']])
                ->andWhere(['not', ['status' => 0]])
                ->orderBy('created_at desc')
                ->limit(20)
                ->groupBy('user_id')
                ->having(['child_id' => 0])
                ->asArray()
                ->all();
            $attr = $lottery['goods']['attr'][0];

            $newGoods = [
                'attr_id' => $attr['id'],
                'attr_list' => (new Goods())->signToAttr($attr['sign_id'], $lottery['goods']['attr_groups']),
                'pic_url' => \Yii::$app->serializer->decode($lottery['goods']['goodsWarehouse']['pic_url']),
                'video_url' => Video::getUrl($lottery['goods']['goodsWarehouse']['video_url']),
                'name' => $lottery['goods']['goodsWarehouse']['name'],
                'subtitle' => $lottery['goods']['goodsWarehouse']['subtitle'],
                'num' => $num,
                'original_price' => $lottery['goods']['price'],
                'detail' => $lottery['goods']['goodsWarehouse']['detail'],
                'id' => $lottery['goods']['id'],
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'goods' => $newGoods,
                    'lottery' => $lottery,
                    'lucky_list' => $lucky_list,
                    'user_log' => $user_log,
                    'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['lottery_tpl']),
                    'new_status' => $lottery['start_at'] < date('Y-m-d H:i:s') ? $user_log ? 1 : 0 : 2,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
