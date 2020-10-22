<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/23
 * Time: 17:00
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\User;
use app\models\UserInfo;
use app\plugins\bonus\models\BonusCaptain;
use yii\helpers\ArrayHelper;

class UserExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'user_id',
                'value' => '用户id',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'expire',
                'value' => '有效期',
            ],
            [
                'key' => 'intro',
                'value' => '会员权益',
            ],
            [
                'key' => 'all_send',
                'value' => '所有赠送',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->select(['v.*', 'i.*'])
            ->asArray()->orderBy("v.created_at DESC")->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '超级会员卡会员列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $name = '超级会员卡会员列表';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as &$item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['user_id'] = $item['user_id'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['expire'] = $item['start_time'] . '~' . $item['end_time'];
            $arr['intro'] = $this->parseRights($item['image_discount'],$item['image_is_free_delivery']);
            $item['send_integral_num'] = 0;
            $item['send_balance'] = 0;
            $item['send_cards'] = [];
            $item['send_coupons'] = [];
            foreach ($item['order'] as $k =>$items) {
                if ($items['order_id'] == 0) {
                    $item['order'][$k]['price'] = $items['price']."(后台添加)";
                }
                $detail = json_decode($items['all_send'],true);
                $detail = (empty($detail) || !is_array($detail)) ? [] : $detail;
                foreach ($detail as $k => $v) {
                    if ($k == 'send_integral_num') {
                        $item['send_integral_num'] += $v;
                    }
                    if ($k == 'send_balance') {
                        $item['send_balance'] += $v;
                    }
                    if ($k == 'cards' && is_array($v)) {
                        foreach ($v as $values) {
                            $card = GoodsCards::findOne(['id' => $values['card_id']]);
                            $temp['card_id'] = $values['card_id'];
                            $temp['name'] = $card->name;
                            $temp['num'] = $values['send_num'];
                            if (!in_array($values['card_id'],array_column($item['send_cards'],'card_id'))) {
                                $item['send_cards'][] = $temp;
                            } else {
                                foreach ($item['send_cards'] as &$card) {
                                    if ($card['card_id'] == $values['card_id']) {
                                        $card['num'] += $values['send_num'];
                                    }
                                }
                                unset($card);
                            }
                        }
                    }
                    if ($k == 'coupons' && is_array($v)) {
                        foreach ($v as $values) {
                            $coupon = Coupon::findOne(['id' => $values['coupon_id']]);
                            $temp['coupon_id'] = $values['coupon_id'];
                            $temp['name'] = $coupon->name;
                            $temp['num'] = $values['send_num'];
                            if (!in_array($values['coupon_id'],array_column($item['send_coupons'],'coupon_id'))) {
                                $item['send_coupons'][] = $temp;
                            } else {
                                foreach ($item['send_coupons'] as &$card) {
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
            $sendCards = '卡券:';
            $sendCoupons = '优惠券:';
            if (!empty($item['send_cards'])) {
                foreach ($item['send_cards'] as $card) {
                    if (!empty($card)) {
                        $sendCards .= $card['num'] . "|" .$card['name'].",---";
                    }
                }
            }
            if (!empty($item['send_coupons'])) {
                foreach ($item['send_coupons'] as $coupon) {
                    if (!empty($coupon)) {
                        $sendCoupons .= $coupon['num'] . "|" . $coupon['name'].",---";
                    }
                }
            }
            $arr['all_send'] = "积分:{$item['send_integral_num']}积分,余额:{$item['send_balance']}元,{$sendCards},{$sendCoupons}";
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    private function parseRights($discount,$type)
    {
        $discount = "会员折扣{$discount}折";
        $text = $type == 1 ? '自营商品包邮,' : '';
        return $text.$discount;
    }
}
