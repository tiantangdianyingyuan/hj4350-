<?php
Yii::$app->loadViewComponent('app-order');
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <app-order order-url="plugin/advance/mall/order/index"
                   :select-list="selectList"
                   :tabs="tabs"
                   order-detail-url="plugin/advance/mall/order/detail"
                   recycle-url="plugin/advance/mall/order/destroy-all">
            <slot name="orderTitle">尾款订单</slot>
        </app-order>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: 'advance_no', name: '定金订单号'},
                    {value: '9', name: '商户单号'},
                    {value: '2', name: '用户名'},
                    {value: '4', name: '用户ID'},
                    {value: '5', name: '商品名称'},
                    {value: '3', name: '收件人'},
                    {value: '6', name: '收件人电话'},
                    {value: '7', name: '门店名称'}
                ],
                tabs: [
                    {value: '-1', name: '全部'},
                    {value: '0', name: '未付款'},
                    {value: '1', name: '待发货'},
                    {value: '2', name: '待收货'},
                    {value: '3', name: '已完成'},
                    {value: '4', name: '待处理/待退款'},
                    {value: '5', name: '已取消'},
                    {value: '7', name: '回收站'},
                ],
            };
        },
        methods: {},
    });
</script>
