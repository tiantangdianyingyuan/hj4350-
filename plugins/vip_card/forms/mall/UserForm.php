<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/23
 * Time: 17:00
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsCards;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use app\plugins\vip_card\models\VipCardUser;
use yii\helpers\ArrayHelper;

class UserForm extends Model
{
    public $id;
    public $keyword;
    public $type;
    public $search_type;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $date_start;
    public $date_end;

    public $flag;
    public $fields;

    public $user_id;
    public $expire_type;

    public function rules()
    {
        return [
            [['id', 'type', 'limit', 'page', 'search_type', 'user_id', 'expire_type'], 'integer'],
            [['date_start', 'date_end', 'keyword', 'flag'], 'string'],
            [['keyword'], 'default', 'value' => ''],
            [['sort'], 'default', 'value' => ['v.created_at' => SORT_DESC]],
            [['fields'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $pagination = null;
        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new UserExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->select(['v.*', 'i.*'])
            ->asArray()->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->all();

        foreach ($list as &$value) {
            $value['send_integral_num'] = 0;
            $value['send_balance'] = 0;
            $value['send_cards'] = [];
            $value['send_coupons'] = [];
            foreach ($value['order'] as $k => $item) {
                //todo 应该写在各自插件内
                if ($item['sign'] == '' && $item['order_id'] == 0) {
                    $value['order'][$k]['price'] = $item['price'] . "(后台添加)";
                } elseif ($item['sign'] == 'exchange') {
                    $value['order'][$k]['price'] = $item['price'] . "(兑换中心兑换)";
                }
                $detail = json_decode($item['all_send'], true);
                $detail = (empty($detail) || !is_array($detail)) ? [] : $detail;
                foreach ($detail as $k => $v) {
                    if ($k == 'send_integral_num') {
                        $value['send_integral_num'] += $v;
                    }
                    if ($k == 'send_balance') {
                        $value['send_balance'] += $v;
                    }
                    if ($k == 'cards' && is_array($v)) {
                        foreach ($v as $values) {
                            $card = GoodsCards::findOne(['id' => $values['card_id'], 'is_delete' => 0]);
                            if ($card) {
                                $temp['card_id'] = $values['card_id'];
                                $temp['name'] = $card->name;
                                $temp['num'] = $values['send_num'];
                                if (!in_array($values['card_id'],array_column($value['send_cards'],'card_id'))) {
                                    $value['send_cards'][] = $temp;
                                } else {
                                    foreach ($value['send_cards'] as &$card) {
                                        if ($card['card_id'] == $values['card_id']) {
                                            $card['num'] += $values['send_num'];
                                        }
                                    }
                                    unset($card);
                                }
                            }
                        }
                    }
                    if ($k == 'coupons' && is_array($v)) {
                        foreach ($v as $values) {
                            $coupon = Coupon::findOne(['id' => $values['coupon_id'], 'is_delete' => 0]);
                            if ($coupon) {
                                $temp['coupon_id'] = $values['coupon_id'];
                                $temp['name'] = $coupon->name;
                                $temp['num'] = $values['send_num'];
                                if (!in_array($values['coupon_id'],array_column($value['send_coupons'],'coupon_id'))) {
                                    $value['send_coupons'][] = $temp;
                                } else {
                                    foreach ($value['send_coupons'] as &$card) {
                                        if ($card['coupon_id'] == $values['coupon_id']) {
                                            $card['num'] += $values['send_num'];
                                        }
                                    }
                                    unset($card);
                                }
                            }
                        }
                    }
                }
            }
            unset($value['all_send']);
            $value['start_time'] = date('Y-m-d',strtotime($value['start_time']));
            $value['end_time'] = date('Y-m-d',strtotime($value['end_time']));
            $value['intro'] = $this->parseRights($value['image_discount'],$value['image_is_free_delivery']);
            $value['rights'] = $this->parseRightsDetail($value);
        }
        unset($value);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'export_list' => (new UserExport())->fieldsList()
            ]
        ];
    }

    private function where()
    {
        $query = VipCardUser::find()->alias('v')
            ->where([
                'v.is_delete' => 0,
                'v.mall_id' => \Yii::$app->mall->id
            ])
            ->joinWith(['user u' => function ($query) {
                $query->select(['nickname', 'id', 'username', 'mobile', 'mall_id']);
                if ($this->keyword && $this->search_type == 2) {
                    $query->andWhere(['like', 'nickname', $this->keyword]);
                } elseif ($this->keyword && $this->search_type == 1) {
                    $query->andWhere(['u.id' => $this->keyword]);
                }
            }])->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
            ->with(['order' => function ($query) {
                $query->where(['status' => 1])->orderBy('created_at DESC');
            }]);

        if ($this->date_start) {
            $query->andWhere(['>=', 'v.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'v.created_at', $this->date_end]);
        }

        if (isset($this->expire_type)) {
            if ($this->expire_type == 0) {
                $query->andWhere(['>', 'v.end_time', date('Y-m-d H:i:s')]);
            } elseif ($this->expire_type == 1) {
                $query->andWhere(['<=', 'v.end_time', date('Y-m-d H:i:s')]);
            }
        }

        return $query;
    }

    private function parseRights($discount,$type)
    {
        $discount = "会员折扣{$discount}折";
        $text = $type == 1 ? '自营商品包邮,' : '';
        return $text.$discount;
    }

    private function parseRightsDetail($value)
    {
        $type = json_decode($value['image_type_info'],true);

        $goods = GoodsWarehouse::find()
            ->select('id,cost_price,cover_pic,created_at,name,original_price,pic_url,unit')
            ->where(['id' => $type['goods'], 'is_delete' => 0])->all();
        $cats = GoodsCats::find()->where(['id' => $type['cats'], 'is_delete' => 0])->all();

        $right = [
            'discount' => $value['image_discount'],
            'is_delivery' => $value['image_is_free_delivery'],
            'all' => $type['all'] ? 1 : 0 ,
        ];

        if ($right['all'] == 0) {
            $right['goods'] = $goods;
            $right['cats'] = $cats;
        }

        return $right;
    }

    public function delete()
    {
        try {
            $user = VipCardUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $this->user_id,
                'is_delete' => 0
            ])->one();

            if (!$user) {
                throw new \Exception('该用户不存在');
            }
            $user->is_delete = 1;
            if (!$user->save()) {
                throw new \Exception($this->getErrorMsg($user));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
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

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $user = VipCardUser::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $this->user_id,
                'is_delete' => 0
            ])->one();

            if (!$user) {
                throw new \Exception('该用户不存在');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'user' => $user,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'errors' => $e->getLine()
            ];
        }
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $checkInUserId = VipCardUser::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->select('user_id');
        $query = User::find()->alias('u')->select('u.id,u.nickname')->where([
            'AND',
            ['or', ['LIKE', 'u.nickname', $this->keyword], ['u.id' => $this->keyword], ['u.mobile' => $this->keyword]],
            ['u.mall_id' => \Yii::$app->mall->id],
            ['not in', 'user_id', $checkInUserId]
        ]);
        $list = $query->InnerJoinwith('userInfo')->orderBy('nickname')->limit(30)->all();

        $newList = [];
        /** @var User $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['avatar'] = $item->userInfo ? $item->userInfo->avatar : '';
            $platform = $item->userInfo ? $item->userInfo->platform : '';
            $newItem['nickname'] = UserInfo::getPlatformText($platform) . '（' . $item->nickname . '）';
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList
            ]
        ];
    }

    public function right()
    {
        $userCard = VipCardUser::find()->where(['user_id' => $this->user_id, 'is_delete' => 0])->one();
        $type = json_decode($userCard->image_type_info,true);
        if ($this->type == 1) {
            $count = count($type['goods']);
            $page = \Yii::$app->request->get('page', 1);
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            if ($page) {
                $pagination->page = $page - 1;
            } else {
                $pagination->page = \Yii::$app->request->get('page', 1) - 1;
            }
            $query = GoodsWarehouse::find()
                ->select('id,cost_price,cover_pic,created_at,name,original_price,pic_url,unit')
                ->where(['id' => $type['goods'],'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
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
            $query = GoodsCats::find()->where(['id' => $type['cats'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,]);
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
