<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\goods;


use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\events\GoodsEvent;
use app\events\GoodsStatusEvent;
use app\forms\common\mch\MchSettingForm;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGoods;

class GoodsBase extends Model
{
    public $id;
    public $goods_id;
    public $search;
    public $page;
    public $status;
    public $sort;
    public $batch_ids = [];
    public $is_all;
    public $freight_id;
    public $goods_name;
    public $give_integral;
    public $give_integral_type;
    public $forehead_integral;
    public $forehead_integral_type;
    public $accumulative;
    public $continue_goods_count;
    public $continue_order_count;
    public $is_goods_confine;
    public $is_order_confine;
    public $goods_price_type;
    public $goods_price;
    public $goods_price_update_type;
    public $shipping_id;

    public $cat_id;

    public $mallMembers = [];
    /**
     * @var Goods
     */
    public $goods;
    public $plugin_sign;


    public function rules()
    {
        return [
            [['id', 'status', 'goods_id', 'sort', 'freight_id', 'cat_id',
                'give_integral', 'give_integral_type', 'forehead_integral', 'forehead_integral_type',
                'accumulative', 'is_all', 'is_goods_confine', 'is_order_confine', 'continue_goods_count',
                'continue_order_count', 'goods_price_type', 'goods_price_update_type', 'shipping_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['freight_id'], 'default', 'value' => 0],
            [['search', 'goods_name', 'plugin_sign', 'goods_price'], 'string'],
            [['batch_ids'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function findGoods()
    {
        $goods = Goods::find()->with('goodsWarehouse')->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        $this->goods = $goods;
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->findGoods();
            $goods = $this->goods;
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            $goods->is_delete = 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }
            \Yii::$app->trigger(Goods::EVENT_DESTROY, new GoodsEvent(['goods' => $goods]));

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function switchStatus()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;

            try {
                $exchange = \Yii::$app->plugin->getPlugin('exchange');
                $exchange->breakGoodsStatus($this->id, $goods->status);
            } catch (ClassNotFoundException $e) {
            }

            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            $this->setMchGoodsStatus($goods);

            \Yii::$app->trigger(Goods::EVENT_STATUS, new GoodsStatusEvent(['id' => $this->id, 'status_after' => $goods->status]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    // 更新多商户商品上架状态
    private function setMchGoodsStatus($goods)
    {
        if (\Yii::$app->user->identity->mch_id > 0) {
            $mchGoods = MchGoods::findOne(['goods_id' => $goods->id]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status) {
                $mchGoods->status = 2;
            } else {
                $mchGoods->status = 0;
            }
            $mchGoods->remark = '';
            $res = $mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($mchGoods));
            }
        }
    }

    public function switchQuickShop()
    {
        try {
            $goods = MallGoods::findOne([
                'goods_id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->is_quick_shop = $goods->is_quick_shop ? 0 : 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function editSort()
    {
        try {
            /** @var Goods $goods */
            $goods = Goods::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->sort = $this->sort;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    // 批量删除
    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                // 添加的话多商户插件商品列表无法删除
                // 'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];

            $goodsList = Goods::find()->where($where)->all();
            $res = Goods::updateAll([
                'is_delete' => 1
            ], $where);
            foreach ($goodsList as $goods) {
                \Yii::$app->trigger(Goods::EVENT_DESTROY, new GoodsEvent(['goods' => $goods]));
            }
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    // 批量更新商品状态
    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $isGoodsAudit = 1;
        try {
            $mchPlugin = \Yii::$app->plugin->getPlugin('mch');
            if ($mchPlugin) {
                $mchSetting = (new MchSettingForm())->search();
                $isGoodsAudit = $mchSetting['is_goods_audit'];
            }
        } catch (\Exception $exception) {
        }

        try {
            // 是多商户 并且 商品需要审核
            if (\Yii::$app->user->identity->mch_id > 0 && $isGoodsAudit) {
                $res = $this->setMchGoodsApplyStatus();
            } else {
                if ($this->is_all) {
                    $where = [
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->user->identity->mch_id,
                        'sign' => $this->plugin_sign,
                        'is_delete' => 0,
                    ];
                } else {
                    $where = [
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->user->identity->mch_id,
                        'sign' => $this->plugin_sign,
                        'id' => $this->batch_ids,
                    ];
                }

                try {
                    $exchange = \Yii::$app->plugin->getPlugin('exchange');
                    $exchange->breakGoodsStatus($this->batch_ids, $this->status);
                } catch (ClassNotFoundException $e) {
                }

                $res = Goods::updateAll(['status' => $this->status], $where);

                \Yii::$app->trigger(Goods::EVENT_STATUS, new GoodsStatusEvent(['id' => $this->batch_ids,'status_after' => $this->status]));
                // 如果是多商户 则需更新商户商品状态
                $this->updateMchGoodsStatus();
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 批量更新商品 是否使用会员价
    public function batchUpdateGoodsMember()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }

        $res = Goods::updateAll(['is_level' => $this->status], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateFreight()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }

        $res = Goods::updateAll(['freight_id' => $this->freight_id], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateFreeDelivery()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }

        $res = Goods::updateAll(['shipping_id' => $this->shipping_id], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateConfineCount()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }

            if ($this->continue_goods_count < 0 && !$this->is_goods_confine) {
                throw new \Exception('限购商品数量不能小于0');
            }

            if ($this->continue_order_count < 0 && !$this->is_order_confine) {
                throw new \Exception('限购订单数量不能小于0');
            }

            $goodsCount = (int)$this->continue_goods_count;
            if ($this->is_goods_confine || $goodsCount < 0) {
                $goodsCount = -1;
            }

            $orderCount = (int)$this->continue_order_count;
            if ($this->is_order_confine || $orderCount < 0) {
                $orderCount = -1;
            }

            $res = Goods::updateAll(['confine_count' => $goodsCount, 'confine_order_count' => $orderCount], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    // 批量更新赠送积分
    public function batchUpdateIntegral()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }

            if ($this->give_integral < 0) {
                throw new \Exception('积分赠送不能小于0');
            }

            if ($this->forehead_integral < 0) {
                throw new \Exception('积分抵扣不能小于0');
            }

            $res = Goods::updateAll([
                'give_integral' => (int)$this->give_integral <= 0 ? 0 : $this->give_integral,
                'give_integral_type' => $this->give_integral_type == 1 ? $this->give_integral_type : 2,
                'forehead_integral' => $this->forehead_integral <= 0 ? 0 : $this->forehead_integral,
                'forehead_integral_type' => $this->forehead_integral_type == 1 ? $this->forehead_integral_type : 2,
                'accumulative' => $this->accumulative == 1 ? $this->accumulative : 0,
            ], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateGoodsName()
    {
        try {
            /** @var Goods $goods */
            $goods = Goods::find()->where(['id' => $this->goods_id])->with('goodsWarehouse')->one();
            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->goodsWarehouse->name = $this->goods_name;
            $res = $goods->goodsWarehouse->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods->goodsWarehouse));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function setMchGoodsApplyStatus()
    {
        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'status' => [0, 3]
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'goods_id' => $this->batch_ids,
                'status' => [0, 3]
            ];
        }

        $res = MchGoods::updateAll([
            'status' => 1,
            'remark' => '申请上架'
        ], $where);

        // 有更新再发送模板消息
        if ($res) {
            $this->sendMpTplMsg();
        }

        return $res;
    }

    /**
     *  更改多商户商品状态
     */
    private function updateMchGoodsStatus()
    {
        if (\Yii::$app->user->identity->mch_id > 0) {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_id' => $this->batch_ids,
                ];
            }
            if ($this->status) {
                $res = MchGoods::updateAll([
                    'status' => 2,
                    'remark' => ''
                ], $where);
            } else {
                $res = MchGoods::updateAll([
                    'status' => 0,
                    'remark' => '申请上架'
                ], $where);
            }
        }
    }

    /**
     * 发给管理员公众号消息
     */
    private function sendMpTplMsg()
    {
        try {
            try {
                $mch = Mch::findOne(\Yii::$app->user->identity->mch_id);
                $mchName = $mch->store->name;
            } catch (\Exception $exception) {
                $mchName = '商城商户';
            }

            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'mchGoodApplyTpl';
            $tplMsg->params = [
                'goods' => '商户：' . $mchName . '商品申请上架'
            ];
            $tplMsg->sendTemplate(new MpTplMsgSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }

    public function batchUpdateGoodsPrice()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->batch_ids,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'sign' => $this->plugin_sign,
            ];
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods = Goods::find()->where($where)->with('attr')->all();
            $count = 0;
            /** @var Goods $item */
            foreach ($goods as $item) {
                $item->price = $this->getNewPrice($item->price);
                $res = $item->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($item));
                }

                foreach ($item->attr as $aItem) {
                    $newPrice = $this->getNewPrice($aItem->price);
                    $aItem->price = $newPrice;
                    $res = $aItem->save();
                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($aItem));
                    }
                }
                $count += 1;

                $plugin = \Yii::$app->plugin->getPlugin($item->sign);
                // 判断插件是否有hasVideoGoodsList这个方法，没有的则使用商城的
                if (method_exists($plugin, 'updateGoodsPrice')) {
                    $res = $plugin->updateGoodsPrice($item);
                }
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $count
                ]
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    protected function getNewPrice($newPrice)
    {
        if ($this->goods_price_type == 1) {
            if ($this->goods_price_update_type == 1) {
                // 固定金额 提高
                $newPrice += $this->goods_price;
            } elseif ($this->goods_price_update_type == 2) {
                // 固定金额 降低
                $newPrice -= $this->goods_price;
            }
        } elseif ($this->goods_price_type == 2) {
            if ($this->goods_price_update_type == 1) {
                // 百分比 提高
                $newPrice += $newPrice * ($this->goods_price / 100);
            } elseif ($this->goods_price_update_type == 2) {
                // 百分比 降低
                $newPrice -= $newPrice * ($this->goods_price / 100);
            }
        }

        $maxPrice = '99999999.00';
        $minPrice = '0';
        $newPrice = $newPrice > $maxPrice ? $maxPrice : $newPrice;
        $newPrice = $newPrice < $minPrice ? $minPrice : $newPrice;

        return $newPrice;
    }
}