<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/23
 * Time: 16:46
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;

class OrderExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '实付金额',
            ],
            [
                'key' => 'vip_name',
                'value' => '超级会员卡',
            ],
            [
                'key' => 'card_name',
                'value' => '小标题',
            ],
            [
                'key' => 'goods_num',
                'value' => '数量',
            ],
            [
                'key' => 'xiaoji',
                'value' => '小计',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->with(['user.userInfo', 'detail.goods.goodsWarehouse'])
            ->orderBy('o.created_at DESC')
            ->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        (new CsvExport())->export($dataList, $this->fieldsNameList, $this->getFileName());
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $name = '超级会员卡';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $order = new Order();
        $number = 1;
        /** @var Order $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = $item->user->nickname;
            $arr['name'] = $item->name;
            $arr['created_at'] = $item->created_at;
            $arr['pay_type'] = $order->getPayTypeText($item->pay_type);
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['is_pay'] = $item->is_pay == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item->pay_time);
            $arr['words'] = $item->words;
            $arr['seller_remark'] = $item->seller_remark;
            // TODO 下单表单待完善
            $arr['remark'] = $item->remark;
            $arr['number'] = $number++;
            $arr['total_price'] = (float)$item->total_price;
            $arr['total_pay_price'] = (float)$item->total_pay_price;
            $extra = json_decode(current($item['detail'])['goods_info'], true);
            $main = CommonVip::getCommon()->getMainCard();
            $arr['vip_name'] = $main->name;
            $arr['card_name'] = $extra['rules_data']['name'];
            $arr['goods_num'] = 1;
            $arr['xiaoji'] = 1;
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
