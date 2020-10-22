<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('app-order');
?>
<style>
    .content {
        color: #E6A23C;
    }

    .other {
        height: 35px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <app-order
                active-name="0"
                :tabs="tabs"
                :select-list="selectList"
                :is-show-recycle="false"
                :is-show-confirm="false"
                :is-show-send="false"
                :is-show-clerk="false"
                :is-show-print="false"
                :is-show-finish="false"
                :is-show-order-status="false"
                :is-show-edit-address="false"
                edit-remark-url="plugin/bonus/mall/order/remark"
                order-url="plugin/bonus/mall/order/index"
                order-detail-url="plugin/bonus/mall/order/detail"
                :new-search="search">
            <!-- 标题栏 -->
            <template slot="orderTitle">
                <el-breadcrumb class="title" v-if="captain_name" separator="/">
                    <el-breadcrumb-item>
                        <span style="color: #409EFF;cursor: pointer"
                              @click="$navigate({r:'plugin/bonus/mall/order/index'})">
                            分红订单
                        </span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>队长{{captain_name}}</el-breadcrumb-item>
                </el-breadcrumb>
                <span class="title" v-else>分红订单</span>
            </template>
            <template slot="footerFirst" slot-scope="item">
                <div>队长信息：{{item.item.captain_name}} {{item.item.captain_mobile}}</div>
            </template>
            <template slot="footer" slot-scope="item">
                <div class="other" flex="main:justify cross:center">
                    <div class="content" :class="{ 'remark-show': !item.item.bonus_remark }">
                        <span v-if="item.item.bonus_remark">分红订单备注：{{item.item.bonus_remark}}</span>
                    </div>
                    <div>
                        <span>分红金额</span>
                        <span style="color: #E7A75E;font-size: 16px">￥{{item.item.bonus_price}}</span>
                    </div>
                </div>
            </template>
            <template slot="orderTag" slot-scope="order">
                <el-tag size="small" type="success" v-if="order.order.is_sale == 1">已完成</el-tag>
                <el-tag size="small" type="warning" v-else-if="order.order.is_sale == 0">未完成</el-tag>
                <el-tag size="small" v-if="order.order.send_type == 0">快递发送</el-tag>
                <el-tag size="small" v-if="order.order.send_type == 1">到店自提</el-tag>
            </template>
        </app-order>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                    platform: '',
                    status: '',
                    plugin: 'all',
                    send_type: -1,
                    n: 1
                },
                captain_id: null,
                captain_name: null,
                tabs: [
                    {value: '0', name: '全部'},
                    {value: '1', name: '未完成'},
                    {value: '2', name: '已完成'},
                ],
                orderTitle: [
                    {width: '35%', name: '订单信息'},
                    {width: '15%', name: '队长信息'},
                    {width: '15%', name: '收货人'},
                    {width: '20%', name: '实付金额'},
                    {width: '15%', name: '操作'},
                ],
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: '2', name: '用户名'},
                    {value: '4', name: '用户ID'},
                    {value: '5', name: '商品名称'},
                    {value: '3', name: '收件人'},
                    {value: '6', name: '收件人电话'},
                    {value: '7', name: '队长名'},
                ],
            };
        },
        created() {
        },
        methods: {}
    });
</script>
