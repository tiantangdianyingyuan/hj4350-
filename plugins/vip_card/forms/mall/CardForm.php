<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 11:21
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardOrder;

class CardForm extends Model
{
    public $id;
    public $status;
    public $page;
    public $keyword;
    public $sort;
    public $type;

    public function rules()
    {
        return [
            [['id', 'page', 'status', 'sort', 'type'], 'integer'],
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

        $query = VipCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->one();

        if ($list) {
            $list['detail'] = VipCardDetail::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->orderBy(['sort' => SORT_ASC, 'price' => SORT_ASC, 'created_at' => SORT_DESC])
                ->with('vipCards')
                ->with('vipCoupons')
                ->page($pagination)
                ->asArray()
                ->all();

            foreach ($list['detail'] as &$v) {
                $query = VipCardOrder::find()->where(['mall_id' => \Yii::$app->mall->id, 'detail_id' => $v['id'] ,'status' => 1])
                    ->count();
                $v['sales'] = $query;

                $vipCards = array_column($v['vipCards'], 'name', 'id');
                foreach ($v['cards'] as $k => &$item) {
                    if (isset($vipCards[$item['card_id']])) {
                        $item['name'] = $vipCards[$item['card_id']];
                    } else {
                        unset($v['cards'][$k]);
                    }
                }
                unset($item);

                $vipCoupons = array_column($v['vipCoupons'], 'name', 'id');
                foreach ($v['coupons'] as $k => &$item) {
                    if (isset($vipCoupons[$item['coupon_id']])) {
                        $item['name'] = $vipCoupons[$item['coupon_id']];
                    } else {
                        unset($v['coupons'][$k]);
                    }
                }
                unset($item);
            }
            unset($v);

            $types = json_decode($list['type_info'],true);
            $list['type_info'] = $types;
            $list['type_info_detail']['goods'] = GoodsWarehouse::find()->where(['id' => $types['goods'],'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->all();
            $list['type_info_detail']['cats'] = GoodsCats::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $types['cats']])->asArray()->all();
            foreach ($list['type_info_detail']['cats'] as &$item) {
                $item['value'] = $item['id'];
                $item['label'] = $item['name'];
            }
            unset($item);

            $list['type_info_detail']['all'] = $types['all'];
        } else {
            $pagination = new Pagination();
        }

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
        $detail = CommonVip::getCommon()->getMainCard();
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
            /** @var VipCard $card */
            $card = CommonVip::getCommon()->getMainCard($this->id);
            if (!$card) {
                throw new \Exception('会员卡不存在');
            }

            $card->status = $this->status;
            $res = $card->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($card));
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
            /** @var VipCard $card */
            $card = CommonVip::getCommon()->getMainCard();
            if (!$card) {
                throw new \Exception('会员卡不存在');
            }

            $card->is_delete = 1;
            $res = $card->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($card));
            }

            VipCardDetail::updateAll(['is_delete' => 1], ['vip_id' => $card->id, 'mall_id' => \Yii::$app->mall->id]);
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

    public function editSort()
    {
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

            $detail->sort = $this->sort;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
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

    public function right()
    {
        $card = CommonVip::getCommon()->getMainCard();
        $type = json_decode($card->type_info,true);
        if ($this->type == 1) {
            $count = count($type['goods']);
            $page = \Yii::$app->request->get('page', 1);
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            if ($page) {
                $pagination->page = $page - 1;
            } else {
                $pagination->page = \Yii::$app->request->get('page', 1) - 1;
            }
            $query = GoodsWarehouse::find()->where(['id' => $type['goods']]);
            if ($this->keyword) {
                $query->andWhere(['like', 'name', $this->keyword]);
            }
            $list = $query->limit($pagination->limit)->offset($pagination->offset)->all();
        } else {
            $count = count($type['cats']);
            $page = \Yii::$app->request->get('page', 1);
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            if ($page) {
                $pagination->page = $page - 1;
            } else {
                $pagination->page = \Yii::$app->request->get('page', 1) - 1;
            }

            $query = GoodsCats::find()->where(['id' => $type['cats']]);
            if ($this->keyword) {
                $query->andWhere(['like', 'name', $this->keyword]);
            }
            $list = $query->limit($pagination->limit)->offset($pagination->offset)->all();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
