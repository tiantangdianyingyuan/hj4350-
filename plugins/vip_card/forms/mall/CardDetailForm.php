<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/9
 * Time: 10:49
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsCards;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardCards;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardUser;
use yii\helpers\ArrayHelper;

class CardDetailForm extends Model
{
    public $id;
    public $status;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page', 'status'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['search'], 'safe'],
            [['keyword'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [

        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = VipCardDetail::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = VipCardDetail::find()
            ->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with(['vipCards', 'vipCoupons', 'main'])
            ->asArray()
            ->one();

        $vipCards = array_column($detail['vipCards'], 'name', 'id');
        foreach ($detail['cards'] as $k => &$item) {
            if (isset($vipCards[$item['card_id']])) {
                $item['name'] = $vipCards[$item['card_id']];
            } else {
                unset($detail['cards'][$k]);
            }
        }
        unset($item);

        $vipCoupons = array_column($detail['vipCoupons'], 'name', 'id');
        foreach ($detail['coupons'] as $k => &$item) {
            if (isset($vipCoupons[$item['coupon_id']])) {
                $item['name'] = $vipCoupons[$item['coupon_id']];
            } else {
                unset($detail['coupons'][$k]);
            }
        }
        unset($item);

        $types = json_decode($detail['main']['type_info'], true);
        $detail['type_info'] = $types;
        $detail['type_info_detail']['goods'] = [];
        $goodsList = Goods::find()
            ->where([
                'goods_warehouse_id' => $types['goods'],
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'sign' => '',
            ])
            ->all();
        foreach ($goodsList as $goods) {
            try {
                $temp['name'] = $goods->name;
                $temp['id'] = $goods->id;
                $temp['sales'] = "已售{$goods->getSales()}件";
                $temp['price'] = $goods->price;
                $temp['cover_pic'] = $goods->coverPic;
                $temp['page_url'] = "/pages/goods/goods?id={$goods->id}";
                $detail['type_info_detail']['goods'][] = $temp;
            } catch (\Exception $e) {
                \Yii::error('会员卡指定商品不存在');
                \Yii::error($e);
            }
        }
        unset($temp);

        $detail['type_info_detail']['cats'] = [];
        $catsList = GoodsCats::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $types['cats'],
                'mch_id' => 0,
                'status' => 1
            ])
            ->all();
        foreach ($catsList as $item) {
            $temp['value'] = $item['id'];
            $temp['label'] = $item['name'];
            $detail['type_info_detail']['cats'][] = $temp;
        }
        unset($temp);

        $detail['type_info_detail']['all'] = $types['all'];

        if (!$detail) {
            throw new \Exception('会员卡不存在');
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => $detail
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $arr = [0, 1];
            if (!in_array($this->status, $arr)) {
                throw new \Exception('status 状态参数错误->' . $this->status);
            }
            /** @var VipCardDetail $detail */
            $detail = VipCardDetail::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->id
            ])->one();
            if (!$detail) {
                throw new \Exception('会员卡不存在');
            }

            $detail->status = $this->status;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
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

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var VipCardDetail $detail */
            $detail = VipCardDetail::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->id
            ])->one();
            if (!$detail) {
                throw new \Exception('会员卡不存在');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }

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


    public function getCoupons()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $coupons = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $coupons,
                'pagination' => $pagination
            ]
        ];
    }

    public function getCards()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = GoodsCards::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);


        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $cards = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $cards,
                'pagination' => $pagination
            ]
        ];
    }
}
