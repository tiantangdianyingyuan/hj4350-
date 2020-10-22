<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 17:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
/**
 * @var \app\models\Mall $mall
 * @var \app\models\Order $order
 * @var $new_goods_list
 * @var $show_type
 */
?>
<?php
$table_tb = 2;
$is_form_data = $show_type['form_data'] ? 1 : 0;
$is_goods_no = $show_type['goods_no'] ? 1 : 0;
$is_attr = $show_type['attr'] ? 1 : 0;
$show_type_num = $is_attr + $is_goods_no + $table_tb;
?>
<style>
    .table {
        max-width: 500rem;
        width: 30rem;
        margin-bottom: 10px;
    }

    tr {
        max-width: 500rem;
    }

    .td-1 {
        width: 50%;
    }

    .td-2 {
        width: 20%;
    }

    .td-3 {
        width: 10%;
    }

    .td-4 {
        width: 20%;
    }
</style>
<p>尊敬的:<b><?= $mall->name; ?></b></p>
<h1>您有一个新的订单</h1>
<p>订单号：<?= $order->order_no ?></p>
<p>订单总金额：<?= $order->total_pay_price ?></p>
<?php foreach ($new_goods_list as $k => $goods) : ?>
    <table class="table" border="1" rules="all">
        <thead>
        <tr>
            <td class="td-1">商品名</td>
            <td class="td-4" <?= $is_goods_no ? 'show' : 'hidden' ?> >货号</td>
            <td class="td-2" <?= $is_attr ? 'show' : 'hidden' ?> >规格</td>
            <td class="td-3">数量</td>
        </tr>
        </thead>
        <?php foreach ($goods['goods_list'] as $v) : ?>
            <tr>
                <td class="td-1"><?= $v->goods->name ?></td>
                <td class="td-4" <?= $is_goods_no ? 'show' : 'hidden' ?> ><?= $v->goods_no ?></td>
                <td class="td-2" <?= $is_attr ? 'show' : 'hidden' ?> >
                    <?php foreach (json_decode($v->goods_info, true)['attr_list'] as $index => $value) : ?>
                        <span style="font-size: 10px;"><?= $value['attr_group_name'] ?>
                            ：<?= $value['attr_name'] ?></span>
                    <?php endforeach; ?>
                </td>
                <td class="td-3"><?= $v->num ?></td>
            </tr>
        <?php endforeach; ?>

        <tr <?= ($is_form_data && $goods['form_name']) ? 'show' : 'hidden' ?> >
            <td colspan="<?= $show_type_num ?>">
                <?= $goods['form_name'] ?>
            </td>
        </tr>
        <?php foreach ($goods['form_data'] as $form_data): ?>
            <tr <?= $is_form_data ? 'show' : 'hidden' ?> >
                <td colspan="<?= $show_type_num ?>">
                    <?php if ($form_data['key'] === 'checkbox'): ?>
                        <?= $form_data['label'] ?>：<?= is_array($form_data['value']) ? implode(',', $form_data['value']) : $form_data['value'] ?>
                    <?php else: ?>
                        <?= $form_data['label'] ?>：<?= $form_data['value'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>
<?php if ($order->send_type == 1) : ?>
    <p>自提门店：<?= $order->store->name ?></p>
    <p>门店地址：<?= $order->store->address ?></p>
<?php endif; ?>
<?php if ($order->name) : ?>
    <p>收货人：<?= $order->name ?></p>
<?php endif; ?>
<?php if ($order->mobile) : ?>
    <p>收货人电话：<?= $order->mobile ?></p>
<?php endif; ?>
<?php if ($order->address) : ?>
    <p>收货地址：<?= $order->address ?></p>
<?php endif; ?>
<p>下单时间：<?= $order->created_at; ?></p>
<p>请及时进入商城处理</p>

