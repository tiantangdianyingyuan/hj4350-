<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\models\Order;
use app\models\UserCard;
use app\models\UserCoupon;

class UserExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'id',
                'value' => '用户ID',
            ],
            [
                'key' => 'platform_user_id',
                'value' => '平台标识ID',
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '绑定手机号',
            ],
            [
                'key' => 'contact_way',
                'value' => '联系方式',
            ],
            [
                'key' => 'remark',
                'value' => '备注',
            ],
            [
                'key' => 'created_at',
                'value' => '加入时间',
            ],
            [
                'key' => 'member_level',
                'value' => '会员身份',
            ],
            [
                'key' => 'order_count',
                'value' => '订单数',
            ],
            [
                'key' => 'coupon_count',
                'value' => '优惠券总数',
            ],
            [
                'key' => 'card_count',
                'value' => '卡券总数',
            ],
            [
                'key' => 'integral',
                'value' => '积分',
            ],
            [
                'key' => 'balance',
                'value' => '余额',
            ],
            [
                'key' => 'consume_count',
                'value' => '总消费',
            ],
            [
                'key' => 'parent_id_user',
                'value' => '用户推荐人',
            ],
        ];
    }

    public function export($query)
    {
        // $cardQuery = UserCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');
        // $couponQuery = UserCoupon::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');
        // $orderQuery = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_pay' => 1])->andWhere('user_id = u.id')->select('count(1)');
        // // TODO 消费总额要加上其它消费、如充值等
        $consumeCount = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_confirm' => 1])->andWhere('user_id = u.id')->select('sum(total_pay_price)');

        $list = $query->with(['userInfo', 'identity', 'parent'])
            ->addSelect([
                'consume_count' => $consumeCount
            ])
            ->orderBy(['created_at'=> SORT_DESC])
            ->asArray()
            ->all();


        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '用户列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        $members = CommonMallMember::getAllMember();
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['platform']);
            $arr['id'] = $item['user_id'];
            $arr['platform_user_id'] = $item['platform_user_id'];
            $arr['nickname'] = $item['nickname'];
            $arr['mobile'] = $item['mobile'];
            $arr['contact_way'] = $item['contact_way'];
            $arr['remark'] = $item['remark'];
            $arr['created_at'] = $item['created_at'];

            $memberLevel = $item['member_level'];
            if ($memberLevel > 0) {
                foreach ($members as $member) {
                    if ($member['level'] == $memberLevel) {
                        $arr['member_level'] = $member['name'];
                        break;
                    }
                }
            } elseif ($memberLevel == 0) {
                $arr['member_level'] = '普通用户';
            } else {
                $arr['member_level'] = '未知';
            }
            $arr['order_count'] = (int)$item['order_count'];
            $arr['card_count'] = (int)$item['card_count'];
            $arr['coupon_count'] = (int)$item['coupon_count'];
            $arr['integral'] = (int)$item['integral'];
            $arr['balance'] = (float)$item['balance'];
            $arr['consume_count'] = (int)$item['consume_count'];
            $arr['parent_id_user'] = empty($item['parent']) ? '总店' : $item['parent']['nickname'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
