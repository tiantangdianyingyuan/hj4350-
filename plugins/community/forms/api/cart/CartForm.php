<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/8
 * Time: 16:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\cart;


use app\models\GoodsAttr;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\Model;
use app\plugins\community\jobs\CartJob;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityCart;
use app\plugins\community\models\CommunityGoods;
use yii\helpers\Json;

class CartForm extends Model
{
    public $goods_id;
    public $goods_attr_id;
    public $num;
    public $activity_id;

    public function rules()
    {
        return [
            [['activity_id', 'goods_id', 'goods_attr_id', 'num'], 'required'],
            [['activity_id', 'goods_id', 'goods_attr_id', 'num'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'activity_id' => '活动id',
            'goods_id' => '商品',
            'attr_id' => '商品规格',
            'num' => '数量',
        ];
    }

    public function job()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $token = \Yii::$app->security->generateRandomString();
        $queueId = \Yii::$app->queue->delay(0)->push(new CartJob([
            'form' => $this,
            'token' => $token,
            'mall' => \Yii::$app->mall,
            'user' => \Yii::$app->user->identity,
            'appVersion' => \Yii::$app->appVersion,
        ]));
        return $this->success(['queue_id' => $queueId, 'token' => $token]);
    }

    /**
     * @return CommunityCart|null
     */
    public function getCart()
    {
        $cart = CommunityCart::findOne([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id,
            'mch_id' => 0, 'activity_id' => $this->activity_id, 'goods_id' => $this->goods_id,
            'goods_attr_id' => $this->goods_attr_id
        ]);
        return $cart;
    }

    public function save()
    {
        $cart = $this->getCart();
        return $this->add($cart);
    }

    /**
     * @param CommunityCart $cart
     * @return bool
     * @throws \Exception
     */
    public function add($cart)
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        /* @var CommunityActivity $activity */
        $activity = CommonActivity::getActivity($this->activity_id);
        if (!$activity) {
            throw new \Exception('所选活动不存在');
        }
        if ($activity->status != 1) {
            throw new \Exception('所选活动已下架');
        }
        /* @var CommunityGoods[] $newGoodsList */
        $newGoodsList = [];
        $communityGoodsList = $activity->communityGoods;
        foreach ($communityGoodsList as $communityGoods) {
            $newGoodsList[$communityGoods->goods_id] = $communityGoods;
        }
        if (!array_key_exists($this->goods_id, $newGoodsList)) {
            throw new \Exception('所选商品已被活动移除，请重新选择');
        }
        $communityGoods = $newGoodsList[$this->goods_id];
        /* @var GoodsAttr[] $attrList */
        $attrList = [];
        foreach ($communityGoods->goods->attr as $attr) {
            $attrList[$attr->id] = $attr;
        }
        if (!array_key_exists($this->goods_attr_id, $attrList)) {
            throw new \Exception('所选商品规格不存在，请重新选择');
        }
        $attr = $attrList[$this->goods_attr_id];
        $num = 0;
        if ($cart) {
            $num = $cart->num;
        }
        if ($attr->stock < $this->num + $num) {
            throw new \Exception('所选商品规格的库存小于选择的数量（含购物车中的数量）');
        }
        $attrGroup = $communityGoods->goods->resetAttr();
        $attrInfo = [
            'attr_list' => $attrGroup[$attr->sign_id],
            'price' => $attr->price,
            'weight' => $attr->weight,
            'no' => $attr->no,
            'pic_url' => $attr->pic_url ?: $communityGoods->goods->coverPic,
        ];
        if (!$cart) {
            $cart = new CommunityCart();
            $cart->mall_id = \Yii::$app->mall->id;
            $cart->user_id = \Yii::$app->user->id;
            $cart->mch_id = 0;
            $cart->activity_id = $this->activity_id;
            $cart->community_goods_id = $communityGoods->id;
            $cart->goods_id = $this->goods_id;
            $cart->goods_attr_id = $this->goods_attr_id;
            $cart->attr_info = Json::encode($attrInfo, JSON_UNESCAPED_UNICODE);
            $cart->num = 0;
        }
        $cart->num += $this->num;
        if ($attr->stock < $cart->num) {
            throw new \Exception('所选商品规格的库存小于选择的数量');
        }
        if (!$cart->save()) {
            throw new \Exception($this->getErrorMsg($cart));
        }
        return true;
    }
}
