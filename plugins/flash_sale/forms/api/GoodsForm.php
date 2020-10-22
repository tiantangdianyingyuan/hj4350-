<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 17:31
 */

namespace app\plugins\flash_sale\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\Model;
use app\models\User;
use app\plugins\flash_sale\forms\common\CommonGoods;
use app\plugins\flash_sale\forms\common\CommonSetting;
use app\plugins\flash_sale\models\FlashSaleActivity;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\Goods;
use Exception;
use Yii;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $keyword;
    public $type; // 1为正在进行中的活动  2为下一场活动

    public function rules()
    {
        return [
            [['page', 'id', 'type'], 'integer'],
            [['keyword'], 'string'],
            [['page', 'type'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = CommonGoods::getList($this->keyword, '', $this->type);
        $setting = (new CommonSetting())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list['list'] ?? [],
                'activity' => $list['activity'] ?? (object)[],
                'next_activity' => $list['next_activity'] ?? (object)[],
                'content' => $setting['content'] ?? '',
                'pagination' => $list['pagination'] ?? (object)[],
            ]
        ];
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->mall = Yii::$app->mall;
            $form->user = User::findOne(Yii::$app->user->id);
            $flashGoods = Goods::find()->where(
                [
                    'id' => $this->id,
                    'mall_id' => Yii::$app->mall->id,
                    'is_delete' => 0,
                ]
            )->with(['attr.attr', 'flashSaleGoods'])->one();
            if (!$flashGoods) {
                throw new Exception('商品不存在');
            }
            if ($flashGoods->status != 1) {
                throw new Exception('商品未上架');
            }
            $form->goods = $flashGoods;
            $setting = (new CommonSetting())->search();
            $form->setShare($setting['is_share']);
            $form->setMember($setting['is_member_price']);
            $goods = $form->getAll();
            //当前商品活动状态
            $activity_status = 1;
            /**@var FlashSaleActivity $activity * */
            $activity = FlashSaleActivity::find()->alias('a')
                ->leftJoin(['g' => FlashSaleGoods::tableName()], 'g.activity_id = a.id')
                ->andWhere(['a.is_delete' => 0, 'g.goods_id' => $this->id])
                ->one();
            if (strtotime($activity->start_at) > time()) {
                //未开始
                $activity_status = 0;
            }
            if ($activity->status == 0 || strtotime($activity->end_at) < time()) {
                //下架或已过期
                $activity_status = 2;
            }

            if ($activity_status == 1) {
                $goods['sales'] = $form->goods->sales + $form->goods->virtual_sales;
            } else {
                $goods['sales'] = 0;
            }

            foreach ($flashGoods->attr as $item) {
                foreach ($goods['attr'] as $key => $item1) {
                    if ($item1['id'] == $item['id']) {
                        $goods['attr'][$key]['attr'] = $item->attr;
                        if ($item->attr->type == 1) {
                            $discount = (1 - $item->attr->discount / 10) * $item->price;
                            $price = $item->price;
                            $price -= min($discount, $price);
                            $goods['attr'][$key]['price'] = price_format($price);

                            $discountMember = (1 - $item->attr->discount / 10) * $item1['price_member'];
                            $price1 = $item1['price_member'];
                            $price1 -= min($discountMember, $price1);
                            $goods['attr'][$key]['price_member'] = price_format($price1);
                        } else {
                            $discount = $item->attr->cut;
                            $price = $item->price;
                            $price -= min($discount, $price);
                            $goods['attr'][$key]['price'] = price_format($price);

                            $price1 = $item1['price_member'];
                            $price1 -= min($discount, $price1);
                            $goods['attr'][$key]['price_member'] = price_format($price1);
                        }
                    }
                }
            }

            list($discountType, $minDiscount, $minPrice) = CommonGoods::getMinDiscount($flashGoods);
            $goods['min_discount'] = $minDiscount;
            $goods['discount_type'] = $discountType;
            if ($discountType == 1) {
                $discount = (1 - $minDiscount / 10) * $minPrice;
                $goods['price'] = $minPrice;
                $goods['price'] -= min($discount, $goods['price']);
                $goods['price'] = price_format($goods['price']);

                $discount = (1 - $minDiscount / 10) * $goods['price_min'];
                $goods['price_min'] -= min($discount, $goods['price_min']);
                $goods['price_min'] = price_format($goods['price_min']);

                $discount = (1 - $minDiscount / 10) * $goods['price_max'];
                $goods['price_max'] -= min($discount, $goods['price_max']);
                $goods['price_max'] = price_format($goods['price_max']);

                if (isset($goods['price_member_min']) && isset($goods['price_member_max'])) {
                    $discount = (1 - $minDiscount / 10) * $goods['price_member_min'];
                    $goods['price_member_min'] -= min($discount, $goods['price_member_min']);
                    $goods['price_member_min'] = price_format($goods['price_member_min']);

                    $discount = (1 - $minDiscount / 10) * $goods['price_member_max'];
                    $goods['price_member_max'] -= min($discount, $goods['price_member_max']);
                    $goods['price_member_max'] = price_format($goods['price_member_max']);
                }
            } else {
                $discount = $minDiscount;
                $goods['price'] = $minPrice;
                $goods['price'] -= min($discount, $goods['price']);
                $goods['price_min'] -= min($discount, $goods['price_min']);
                $goods['price_max'] -= min($discount, $goods['price_max']);
                $goods['price'] = price_format($goods['price']);
                $goods['price_min'] = price_format($goods['price_min']);
                $goods['price_max'] = price_format($goods['price_max']);
                if (isset($goods['price_member_min']) && isset($goods['price_member_max'])) {
                    $goods['price_member_min'] -= min($discount, $goods['price_member_min']);
                    $goods['price_member_max'] -= min($discount, $goods['price_member_max']);
                    $goods['price_member_min'] = price_format($goods['price_member_min']);
                    $goods['price_member_max'] = price_format($goods['price_member_max']);

                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods,
                    'activity' => $flashGoods->flashSaleGoods->activity,
                    'activity_status' => $activity_status
                ]
            ];
        } catch (Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
