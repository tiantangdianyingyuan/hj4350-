<?php
namespace app\plugins\scratch\forms\api;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Coupon;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\scratch\forms\common\CommonEcard;
use app\plugins\scratch\forms\common\CommonScratch;
use app\plugins\scratch\models\Scratch;
use app\plugins\scratch\models\ScratchLog;
use app\plugins\scratch\models\ScratchSetting;

class ScratchForm extends Model
{
    public $id;
    public $log;

    private function validated(ScratchSetting $setting)
    {
        $query = ScratchLog::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['user_id' => \Yii::$app->user->id],
            ['<>', 'status', 0]
        ]);

        if ($setting->type == 1) {
            $query->andWhere(['>', 'created_at', date('Y-m-d', $_SERVER['REQUEST_TIME'])]);
        } elseif ($setting->type == 2) {
            $query->andWhere([
                'AND',
                ['>','created_at',$setting->start_at],
                ['<','created_at', $setting->end_at],
            ]);
        }
        //记录数
        $this->log = $query->count();

        //时间检测 配置信息
        if ($setting->start_at > date('Y-m-d H:i:s') || $setting->end_at < date('Y-m-d H:i:s')) {
            throw new \Exception('活动未开始或已过期');
        }

        //积分检测
        if ($setting->deplete_integral_num > 0) {
            $integral = \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->select();
            if ($integral < $setting->deplete_integral_num) {
                throw new \Exception('积分不足');
            }
        }

        //机会检测
        if ($this->log >= $setting->oppty) {
            throw new \Exception("抽奖机会不足");
        }
    }

    private function verify($item)
    {
        if ($item->type == 2) {
            $coupon = Coupon::findOne(['id' => $item->coupon_id, 'is_delete' => 0]);
            if (!$coupon) {
                return false;
            }
        }
        if ($item->type == 4 && false) {
            $goodsAttr = GoodsAttr::findOne(['id' => $item->attr_id, 'is_delete' => 0]);
            if (!$goodsAttr) {
                return false;
            }
        }
        return true;
    }

    public function index()
    {
        try {
            $setting = CommonScratch::getSetting();
            if (!$setting) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '规则尚未配置'
                ];
            }
            $this->validated($setting);

            $scratchLog = ScratchLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'status' => 0,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);

            if (empty($scratchLog)) {
                $scratchLog = $this->lottery($setting);
            } elseif ($scratchLog->type == 5) {
                $scratchLog = \yii\helpers\ArrayHelper::toArray($scratchLog);
                $scratchLog['name'] = CommonScratch::getNewName($scratchLog);
            } else {
                $scratch = Scratch::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $scratchLog->scratch_id,
                    'is_delete' => 0,
                    'status' => 1,
                ])
                    ->with("coupon")
                    ->with("goods.goodsWarehouse")
                    ->one();

                if ($scratch && $this->verify($scratch)) {
                    $scratchLog = \yii\helpers\ArrayHelper::toArray($scratchLog);
                    $scratchLog['name'] = CommonScratch::getNewName($scratch);
                } else {
                    $scratchLog->is_delete = 1;
                    $scratchLog->save();
                    $scratchLog = $this->lottery($setting);
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $scratchLog,
                    'setting' => $setting,
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0,
                    'setting' => $setting,
                ]
            ];
        }
    }


    private function lottery(ScratchSetting $setting)
    {
        $scratch = Scratch::findAll(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1]);

        $suc = [];
        foreach ($scratch as $v) {
            if ($v->stock > 0 && $this->verify($v)) {
                $suc[$v->id] = $v->stock;
            }
        };

        $max = array_sum($suc);
        $rand = $this->randomNum(1, 10000);
        if ($rand < $setting->probability && $max > 0) {
            $id = $this->getRand($suc);

            /* @var Scratch $scratch */
            $scratch = Scratch::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $id,
                'is_delete' => 0,
                'status' => 1,
            ])
            ->with("coupon")
            ->with("goods")
            ->one();

            $scratchLog = new ScratchLog();
            $scratchLog->mall_id = \Yii::$app->mall->id;
            $scratchLog->user_id = \Yii::$app->user->id;
            $scratchLog->scratch_id = $scratch->id;
            $scratchLog->status = 0;
            $scratchLog->type = $scratch->type;
            $scratchLog->num = $scratch->num;
            $scratchLog->price = floatval($scratch->price);
            $scratchLog->detail = '';
            if ($scratchLog->type == 4) {
                $scratchLog->goods_id = $scratch->goods->id;
            } elseif ($scratchLog->type == 2) {
                $scratchLog->detail = (string)$scratch->coupon_id;
            }

            $t = \Yii::$app->db->beginTransaction();
            $sql = 'select * from ' . Scratch::tableName() . ' where mall_id = ' . \Yii::$app->mall->id . ' and id = ' . $id . ' and is_delete = 0 and status = 1 for update';
            $form = \Yii::$app->db->createCommand($sql)->queryOne();
            if ($form['stock'] > 0) {
                $scratch->stock = $form['stock'] - 1;
                if (!$scratch->save()) {
                    $t->rollBack();
                    throw new \Exception($this->getErrorMsg($scratch));
                }
            } else {
                $scratchLog->type = 5;
                $scratchLog->detail = '';
                $scratchLog->num = 0;
                $scratchLog->price = 0;
            }

            if ($scratchLog->save()) {
                $t->commit();
                $scratchLog = \yii\helpers\ArrayHelper::toArray($scratchLog);
                $scratchLog['name'] = CommonScratch::getNewName($scratch);
                return $scratchLog;
            } else {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($scratchLog));
            }
        } else {
            $scratchLog = new ScratchLog();
            $scratchLog->mall_id = \Yii::$app->mall->id;
            $scratchLog->user_id = \Yii::$app->user->id;
            $scratchLog->scratch_id = 0;
            $scratchLog->detail = '';
            $scratchLog->status = 0;
            $scratchLog->type = 5;

            if ($scratchLog->save()) {
                $scratchLog = \yii\helpers\ArrayHelper::toArray($scratchLog);
                $scratchLog['name'] = CommonScratch::getNewName($scratchLog);
                return $scratchLog;
            } else {
                throw new \Exception($this->getErrorMsg($scratchLog));
            }
        }
    }

    public function receive()
    {
        //检测
        $scratchLog = ScratchLog::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'status' => 0,
            'is_delete' => 0,
            'id' => $this->id
        ]);

        if (empty($scratchLog)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据异常',
            ];
        }

        try {
            $setting = CommonScratch::getSetting();
            $this->validated($setting);

            //扣积分
            if ($setting->deplete_integral_num > 0) {
                $desc = '刮刮卡消耗' . (int)$setting->deplete_integral_num . '积分';
                \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->sub((int)$setting->deplete_integral_num, $desc);
            }

            $scratchLog->status = 1;
            if ($scratchLog->save()) {
                //兑奖
                $data = $this->send($scratchLog);
                if ($data['code'] == ApiCode::CODE_SUCCESS) {
                } else {
                    return $data;
                }
            } else {
                throw new \Exception($this->getErrorMsg($scratchLog));
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        try {
            $this->validated($setting);
            $list = $this->lottery($setting);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list,
                    'setting' => $setting,
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage(),
                'data' => [
                    'list' => [],
                    'setting' => $setting,
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0
                ]
            ];
        }
    }

    //领取
    public function send(scratchLog $scratchLog)
    {
        //余额
        if ($scratchLog->type == 1) {
            if (floatval($scratchLog->price) <= 0) {
                return true;
            }

            $t = \Yii::$app->db->beginTransaction();
            $desc = '刮刮卡获赠' . (float)$scratchLog->price . '元';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->balance->add((float)$scratchLog->price, $desc);

            //状态
            $scratchLog->status = 2;
            $scratchLog->raffled_at = date('Y-m-d H:i:s');
            if ($scratchLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '余额领取成功',
                    'data' => $scratchLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($scratchLog);
            }
        }

        if ($scratchLog->type == 2) {
            $t = \Yii::$app->db->beginTransaction();

            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = \Yii::$app->mall;
            $commonCoupon->user = \Yii::$app->user->identity;

            $coupon = Coupon::findOne(['id' => $scratchLog->detail]);
            $class = new ScratchLogCouponRelation($coupon, $scratchLog);
            $commonCoupon->receive($coupon, $class, '刮刮卡发放');
            //状态
            $scratchLog->status = 2;
            $scratchLog->raffled_at = date('Y-m-d H:i:s');

            if ($scratchLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '优惠券领取成功',
                    'data' => $scratchLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($scratchLog);
            }
        }
        if ($scratchLog->type == 3) {
            $t = \Yii::$app->db->beginTransaction();

            $desc = '刮刮卡获赠' . (int)$scratchLog->num . '积分';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->add((int)$scratchLog->num, $desc);

            //状态
            $scratchLog->status = 2;
            $scratchLog->raffled_at = date('Y-m-d H:i:s');
            if ($scratchLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '积分领取成功',
                    'data' => $scratchLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($scratchLog);
            }
        }

        if ($scratchLog->type == 4) {
            $scratchLog->token = \Yii::$app->security->generateRandomString();
            $scratchLog->save();
            CommonEcard::getCommon()->setEcardScratch($scratchLog);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '商品领取成功',
                'data' => $scratchLog->id
            ];
        }

        if ($scratchLog->type == 5) {
            $scratchLog->status = 2;
            $scratchLog->raffled_at = date('Y-m-d H:i:s');
            $scratchLog->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '领取成功',
                'data' => $scratchLog->id
            ];
        }
    }


    private function randomNum($min, $max)
    {
        return mt_rand() % ($max - $min + 1) + $min;
    }

    private function getRand($probability)
    {
        $max = array_sum($probability);
        foreach ($probability as $key => $val) {
            $rand_number = $this->randomNum(1, $max);
            if ($rand_number <= $val) {
                return $key;
            } else {
                $max -= $val;
            }
        }
    }

    public function setting()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'setting' => CommonScratch::getSetting(),
            ]
        ];
    }
}
