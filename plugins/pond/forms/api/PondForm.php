<?php

namespace app\plugins\pond\forms\api;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\pond\forms\common\CommonEcard;
use app\plugins\pond\forms\common\CommonPond;
use app\plugins\pond\models\Pond;
use app\plugins\pond\models\PondLog;
use app\plugins\pond\models\PondSetting;

class PondForm extends Model
{
    public $log;

    private function validated(PondSetting $setting)
    {
        $query = PondLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
        ]);

        if ($setting->type == 1) {
            $query->andWhere(['>', 'created_at', date('Y-m-d', $_SERVER['REQUEST_TIME'])]);
        } elseif ($setting->type == 2) {
            $query->andWhere([
                'AND',
                ['>', 'created_at', $setting->start_at],
                ['<', 'created_at', $setting->end_at],
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
            try {
                $integral = \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->select();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            if ($integral < $setting->deplete_integral_num) {
                throw new \Exception('积分不足');
            }
        }

        //机会检测
        if ($this->log >= $setting->oppty) {
            throw new \Exception("抽奖机会不足");
        }
    }

    public function index()
    {
        try {
            $list = Pond::find()->where([
                'mall_id' => \Yii::$app->mall->id,
            ])
                ->with(['goods.goodsWarehouse', 'coupon'])
                ->asArray()
                ->all();

            $setting = CommonPond::getSetting();
            if (!$setting) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '规则尚未配置'
                ];
            }

            array_walk($list, function (&$v) {
                $v['name'] = CommonPond::getNewName($v);
            });
            unset($v);

            //验证
            $this->validated($setting);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0,
                    'setting' => $setting,
                    'list' => $list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0,
                    'setting' => $setting,
                    'list' => $list,
                ]
            ];
        }
    }

    //抽奖
    public function lottery()
    {
        try {
            $setting = CommonPond::getSetting();
            //验证
            $this->validated($setting);
            //随机数
            $this->random($setting, $id, $err);
            //记录
            $this->saveLog($id, $setting->deplete_integral_num, $err);

            $oppty = $setting->oppty - $this->log - 1;
            $integral = \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->select();
            if ($oppty <= 0) {
                $msg = '抽奖机会不足';
            } elseif ($integral < $setting->deplete_integral_num) {
                $msg = '积分不足';
            } else {
                $msg = '';
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg,
                'data' => [
                    'oppty' => $oppty > 0 ? $oppty : 0,
                    'id' => $id
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'oppty' => $setting->oppty > $this->log ? $setting->oppty - $this->log : 0,
                    'setting' => $setting
                ]
            ];
        }
    }

    private function random(PondSetting $setting, &$id, &$err)
    {
        $pond = Pond::find()->where(['mall_id' => \Yii::$app->mall->id])->all();

        $suc = $err = [];
        foreach ($pond as $v) {
            $this->verify($v);

            if ($v->type == 5) {
                $err[$v->id] = $v->id;
            } elseif ($v->stock > 0) {
                $suc[$v->id] = $v->stock;
            }
        };

        $max = array_sum($suc);
        if (empty($err)) {
            if ($max > 0) {
                $id = $this->getRand($suc);
            } else {
                throw new \Exception('库存不足');
            }
        } else {
            $rand = $this->randomNum(1, 10000);
            if ($rand < $setting->probability && $max > 0) {
                $id = $this->getRand($suc);
            } else {
                $id = array_rand($err, 1);
            }
        }
    }

    //奖品核实
    private function verify(&$item)
    {
        if ($item->type == 2) {
            $coupon = Coupon::findOne(['id' => $item->coupon_id, 'is_delete' => 0]);
            if (!$coupon) {
                $item->stock = 0;
            }
        }
        if ($item->type == 4) {
            $goods = Goods::findOne(['id' => $item->goods_id]);
            $item->stock = CommonEcard::getCommon()->getEcardStock($item->stock, $goods);
        }
    }

    //记录
    private function saveLog($id, $deplete_integral_num, $err)
    {
        /* @var Pond $pond */
        $pond = Pond::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $id,
        ])
            ->with(['goods.goodsWarehouse', 'coupon', 'goods.attr'])
            ->one();

        $pondLog = new PondLog();
        $pondLog->mall_id = \Yii::$app->mall->id;
        $pondLog->user_id = \Yii::$app->user->id;
        $pondLog->type = $pond->type;
        $pondLog->detail = '';
        $pondLog->goods_id = 0;
        $pondLog->num = $pond->num;
        $pondLog->status = 0;
        $pondLog->pond_id = $pond->id;
        $pondLog->price = floatval($pond->price);
        if ($pondLog->type == 4) {
            $pondLog->goods_id = $pond->goods->id;
            $pondLog->token = \Yii::$app->security->generateRandomString();
            CommonEcard::getCommon()->setEcardPond($pondLog);
        } elseif ($pondLog->type == 2) {
            $pondLog->detail = (string)$pond->coupon_id;
        }

        $t = \Yii::$app->db->beginTransaction();
        if ($pond->type != 5) {
            $sql = 'select * from ' . Pond::tableName() . ' where mall_id = ' . \Yii::$app->mall->id . ' and id = ' . $id . ' for update';
            $form = \Yii::$app->db->createCommand($sql)->queryOne();

            if ($form['stock'] > 0) {
                $pond->stock = $form['stock'] - 1;
                if (!$pond->save()) {
                    $t->rollBack();
                    throw new \Exception($this->getErrorMsg($pond));
                }
            } else {
                if (empty($err)) {
                    throw new \Exception('网络异常');
                } else {
                    $pondLog->type = 5;
                    $pondLog->pond_id = array_rand($err, 1);
                    $pondLog->detail = '';
                    $pondLog->num = 0;
                    $pondLog->price = 0;
                };
            }
        };

        if ($deplete_integral_num > 0) {
            $desc = '九宫格抽奖消耗' . $deplete_integral_num . '积分';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->sub((int)$deplete_integral_num, $desc);
        }

        if ($pondLog->save()) {
            $t->commit();
            $this->send($pondLog);
        } else {
            $t->rollBack();
            throw new \Exception($this->getErrorMsg($pondLog));
        }
    }

    //领取
    public function send(PondLog $pondLog)
    {
        //余额
        if ($pondLog->type == 1) {
            if (floatval($pondLog->price) <= 0) {
                return true;
            }

            $t = \Yii::$app->db->beginTransaction();

            $desc = '九宫格抽奖获赠' . (float)$pondLog->price . '元';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->balance->add((float)$pondLog->price, $desc);

            //状态
            $pondLog->status = 1;
            $pondLog->raffled_at = date('Y-m-d H:i:s');
            if ($pondLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '余额领取成功',
                    'data' => $pondLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($pondLog);
            }
        }

        if ($pondLog->type == 2) {
            $t = \Yii::$app->db->beginTransaction();

            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = \Yii::$app->mall;
            $commonCoupon->user = \Yii::$app->user->identity;

            $coupon = Coupon::findOne(['id' => $pondLog->detail]);
            $class = new PondLogCouponRelation($coupon, $pondLog);
            $commonCoupon->receive($coupon, $class, '九宫格发放');

            //状态
            $pondLog->status = 1;
            $pondLog->raffled_at = date('Y-m-d H:i:s');

            if ($pondLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '优惠券领取成功',
                    'data' => $pondLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($pondLog);
            }
        }


        if ($pondLog->type == 3) {
            $t = \Yii::$app->db->beginTransaction();

            $desc = '九宫格抽奖获赠' . (int)$pondLog->num . '积分';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->add((int)$pondLog->num, $desc);

            //状态
            $pondLog->status = 1;
            $pondLog->raffled_at = date('Y-m-d H:i:s');
            if ($pondLog->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '积分领取成功',
                    'data' => $pondLog->id
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($pondLog);
            }
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
                'setting' => CommonPond::getSetting(),
            ]
        ];
    }
}
