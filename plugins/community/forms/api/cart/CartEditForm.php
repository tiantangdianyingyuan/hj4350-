<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/11
 * Time: 9:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\cart;


use app\plugins\community\forms\Model;
use app\plugins\community\jobs\CartJob;
use app\plugins\community\models\CommunityCart;
use yii\helpers\Json;

class CartEditForm extends Model
{
    public $list;
    public $middleman_id;

    public function rules()
    {
        return [
            ['list', 'safe'],
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

    public function save()
    {
        $list = Json::decode($this->list, true);
        $newList = [];
        $idList = [];
        foreach ($list as $item) {
            if (!isset($item['id'])) {
                throw new \Exception('参数的数据结构不正确');
            }
            if (!isset($item['num'])) {
                throw new \Exception('参数的数据结构不正确，缺少商品数量');
            }
            $newList[$item['id']] = $item['num'];
            $idList[] = $item['id'];
        }
        if (empty($idList)) {
            throw new \Exception('购物车无数据提交');
        }
        /* @var CommunityCart[] $cartList */
        $cartList = CommunityCart::find()
            ->where(['id' => $idList, 'is_delete' => 0])
            ->all();
        foreach ($cartList as $cart) {
            if (!isset($newList[$cart->id])) {
                continue;
            }
            $cart->num = $newList[$cart->id];
            if (!$cart->save()) {
                \Yii::warning('购物车数据添加失败' . $this->getErrorMsg($cart));
                continue;
            }
        }
        return true;
    }
}
