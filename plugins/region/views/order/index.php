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
    .header-cell {
        background-color: #F5F7FA!important;
    }
    .rate-info {
        position: absolute;
        top: 24px;
        left: 104px;
        color: #BDBDBD;
    }

    .rate-info span {
        margin: 0 10px;
    }

    .detail-dialog .el-dialog__body {
        padding-top: 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <app-order
                title-label="分红订单"
                active-name="0"
                :tabs="tabs"
                :select-list="selectList"
                :is-show-recycle="false"
                :is-show-confirm="false"
                :is-show-remark="false"
                :is-show-send="false"
                :is-show-clerk="false"
                :is-show-print="false"
                :is-show-finish="false"
                :is-show-order-status="false"
                :is-show-edit-address="false"
                :show-more-info="true"
                :order-title="orderTitle"
                edit-remark-url="plugin/region/mall/order/remark"
                order-url="plugin/region/mall/order/index"
                order-detail-url="plugin/region/mall/order/detail"
                :new-search="search">
            <template slot="orderTag" slot-scope="order">
                <el-tag size="small" v-if="order.order.send_type == 0">快递发送</el-tag>
                <el-tag size="small" v-if="order.order.send_type == 1">到店自提</el-tag>
                <el-tag size="small" v-if="order.order.send_type == 2">同城配送</el-tag>
                <el-tag size="small" type="success" v-if="order.order.extra && order.order.extra.is_bonus == 1">已结算</el-tag>
                <el-tag size="small" type="warning" v-else-if="order.order.extra && order.order.extra.is_bonus == 0">未结算</el-tag>
            </template>
            <template slot="orderInfo" slot-scope="order">
                <div flex="dir:top" style="font-size: 16px">
                    <div>{{order.order.extra.bonus_rate}}%</div>
                    <div style="color: #FF9C54;margin-top: 10px">￥{{order.order.extra.bonus_price}}</div>
                </div>
            </template>
            <template slot="orderSend" slot-scope="order">
                <el-tooltip class="item" effect="dark" content="分红详情" placement="top">
                    <img class="app-order-icon" @click="showBonus(order.order.extra)" src="statics/img/mall/detail.png" alt="">
                </el-tooltip>
            </template>
        </app-order>
    </el-card>
    <el-dialog title="分红详情" :visible.sync="dialogVisible" class="detail-dialog" width="40%">
        <div class="rate-info">订单分红比例:<span>{{detail.bonus_rate}}%</span> 订单分红金额:<span>{{detail.bonus_price}}</span></div>
        <el-table :header-cell-class-name="headerCell" :data="bonusInfo" border>
            <el-table-column prop="level" label="代理级别"></el-table-column>
            <el-table-column prop="rate" label="代理分红比例"></el-table-column>
            <el-table-column prop="num" label="代理人数"></el-table-column>
            <el-table-column prop="price" label="代理分红金额/人"></el-table-column>
        </el-table>
    </el-dialog>
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
                    bonus_status: '',
                    plugin: 'all',
                    send_type: -1,
                },
                bonusInfo: [
                    {level:'省代理',rate: '', num: '', price:''},
                    {level:'市代理',rate: '', num: '', price:''},
                    {level:'区/县代理',rate: '', num: '', price:''},
                ],
                detail: {},
                dialogVisible: false,
                captain_id: null,
                captain_name: null,
                tabs: [
                    {value: '0', name: '全部'},
                    {value: '2', name: '未结算'},
                    {value: '1', name: '已结算'},
                ],
                orderTitle: [
                    {width: '45%', name: '订单信息'},
                    {width: '20%', name: '实付金额'},
                    {width: '20%', name: '订单分红比例/订单分红金额'},
                    {width: '15%', name: '操作'},
                ],
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: '2', name: '用户名'},
                    {value: '4', name: '用户ID'},
                    {value: '5', name: '商品名称'},
                    {value: '3', name: '收货人'},
                    {value: '6', name: '收货人电话'},
                    {value: '7', name: '门店名称'},
                    {value: '8', name: '商品货号'},
                ],
            };
        },
        created() {
        },
        methods: {
            headerCell:function(row, column){
                return 'header-cell'
            },
            showBonus(regionOrder) {
                this.detail = regionOrder;
                this.dialogVisible = true;
                this.bonusInfo[0].rate = regionOrder.province_rate + '%';
                this.bonusInfo[1].rate = regionOrder.city_rate + '%';
                this.bonusInfo[2].rate = regionOrder.district_rate + '%';
                this.bonusInfo[0].num = regionOrder.province_num;
                this.bonusInfo[1].num = regionOrder.city_num;
                this.bonusInfo[2].num = regionOrder.district_num;
                this.bonusInfo[0].price = '￥' + regionOrder.province_price;
                this.bonusInfo[1].price = '￥' + regionOrder.city_price;
                this.bonusInfo[2].price = '￥' + regionOrder.district_price;

            }
        }
    });
</script>
