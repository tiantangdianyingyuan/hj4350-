<?php

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\mall\live\CommonLive;
use app\models\LiveGoods;
use app\models\Model;

class GoodsForm extends Model
{
    public $page = 1;
    public $limit = 20;
    public $status = 0;
    public $goods_id;

    private $second = 600;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'goods_id'], 'integer'],
        ];
    }

    public function getList()
    {
        try {
            $accessToken = CommonLive::checkAccessToken();
            $cacheKey = $this->getCacheKey();
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token={$accessToken}";
            $res = CommonLive::get($api, [
                'offset' => $this->page * $this->limit - $this->limit,
                'limit' => $this->limit,
                'status' => $this->status,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] == 0) {
                $res['goods'] = $this->getNewList($res['goods']);
                \Yii::$app->cache->set($cacheKey, $res, $this->second);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => "请求成功",
                    'data' => [
                        'list' => $res['goods'],
                        'pageCount' => ceil($res['total'] / $this->limit),
                        'total' => $res['total'],
                    ],
                ];
            } else {
                throw new \Exception($res['errmsg']);
            }

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function getNewList($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $item['price'] = price_format($item['price'], 2);
            $item['price2'] = price_format($item['price2'], 2);
            $item['price_text'] = $this->getNewPrice($item);

            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $item['goodsId']])->one();
            $item['audit_id'] = $liveGoods ? $liveGoods->audit_id : 0;
            $newList[] = $item;
        }

        return $newList;
    }

    private function getNewPrice($item)
    {
        $priceText = '';
        if ($item['priceType'] == 1) {
            // 一口价
            $priceText = '一口价 | ' . $item['price'];
        } elseif ($item['priceType'] == 2) {
            // 区间价
            $priceText = '价格区间 | ' . $item['price'] . ' — ' . $item['price2'];
        } elseif ($item['priceType'] == 3) {
            // 折扣价
            $priceText = '折扣价 | ' . '<del>' . $item['price'] . '</del>' . '  ' . $item['price2'];
        }

        return $priceText;
    }

    private function getCacheKey()
    {
        return 'GOODS_LIST_' . \Yii::$app->mall->id . $this->status . $this->page;
    }

    public function getDetail()
    {
        try {
            $accessToken = CommonLive::checkAccessToken();
            $goodsList = $this->getAuditStatus($accessToken, [$this->goods_id]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'goods' => $goodsList[0],
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function getAuditStatus($accessToken, $goodsIds = array())
    {
        try {
            $accessToken = CommonLive::checkAccessToken();
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxa/business/getgoodswarehouse?access_token={$accessToken}";
            // dd($this->goods_id);
            $res = CommonLive::post($api, [
                'goods_ids' => $goodsIds,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res['errcode'] == 0) {
                return $res['goods'];
            } else {
                throw new \Exception($res['errmsg']);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function deleteGoods()
    {
        try {
            if (!$this->goods_id) {
                throw new \Exception('请传入商品ID');
            }

            $accessToken = CommonLive::checkAccessToken();
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/delete?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'goodsId' => $this->goods_id,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] == 0) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => "删除成功",
                ];
            } else {
                throw new \Exception($res['errmsg']);
            }

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function submitAudit()
    {
        try {
            if (!$this->goods_id) {
                throw new \Exception('请传入商品ID');
            }

            $accessToken = CommonLive::checkAccessToken();
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/audit?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'goodsId' => $this->goods_id,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if (!$res['errcode'] == 0) {
                throw new \Exception($res['errmsg']);
            }

            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $this->goods_id])->one();
            if (!$liveGoods) {
                $liveGoods = new LiveGoods();
                $liveGoods->goods_id = $this->goods_id;
                $liveGoods->mall_id = \Yii::$app->mall->id;
            }

            $liveGoods->audit_id = $res['auditId'];
            $res = $liveGoods->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($liveGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "提交成功",
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function cancelAudit()
    {
        try {
            if (!$this->goods_id) {
                throw new \Exception('请传入商品ID');
            }

            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $this->goods_id])->one();
            if (!$liveGoods) {
                throw new \Exception('该商品无审核ID,无法撤销审核,请到微信后台进行该操作');
            }

            $accessToken = CommonLive::checkAccessToken();
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'goodsId' => $liveGoods->goods_id,
                'auditId' => $liveGoods->audit_id,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] == 0) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => "撤销成功",
                ];
            } else {
                throw new \Exception($res['errmsg']);
            }

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
