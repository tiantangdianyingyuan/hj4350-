<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 13:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints;


use app\forms\common\prints\content\GoodsContent;
use app\forms\common\prints\content\OrderContent;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\printer\FeiePrinter;
use app\forms\common\prints\printer\GpPrinter;
use app\forms\common\prints\printer\KdtPrinter;
use app\forms\common\prints\printer\YilianyunPrinter;
use app\forms\common\prints\templates\FirstTemplate;
use app\models\Form;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Printer;
use app\models\PrinterSetting;
use app\models\Store;
use app\plugins\mch\models\Mch;

class PrintOrder extends Model
{
    /**
     * @param $order
     * @param $orderId
     * @param $printType
     * @param int $print_id
     * @return string
     * @throws PrintException
     */
    public function print($order, $orderId, $printType, int $print_id = 0)
    {
        if (!$order) {
            $order = Order::findOne(['is_delete' => 0, 'id' => $orderId, 'mall_id' => \Yii::$app->mall->id]);
        }
        if (!$order) {
            throw new PrintException('需要订单信息');
        }
        $data = $this->getData($order);

        $query = PrinterSetting::find()->where([
            'mch_id' => $order->mch_id,
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'status' => 1])->with(['printer']);
        $print_id && $query->andWhere(['id' => $print_id]);

        // 自提订单小票打印根据 门店
        if ($order->send_type == 1) {
            $query->andWhere([
                'or',
                ['store_id' => $order->store_id],
                ['store_id' => 0]
            ]);
        } else {
            $query->andWhere(['store_id' => 0]);
        }

        $printSetting = $query->all();

        $count = 0;
        /* @var PrinterSetting[] $printSetting */
        foreach ($printSetting as $item) {
            $printTypeList = \Yii::$app->serializer->decode($item->type);
            $printTypeList['reprint'] = 1;

            $orderSendTypeList = \Yii::$app->serializer->decode($item->order_send_type);
            if (
                $printType !== 'reprint' &&
                (isset($orderSendTypeList[$order->send_type]) && $orderSendTypeList[$order->send_type] == 0)
            ) {
                \Yii::warning('发货方式不支持');
                continue;
            }
            if (!(isset($printTypeList[$printType]) && $printTypeList[$printType] == 1)) {
                \Yii::warning('打印设置打印方式');
                continue;
            }
            $data->is_attr = $item->is_attr;
            $data->new_goods_list = $this->getNewGoodsList($order, $item);

            /* @var Printer $printer */
            $printer = $item->printer;
            if (!$printer) {
                \Yii::warning('错误的打印机');
                continue;
            }
            if ($printer->is_delete == 1) {
                \Yii::warning('打印机已被删除');
                continue;
            }
            $config = \Yii::$app->serializer->decode($printer->setting);
            $limit = 0;
            switch ($printer->type) {
                case Printer::P_360_KDT2:
                    \Yii::warning('365kdt打印');
                    $printer = new KdtPrinter($config);
                    $limit = 5000;
                    break;
                case Printer::P_FEIE:
                    \Yii::warning('飞鹅打印');
                    $printer = new FeiePrinter($config);
                    $limit = 5000;
                    break;
                case Printer::P_YILIANYUN_K4:
                    \Yii::warning('易联云打印');
                    $printer = new YilianyunPrinter($config);
                    break;
                case Printer::P_GAINSCHA_GP:
                    \Yii::warning('佳博打印');
                    $config['orderNo'] = $order->order_no;
                    $printer = new GpPrinter($config);
                    break;
                default:
                    \Yii::warning('未知的打印机设置');
                    $printer = null;
            }
            try {
                if (!$printer) {
                    throw new PrintException('未知打印机设置');
                }
                // TODO 暂时只有一个模板
                $template = new FirstTemplate();
                $template->data = $data;
                $template->printer = $item;
                $printer->print($template->getContentByArray(), $limit);
                $count++;
            } catch (PrintException $exception) {
                \Yii::error($exception);
                continue;
            }
        }
        if ($count > 0) {
            return "打印成功,总共{$count}个打印机打印";
        } else {
            throw new PrintException('没有打印机打印');
        }
    }

    /**
     * @param Order $order
     * @return array
     * @throws PrintException
     */
    private function getGoodsList($order)
    {
        try {
            /* @var OrderDetail[] $orderDetail */
            $orderDetail = $order->detail;
            $data = [];
            foreach ($orderDetail as $item) {
                /* @var Goods $goods */
                $goods = $item->goods;
                $goodsInfo = $item->decodeGoodsInfo();
                $attr = [];
                foreach ($goodsInfo['attr_list'] as $attrList) {
                    $attr[] = $attrList['attr_group_name'] . ':' . $attrList['attr_name'];
                }
                $attr = implode(',', $attr);
                $data[] = new GoodsContent([
                    'num' => $item->num,
                    'total_price' => $item->total_price,
                    'unit_price' => $item->unit_price,
                    'name' => $goods->name,
                    'attr' => $attr
                ]);
            }
            return $data;
        } catch (\Exception $e) {
            throw new PrintException($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return array
     * 订单额外信息
     */
    private function getExtra($order)
    {
        $orderType = '商城订单';
        try {
            if ($order->sign == '') {
                throw new \Exception('商城订单');
            }
            $plugin = \Yii::$app->plugin->getPlugin($order->sign);
            $orderType = $plugin->getDisplayName() . '订单';
        } catch (\Exception $exception) {
        }

        try {
            $orderForm = \Yii::$app->serializer->decode($order->order_form);
        } catch (\Exception $exception) {
            $orderForm = [];
        }

        return [
            'order_type' => $orderType,
            'pay_type' => $order->getPayTypeText(),
            'send_type_text' => $order->getSendType(),
            'order_form' => $orderForm
        ];
    }

    /**
     * @param Order $order
     * @return array
     * @throws PrintException
     * 订单门店信息
     */
    private function getStore($order)
    {
        $store = [];
        if ($order->send_type == 1) {
            $store = Store::findOne(['id' => $order->store_id, 'is_delete' => 0]);
            if (!$store) {
                throw new PrintException('门店不存在');
            }
            return [
                'store_name' => $store->name,
                'store_mobile' => $store->mobile,
                'store_address' => $store->address
            ];
        }
        return $store;
    }

    /**
     * @param Order $order
     * @return OrderContent
     * @throws PrintException
     */
    public function getData($order)
    {
        $data = new OrderContent();
        $data->mall_name = $this->getMallName($order);
        $data->attribute = $order;
        $data->attribute = $this->getExtra($order);
        $data->goods_list = $this->getGoodsList($order);
        $data->attribute = $this->getStore($order);
        $data->plugin_data = $this->getPluginData($order);
        return $data;
    }

    /**
     * @param Order $order
     * @return string
     */
    public function getMallName($order)
    {
        if ($order->mch_id) {
            $detail = Mch::findOne([
                'id' => $order->mch_id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            return $detail->store->name;
        }
        return \Yii::$app->mall->name;
    }

    /**
     * 表单商品列表
     * @param $order
     * @param $print
     * @return array
     * @throws PrintException
     */
    public function getNewGoodsList($order, $print)
    {
        try {
            $is_form_data = \yii\helpers\BaseJson::decode($print->show_type)['form_data'] ?? false;

            $orderDetail = $order->detail;
            $form_ids = array_column($orderDetail, 'form_id');

            if (empty($form_ids)) {
                $is_form_data = false;
            } else {
                array_multisort($form_ids, SORT_DESC, $orderDetail);
                $ids = array_unique($form_ids);
                if (count($ids) <= 2 && in_array(0, $ids)) {
                    $is_form_data = false;
                }
            }

            $data = [];
            foreach ($orderDetail as $item) {
                /* @var Goods $goods */
                $goods = $item->goods;

                $goodsInfo = $item->decodeGoodsInfo();
                $attr = [];
                foreach ($goodsInfo['attr_list'] as $attrList) {
                    $attr[] = $attrList['attr_group_name'] . ':' . $attrList['attr_name'];
                }
                $attr = implode(',', $attr);
                $goods_list = new GoodsContent([
                    'goods_no' => $item->goods_no,
                    'num' => $item->num,
                    'total_price' => $item->total_price,
                    'unit_price' => $item->unit_price,
                    'name' => $goods->name,
                    'attr' => $attr,
                ]);

                $sentinel = true;
                foreach ($data as &$info) {
                    if ($info['form_id'] == $item->form_id || $is_form_data == 0) {
                        $sentinel = false;
                        array_push($info['goods_list'], $goods_list);
                    }
                }
                unset($info);

                $sentinel && $data[] = [
                    'goods_list' => [$goods_list],
                    'form_data' => !\yii\helpers\BaseJson::decode($item->form_data) ? \yii\helpers\BaseJson::decode($order->order_form) ?: [] : \yii\helpers\BaseJson::decode($item->form_data),
                    'form_id' => $item->form_id,
                    'form_name' => Form::findOne($item->form_id)['name'] ?? ''
                ];
            }
            return array_reverse($data);
        } catch (\Exception $e) {
            throw new PrintException($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return array
     * 获取插件额外信息
     */
    public function getPluginData($order)
    {
        $pluginData = [];
        $plugins = \Yii::$app->plugin->getList();
        foreach ($plugins as $key => $plugin) {
            try {
                $class = \Yii::$app->plugin->getPlugin($plugin->name);
                if (($plugin->name == $order->sign || $plugin->name == 'vip_card') && method_exists($class, 'getOrderInfo')) {
                    $data = $class->getOrderInfo($order->id, $order);
                    if (isset($data['print_list'])) {
                        $pluginData = array_merge($pluginData, $data['print_list']);
                    }
                }
            } catch (\Exception $exception) {
            }
        }
        return $pluginData;
    }
}
