<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\mall\live\CommonUpload;
use app\forms\mall\live\GoodsForm;
use app\models\LiveGoods;
use app\models\Model;

class GoodsEditForm extends Model
{
    public $goods_name;
    public $pic_url;
    public $price;
    public $price1;
    public $price2;
    public $price3;
    public $price4;
    public $price_type;
    public $page_url;
    public $goods_id;

    private $newPrice = 0;
    private $newPrice2 = 0;

    public function rules()
    {
        return [
            [['goods_name', 'pic_url', 'page_url', 'price_type'], 'required'],
            [['goods_name', 'pic_url', 'page_url', 'price_type', 'price', 'price1', 'price2', 'price3',
                'price4'], 'string'],
            [['goods_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_name' => '商品名称',
            'pic_url' => '商品图片',
            'page_url' => '小程序路径',
            'price_type' => '价格类型',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->goods_id) {
                return $this->updateGoods();
            }
            $this->setPrice();
            $this->checkData();
            $accessToken = CommonLive::checkAccessToken();
            $mediaId = (new CommonUpload())->uploadImage($accessToken, $this->pic_url, 10, [], [300, 300]);
            // 接口每天上限调用500次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'goodsInfo' => [
                    'coverImgUrl' => $mediaId,
                    'name' => $this->goods_name,
                    'priceType' => $this->price_type,
                    'price' => $this->newPrice,
                    'price2' => $this->newPrice2,
                    'url' => $this->page_url,
                ],
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
                throw new \Exception($res['errmsg']);
            }

            $liveGoods = new LiveGoods();
            $liveGoods->mall_id = \Yii::$app->mall->id;
            $liveGoods->goods_id = $res['goodsId'];
            $liveGoods->audit_id = $res['auditId'];
            $res = $liveGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($liveGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "添加成功",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function updateGoods()
    {
        try {
            $this->setPrice();
            $this->checkData();
            $accessToken = CommonLive::checkAccessToken();
            $goodsList = (new GoodsForm())->getAuditStatus($accessToken, [$this->goods_id]);

            if (empty($goodsList)) {
                throw new \Exception('商品数据异常');
            }
            // 0：未审核，1：审核中，2:审核通过，3审核失败
            $auditStatus = $goodsList[0]['audit_status'];

            if ($auditStatus == 1) {
                throw new \Exception('审核中的商品不允许更新');
            }

            $mediaId = (new CommonUpload())->uploadImage($accessToken, $this->pic_url, 10, [], [300, 300]);
            $goodsInfo = [
                'coverImgUrl' => $mediaId,
                'name' => $this->goods_name,
                'priceType' => $this->price_type,
                'price' => $this->newPrice,
                'price2' => $this->newPrice2,
                'url' => $this->page_url,
                'goodsId' => $this->goods_id,
            ];

            // 审核通过的商品只允许更新价格类型及价格
            if ($auditStatus == 2) {
                $goodsInfo = [
                    'priceType' => $this->price_type,
                    'price' => $this->newPrice,
                    'price2' => $this->newPrice2,
                    'goodsId' => $this->goods_id,
                ];
            }

            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/update?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'goodsInfo' => $goodsInfo,
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
                throw new \Exception($res['errmsg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "更新成功",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function updateErrorMsg($res)
    {
        if ($res['errmsg'] == 'goods url not exist' || $res['errmsg'] == 'goods url invalid') {
            throw new \Exception('无效的页面路由');
        }

        if ($res['errmsg'] == 'product image width or height more than 300px') {
            throw new \Exception('商品图片宽高最大限制 300 * 300');
        }
    }

    private function checkData()
    {
        $max = 999999;
        $min = 0;

        if ($this->price_type == 2 || $this->price_type == 3) {
            if ($this->newPrice > $max || $this->newPrice2 > $max) {
                throw new \Exception('价格不能大于' . $max);
            }

            if ($this->newPrice <= $min || $this->newPrice2 <= $min) {
                throw new \Exception('价格不能小于等于' . $min);
            }
        } else {
            if ($this->newPrice > $max) {
                throw new \Exception('价格不能大于' . $max);
            }

            if ($this->newPrice <= $min) {
                throw new \Exception('价格不能小于等于' . $min);
            }
        }

        if ($this->price_type == 2 && $this->newPrice > $this->newPrice2) {
            throw new \Exception('请输入正确的区间价');
        }

        if ($this->price_type == 3 && $this->newPrice <= $this->newPrice2) {
            throw new \Exception('现价 不能大于等于 原价');
        }

        if (strlen($this->goods_name) > 14 * 3) {
            throw new \Exception('最多可输入14个汉字');
        }

        $array = [1, 2, 3];
        if (!in_array($this->price_type, $array)) {
            throw new \Exception('价格类型有效参数' . implode(' ', $array));
        }
    }

    private function setPrice()
    {
        switch ($this->price_type) {
            case '1':
                $this->newPrice = $this->price;
                $this->newPrice2 = 0;
                break;
            case '2':
                $this->newPrice = $this->price1;
                $this->newPrice2 = $this->price2;
                break;
            case '3':
                $this->newPrice = $this->price3;
                $this->newPrice2 = $this->price4;
                break;
            default:
                break;
        }
    }
}
