<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/8
 * Time: 16:03
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\cart;


use app\models\GoodsAttr;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityCart;
use app\plugins\community\models\CommunitySwitch;
use yii\helpers\Json;

class CartListForm extends Model
{
    public $middleman_id;
    public $activity_id;

    public function rules()
    {
        return [
            [['middleman_id', 'activity_id'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'middleman_id' => '团长',
            'activity_id' => '活动'
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var CommunityActivity $activity */
            $activity = CommunityActivity::find()->with('communityGoods.goods.attr')
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->activity_id])
                ->one();
            if (!$activity) {
                throw new \Exception('所选活动不存在');
            }
            if ($activity->status != 1) {
                throw new \Exception('所选活动已下架');
            }
            $goodsIds = CommunitySwitch::find()
                ->where(['middleman_id' => $this->middleman_id, 'activity_id' => $this->activity_id, 'is_delete' => 0])
                ->select('goods_id')->column();
            /* @var CommunityCart[] $cartList */
            $cartList = CommunityCart::find()->with(['communityGoods.goods.attr', 'communityGoods.goods.goodsWarehouse'])
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id,
                    'activity_id' => $activity->id, 'is_delete' => 0
                ])->all();
            $newList = [];
            foreach ($cartList as $cart) {
                $attrInfo = Json::decode($cart->attr_info, true);
                $newItem = [
                    'activity_id' => $cart->activity_id,
                    'community_goods_id' => $cart->community_goods_id,
                    'goods_id' => $cart->goods_id,
                    'goods_attr_id' => $cart->goods_attr_id,
                    'num' => $cart->num,
                    'name' => $cart->communityGoods->goods->name,
                    'attr_list' => $attrInfo['attr_list'],
                    'pic_url' => $attrInfo['pic_url'],
                    'id' => $cart->id
                ];
                /* @var GoodsAttr[] $attrList */
                $attrList = [];
                foreach ($cart->communityGoods->goods->attr as $attr) {
                    $attrList[$attr->id] = $attr;
                }
                if (!array_key_exists($cart->goods_attr_id, $attrList)
                    || in_array($cart->goods_id, $goodsIds)
                    || $cart->communityGoods->goods->status == 0 || $cart->communityGoods->goods->is_delete == 1) {
                    $newItem['price'] = $attrInfo['price'];
                    $newItem['is_exist'] = 0;
                } else {
                    $attr = $attrList[$cart->goods_attr_id];
                    $newItem['price'] = $attr->price;
                    $newItem['is_exist'] = 1;
                }
                $newList[] = $newItem;
            }
            return $this->success(['list' => $newList, 'activity_name' => $activity->title]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
