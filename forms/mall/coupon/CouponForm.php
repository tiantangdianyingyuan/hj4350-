<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\coupon;

use app\core\response\ApiCode;
use app\forms\common\CommonCats;
use app\forms\common\CommonUser;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\coupon\CouponMallRelation;
use app\forms\common\goods\CommonGoodsCats;
use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponCenter;
use app\models\CouponGoodsRelation;
use app\models\CouponMemberRelation;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;

class CouponForm extends Model
{
    public $keyword;
    public $page;
    public $page_size;
    public $user_id_list;
    public $cat_id_list;
    public $goods_id_list;
    public $couponMember;

    public $id;
    public $mall_id;
    public $name;
    public $type;
    public $discount;
    public $discount_limit;
    public $min_price;
    public $sub_price;
    public $expire_type;
    public $expire_day;
    public $begin_time;
    public $end_time;
    public $total_count;
    public $is_join;
    public $sort;
    public $rule;
    public $pic_url;
    public $desc;
    public $is_member;
    public $appoint_type;
    public $coupon_num;
    public $is_send;
    public $can_receive_count;
    public $app_share_title;
    public $app_share_pic;

    public $is_expired;

    public function rules()
    {
        return [
            [['couponMember'], 'trim'],
            [['id', 'mall_id', 'type', 'pic_url', 'total_count', 'is_join', 'sort', 'expire_type', 'expire_day',
                'appoint_type', 'is_member', 'coupon_num', 'can_receive_count', 'is_expired'], 'integer'],
            [['min_price', 'sub_price', 'discount_limit'], 'number', 'min' => 0, 'max' => 99999999],
            [['begin_time', 'end_time', 'user_id_list', 'cat_id_list', 'goods_id_list'], 'safe'],
            [['name', 'keyword', 'app_share_title', 'app_share_pic'], 'string', 'max' => 255],
            [['desc', 'rule'], 'string', 'max' => 2000],
            [['expire_day'], 'integer', 'min' => 0, 'max' => 999],
            [['discount',], 'number', 'min' => 0.1],
            [['sort'], 'integer', 'min' => 0, 'max' => 99999999],
            [['total_count', 'can_receive_count'], 'integer', 'min' => -1, 'max' => 99999999],
            [['page'], 'default', 'value' => 1],
            [['coupon_num', 'is_send'], 'default', 'value' => 0],
            [['page_size', 'pic_url'], 'default', 'value' => 10],
            [['begin_time', 'end_time'], 'default', 'value' => '0000-00-00 00:00:00'],
            [['rule', 'desc', 'keyword'], 'default', 'value' => ''],
            ['can_receive_count', 'default', 'value' => -1]
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id_list' => '发放对象',
            'mall_id' => 'mall ID',
            'name' => '优惠券名称',
            'type' => '优惠券类型：1=折扣，2=满减',
            'discount' => '折扣率',
            'discount_limit' => '优惠上限',
            'pic_url' => '未用',
            'desc' => '未用',
            'min_price' => '最低消费金额',
            'sub_price' => '优惠金额',
            'total_count' => '优惠券库存',
            'is_join' => '是否加入领券中心',
            'sort' => '排序按升序排列',
            'expire_type' => '到期类型',
            'expire_day' => '有效天数',
            'begin_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'rule' => '使用说明',
            'is_member' => '是否指定会员等级领取',
            'is_delete' => '删除',
            'appoint_type' => '指定类型',
            'can_receive_count' => '每人限领次数',
            'coupon_num' => '发放数量',
            'is_expired' => '是否过期' //搜索用
        ];
    }

    //GET
    public function getList()
    {

        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = Coupon::find()->alias('c')->where([
            'c.mall_id' => \Yii::$app->mall->id,
            'c.is_delete' => 0,
        ]);
        if ($this->is_expired) {
            $query->andWhere([
                'or',
                [
                    'and',
                    ['c.expire_type' => 2],
                    ['>', 'c.end_time', date('Y-m-d H:i:s')]
                ],
                ['c.expire_type' => 1]
            ]);
        }
        if ($this->is_join == 1) {
            $query->innerJoinWith(['couponCenter cc' => function ($query) {
                $query->where(['cc.is_delete' => 0]);
            }]);
        } else {
            $query->with(['couponCenter' => function ($query) {
                $query->where(['is_delete' => 0]);
            }]);
        }
        $data = $query->keyword($this->keyword, ['like', 'c.name', $this->keyword])
            ->page($pagination)
            ->orderBy('c.id DESC')
            ->asArray()
            ->all();
        foreach ($data as $k => $v) {
            $form = new CommonCoupon();
            $form->user = false;
            $data[$k]['count'] = $v['total_count'] + $form->getCount($v['id']);
            $data[$k]['is_join'] = $v['couponCenter'] ? '1' : '0';
            $data[$k]['begin_time'] = new_date($v['begin_time']);
            $data[$k]['end_time'] = new_date($v['end_time']);
            $data[$k]['has_expire'] = $v['expire_type'] == 2 && $v['end_time'] < date('Y-m-d H:i:s');
        };

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $data,
                'pagination' => $pagination
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Coupon::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success'
        ];
    }

    //DETAIL
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $coupons = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ])
            ->with(['cat', 'goodsWarehouse'])
            ->with(['couponMember' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->with(['couponCenter' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->asArray()
            ->one();
        if ($coupons) {
            $coupons['is_member'] = $coupons ? (int)$coupons['is_member'] : 0;
            $coupons['goods_id_list'] = $coupons['cat_id_list'] = [];
            $coupons['is_join'] = isset($coupons['couponCenter']) ? 1 : 0;
            $coupons['total_count'] = isset($coupons['total_count']) ? intval($coupons['total_count']) : 0;

            if (array_key_exists('cat', $coupons) && $coupons['cat']) {
                $array = [];
                foreach ($coupons['cat'] as $v) {
                    $array[] = (int)$v['id'];
                }
                $coupons['cat_id_list'] = $array;
            }

            if (array_key_exists('goodsWarehouse', $coupons) && $coupons['goodsWarehouse']) {
                $array = [];
                foreach ($coupons['goodsWarehouse'] as $v) {
                    $array[] = (int)$v['id'];
                }
                $coupons['goods_id_list'] = $array;
            }
            if (array_key_exists('couponMember', $coupons) && $coupons['couponMember']) {
                $coupons['couponMember'] = array_column($coupons['couponMember'], 'member_level');
            }
        }

        $members = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $coupons,
                'members' => $members,
                'cats' => CommonCats::getAllCats(),
            ]
        ];
    }

    //切换领劵中心
    public function editCenter()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        //领劵中心
        $couponCenter = CouponCenter::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'coupon_id' => $this->id,
            'is_delete' => 0,
        ]);

        if ($this->is_join) {
            if (!$couponCenter) {
                $form = new CouponCenter();
                $form->mall_id = \Yii::$app->mall->id;
                $form->coupon_id = $this->id;
                $form->save();
            }
        } else {
            if ($couponCenter) {
                $couponCenter->is_delete = 1;
                $couponCenter->save();
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '切换成功'
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Coupon::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Coupon();
        }

        $t = \Yii::$app->db->beginTransaction();
        if ($this->appoint_type == 1 && !$this->cat_id_list) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '类别不能为空'
            ];
        }

        if ($this->expire_type == 2 && ($this->begin_time == '0000-00-00 00:00:00' || $this->end_time == '0000-00-00 00:00:00')) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '时间不能为空'
            ];
        }
        if ($this->appoint_type == 2 && !$this->goods_id_list) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '商品不能为空'
            ];
        }

        if ($this->type == 1 && !empty($this->discount_limit) && $this->discount_limit <= 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '优惠上限必须大于0'
            ];
        }

        if ($this->is_member && empty($this->couponMember)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '会员专享优惠券，需要选择指定会员'
            ];
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;

        if ($this->expire_type == 2) {
            $model->expire_day = !empty($model->expire_day) ? $model->expire_day : 1;
        }
        if ($this->type == 2) {
            $model->discount = 10;
            $model->discount_limit = null;
        }
        if ($this->type == 1) {
            if ($this->discount >= 10) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '折扣率的值必须不大于10。'
                ];
            }
            if ($this->discount > 9.9) {
                $model->discount = 9.9;
            }
        }

        if ($model->save()) {
            //领劵中心
            $couponCenter = CouponCenter::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'coupon_id' => $model->id,
                'is_delete' => 0,
            ]);

            if ($this->is_join) {
                if (!$couponCenter) {
                    $form = new CouponCenter();
                    $form->mall_id = \Yii::$app->mall->id;
                    $form->coupon_id = $model->id;
                    $form->save();
                }
            } else {
                if ($couponCenter) {
                    $couponCenter->is_delete = 1;
                    $couponCenter->save();
                }
            }

            //会员优惠券
            $array = [];
            CouponMemberRelation::updateAll(['is_delete' => 1, 'deleted_at' => date("Y-m-d H:i:s")], [
                'mall_id' => \Yii::$app->mall->id,
                'coupon_id' => $model->id,
                'is_delete' => 0,
            ]);
            $this->couponMember = $this->couponMember && $this->is_member ? $this->couponMember : [];
            foreach ($this->couponMember as $v) {
                $array[] = [
                    \Yii::$app->mall->id, $model->id, $v, 0, date("Y-m-d H:i:s"), '0000-00-00 00:00:00',
                ];
            }
            if (isset($array)) {
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        CouponMemberRelation::tableName(),
                        ['mall_id', 'coupon_id', 'member_level', 'is_delete', 'created_at', 'deleted_at'],
                        $array
                    )
                    ->execute();
            }
            //指定分类或商品
            if ($this->appoint_type == 1) {
                CouponCatRelation::updateAll(['is_delete' => 1], ['coupon_id' => $this->id]);
                foreach ($this->cat_id_list as $id) {
                    $form = new CouponCatRelation();
                    $form->coupon_id = $model->id;
                    $form->cat_id = $id;
                    $form->is_delete = 0;
                    $form->save();
                };
            } elseif ($this->appoint_type == 2) {
                CouponGoodsRelation::updateAll(['is_delete' => 1], ['coupon_id' => $this->id]);
                foreach ($this->goods_id_list as $id) {
                    $form = new CouponGoodsRelation();
                    $form->coupon_id = $model->id;
                    $form->goods_warehouse_id = $id;
                    $form->is_delete = 0;
                    $form->save();
                };
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            $t->rollBack();
            return $this->getErrorResponse($model);
        }
    }

    //SEND
    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $model = Coupon::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
            ]);

            if (!$model) {
                throw new \Exception('优惠券不存在');
            }

            if ($this->coupon_num == 0) {
                throw new \Exception('发送数量不能为空');
            }

            if ($model->total_count < $this->coupon_num && $model->total_count != -1) {
                throw new \Exception('优惠券数量不足');
            }

            $userList = User::find()->where([
                'id' => $this->user_id_list,
                'mall_id' => \Yii::$app->mall->id
            ])->all();

            $count = 0;
            $num = 0;
            $msg = null;
            foreach ($userList as $u) {
                try {
                    $common = new CommonCoupon(['coupon_id' => $this->id], false);
                    $coupon = $common->getAutoDetail();
                    $common->user = $u;
                    $class = new CouponMallRelation($coupon);

                    for ($i = 0; $i < $this->coupon_num; $i++) {
                        $msg = "操作完成，";
                        if (!$common->receive($coupon, $class, '后台发放')) {
                            $msg = "优惠券数量不够，";
                            break;
                        }
                        $num++;
                    }
                    $count++;
                } catch (\Exception $e) {
                    dd($e);
                }

                //删除发送小程序模板消息
                $this->is_send = 0;
                //是否发送模版消息，——发送开关
                if ($this->is_send) {
                    $tplMsg = new ActivitySuccessTemplate([
                        'page' => 'pages/coupon/index/index',
                        'user' => $u,
                        'activityName' => '优惠券发放',
                        'name' => $coupon->name,
                        'remark' => '您有新的优惠券待查收'
                    ]);
                    $tplMsg->send();
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg . "共发放{$count}人次，{$num}张。",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function searchUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => CommonUser::searchUser($this->keyword)
        ];
    }

    public function searchCat()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => CommonGoodsCats::searchCat($this->keyword)
        ];
    }

    public function searchGoods()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $goodsId = Goods::find()->select('goods_warehouse_id')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => '', 'mch_id' => 0, 'status' => 1]);

        $list = GoodsWarehouse::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsId])
            ->keyword($this->keyword !== '', ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getOptions()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->andWhere([
            'or',
            [
                'and',
                ['expire_type' => 2],
                ['>', 'end_time', date('Y-m-d H:i:s')]
            ],
            ['expire_type' => 1]
        ])
            ->page($pagination)
            ->orderBy('id DESC')
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
