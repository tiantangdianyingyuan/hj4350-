<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 10:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\template\TemplateList;
use app\forms\common\video\Video;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;

/**
 * @property Mall $mall
 * @property User $user
 */
class GoodsForm extends ApiModel
{

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_id' => '砍价商品id'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /* @var BargainGoods $bargainGoods */
            $bargainGoods = CommonBargainGoods::getCommonGoods($this->mall)->getGoods($this->goods_id);

            if ($bargainGoods->goods->status == 0) {
                throw new \Exception('砍价活动未上线');
            }
            $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);
            /* @var BargainOrder $bargainOrder */
            $bargainOrder = null;
            if ($this->user) {
                $bargainOrder = $commonBargainOrder->getUserOrder($bargainGoods->id, $this->user->id);
                if ($bargainOrder && $bargainOrder->resetTime <= 0) {
                    $bargainOrder = null;
                }
            }
            if ($bargainOrder) {
                $bargainInfo = $commonBargainOrder->getBargainInfo($bargainOrder);
            } else {
                $bargainInfo = '';
            }

            $orderConfig = \Yii::$app->plugin->currentPlugin->getOrderConfig();
            $share = 0;
            $commonGoods = CommonGoodsDetail::getCommonGoodsDetail($this->mall);

            //todo 过公共方法，用于记录足迹，需优化
            $commonGoods->getGoods($this->goods_id);

            $commonGoods->goods = $bargainGoods->goods;
            $commonGoods->user = \Yii::$app->user->identity;
            if ($orderConfig->is_share == 1) {
                $share = $commonGoods->getShare();
            }
            $service = $commonGoods->getServices();

            $goodsWarehouse = $bargainGoods->goods->goodsWarehouse;
            $goodsAttr = $bargainGoods->goods->resetAttr();
            $commonGoodsDetail = CommonGoodsDetail::getCommonGoodsDetail();
            $commonGoodsDetail->goods = $bargainGoods->goods;
            $commonGoodsDetail->setOffer($commonGoodsDetail->goods->goodsWarehouse->type != 'ecard');
            $commonGoodsDetail->setShipping($commonGoodsDetail->goods->goodsWarehouse->type != 'ecard');
            $commonGoodsDetail->setIsLimit($commonGoodsDetail->goods->goodsWarehouse->type != 'ecard');
            $sales = ($bargainGoods->userOrderList ? count($bargainGoods->userOrderList) : 0) + $bargainGoods->goods->virtual_sales; // 正在进行砍价人数
            $newGoods = [
                'name' => $goodsWarehouse->name,
                'subtitle' => $goodsWarehouse->subtitle,
                'pic_url' => \Yii::$app->serializer->decode($goodsWarehouse->pic_url),
                'cover_pic' => $goodsWarehouse->cover_pic,
                'video_url' => Video::getUrl($goodsWarehouse->video_url),
                'unit' => $goodsWarehouse->unit,
                'price' => $bargainGoods->goods->price,
                'detail' => $goodsWarehouse->detail,
                'goods_id' => $bargainGoods->goods->id,
                'goods_attr_id' => $bargainGoods->goodsAttr->id,
                'select_attr_group' => $goodsAttr[$bargainGoods->goodsAttr->sign_id],
                'share' => $share,

                'min_price' => $bargainGoods->min_price,
                'stock' => CommonEcard::getCommon()->getEcardStock($bargainGoods->stock, $bargainGoods->goods),
                'begin_time' => $bargainGoods->begin_time,
                'end_time' => $bargainGoods->end_time,
                'type' => $bargainGoods->type,
                'time' => $bargainGoods->time,
                'join_num' => $sales,
                'bargain_info' => $bargainInfo,
                'goods' => $bargainGoods->goods->toArray(),
                'service' => $service,
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['bargain_success_tpl', 'bargain_fail_tpl']),
                'goods_marketing' => $commonGoodsDetail->getGoodsMarketing(),
                'goods_marketing_award' => $commonGoodsDetail->getGoodsMarketingAward(),
                'express' => $commonGoodsDetail->getExpressPrice(),
                'goods_coupon_center' => $commonGoodsDetail->getGoodsCouponCenter(),
                'guarantee_title' => $commonGoodsDetail->getGuaranteeTitle(),
                'guarantee_pic' => $commonGoodsDetail->getGuaranteePic(),
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'bargain' => $newGoods
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'data' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}
