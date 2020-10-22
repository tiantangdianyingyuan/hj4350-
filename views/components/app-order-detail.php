<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */

Yii::$app->loadViewComponent('order/app-edit-address');
Yii::$app->loadViewComponent('order/app-edit-seller-remark');
Yii::$app->loadViewComponent('order/app-clerk');
Yii::$app->loadViewComponent('order/app-send');
Yii::$app->loadViewComponent('order/app-invoice');
Yii::$app->loadViewComponent('order/app-edit-price');
Yii::$app->loadViewComponent('order/app-city');
Yii::$app->loadViewComponent('order/app-select-print');
?>

<style>
    .app-order-detail .app-order-count-price {
        float: right;
        margin-right: 55px;
        font-size: 12px;
        text-align: right;
    }

    .app-order-detail .el-step__icon-inner {
        font-size: 30px;
    }

    .app-order-detail .app-order-status {
        padding: 50px 120px;
        margin-bottom: 30px;
    }

    .app-order-detail .app-order-status .el-step__icon.is-text {
        border: 0px;
        width: 40px;
    }

    .app-order-detail .app-order-count-price .el-form-item {
        margin-bottom: 5px;
    }

    .app-order-detail .el-collapse-item__header {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        border-bottom: none;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    .app-order-detail .el-collapse-item:last-child {
        margin-bottom: 0;
    }

    .app-order-detail .el-collapse {
        border: none;
    }

    .app-order-detail .order-status {
        display: flex;
        flex-wrap: wrap;
    }

    .app-order-detail .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .app-order-detail .order-status .el-form-item {
        width: 50%;
        min-width: 250px;
    }

    .app-order-detail .el-step.is-center .el-step__description {
        padding: 0 10%;
    }

    /*新的*/
    .app-order-detail .card-box {
        border: 1px solid #EBEEF5;
        border-radius: 3px;
        padding: 10px;
        height: 300px;
        overflow-y: scroll;
    }

    .app-order-detail .card-box .label {
        color: #999999;
        margin-right: 10px;
    }

    .app-order-detail .share-price {
        color: #EF8933;
    }

    .app-order-detail .share-price .orange-label {
        color: #EF8933;
    }

    .app-order-detail .share-title {
        font-size: 15px;
        margin: 10px 0 5px;
    }

    .app-order-detail .action-box {
        padding: 10px 20px;
    }

    .app-order-detail .item-box {
        margin-bottom: 10px;
    }

    .app-order-detail .store-address {
        margin-left: 65px;
        margin-top: 5px;
    }

    .app-order-detail .express-address {
        width: 80%;
    }

    .app-order-detail .goods-pic {
        width: 35px;
        height: 35px;
        margin: 0 4px;
    }

    .order-detail-form-list {
        margin: 0 -10px;
    }

    .order-detail-form-item {
        border-top: 1px solid #EBEEF5;
        padding: 10px;
    }

    .ecard-expand {
        text-align: center;
        height: 35px;
        line-height: 35px;
        border-top: 1px solid #ebeef5;
        cursor: pointer;
    }
    .ecard-expand img.small {
        transform: rotate(90deg);
    }
    .ecard-expand img.big {
        transform: rotate(270deg);
    }
    .item-margin {
        padding: 0 10px;
    }
    #print {
        position: absolute;
        top: 50px;
        right: -1000px;
    }
</style>

<template id="app-order-detail">
    <div class="app-order-detail">
        <app-edit-address
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="addressVisible"
                :order="newOrder">
        </app-edit-address>
        <app-edit-seller-remark
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="sellerRemarkVisible"
                :order="newOrder">
        </app-edit-seller-remark>
        <app-clerk
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="clerkVisible"
                :order="newOrder">
        </app-clerk>
        <app-send
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="sendVisible"
                :send-type="sendType"
                :express-id="expressId"
                :order="newOrder">
        </app-send>
        <app-edit-price
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="changePriceVisible"
                :order="newOrder">
        </app-edit-price>
        <app-city
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="citySendVisible"
                :send-type="sendType"
                :order="newOrder">
        </app-city>
        <app-invoice
                @close="dialogClose"
                :order="newOrder"
                :is-show="invoiceVisible"
                @select_template="select_template"
        ></app-invoice>
        <app-select-print v-model="hasPrintStatus" :order-id="parseInt(order.id)"></app-select-print>
        <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <!-- 标题栏 -->
            <div slot="header">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <span style="color: #409EFF;cursor: pointer" @click="toList">{{titleLabel}}</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>订单详情</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <!-- 订单进度 -->
            <div class="table-body" v-loading="loading">
                <el-card class="app-order-status" shadow="never">
                    <el-steps v-if="isShowSteps" :active="active" align-center>
                        <el-step title="已下单" :description="order.created_at">
                            <template slot="icon">
                                <img src="statics/img/mall/order/status/status_1_active.png">
                            </template>
                        </el-step>
                        <el-step :title="active > 1 && order.is_pay == 1 ? '已付款':'未付款'"
                                 v-show="order.cancel_status != 1 || order.is_pay == 1">
                            <template slot="icon">
                                <img v-if="active > 1 && order.is_pay == 1"
                                     src="statics/img/mall/order/status/status_2_active.png">
                                <img v-else src="statics/img/mall/order/status/status_2.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.pay_time != '0000-00-00 00:00:00'">{{order.pay_time}}</div>
                                <div v-if="order.is_pay == 0 && order.pay_type != 2 && order.auto_cancel_time"
                                     style="color: #ff4544">预计 {{order.auto_cancel_time}} 自动取消订单
                                </div>
                            </template>
                        </el-step>
                        <el-step :title="active > 2 ? '已发货':'未发货'" v-show="order.cancel_status != 1"
                                 :description="order.send_time != '0000-00-00 00:00:00' ? order.send_time : ''">
                            <template slot="icon">
                                <img v-if="active > 2" src="statics/img/mall/order/status/status_3_active.png">
                                <img v-else src="statics/img/mall/order/status/status_3.png">
                            </template>
                        </el-step>
                        <el-step :title="active > 3 ? '已收货':'未收货'"
                                 v-show="order.cancel_status != 1 && order.is_send == 1">
                            <template slot="icon">
                                <img v-if="active > 3" src="statics/img/mall/order/status/status_4_active.png">
                                <img v-else src="statics/img/mall/order/status/status_4.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.confirm_time != '0000-00-00 00:00:00'">{{order.confirm_time}}</div>
                                <div v-if="order.is_send == 1 && order.is_confirm == '0' && order.auto_confirm_time"
                                     style="color: #ff4544">预计 {{order.auto_confirm_time}} 自动确认收货
                                </div>
                            </template>
                        </el-step>
                        <el-step :title="active > 4 ? '已结束':'未结束'"
                                 v-show="order.cancel_status != 1 || order.is_confirm == 1">
                            <template slot="icon">
                                <img v-if="active > 4" src="statics/img/mall/order/status/status_5_active.png">
                                <img v-else src="statics/img/mall/order/status/status_5.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.confirm_time != '0000-00-00 00:00:00' && order.is_sale == 1">
                                    {{order.confirm_time}}
                                </div>
                                <div v-if="order.is_confirm == 1 && order.is_sale == 0 && order.auto_sales_time"
                                     style="color: #ff4544">预计 {{order.auto_sales_time}} 自动结束订单
                                </div>
                            </template>
                        </el-step>
                        <el-step title="已取消"
                                 :description="order.cancel_time != '0000-00-00 00:00:00' ? order.cancel_time : ''"
                                 v-if="order.cancel_status == 1">
                            <template slot="icon">
                                <img src="statics/img/mall/order/status/status_6_active.png">
                            </template>
                        </el-step>
                    </el-steps>
                    <slot name="steps"></slot>
                </el-card>
                <el-row :gutter="12">
                    <el-col :span="8">
                        <div class="card-box" style="padding: 0;">
                            <h3 style="padding: 10px 10px 0 10px">订单信息</h3>
                            <div class="item-box item-margin" flex="dir:left cross:center">
                                <span class="label">订单号:</span>
                                <div>{{ order.order_no}}</div>
                            </div>
                            <div v-if="order.paymentOrder" class="item-box item-margin" flex="dir:left cross:center">
                                <span class="label">商户单号:</span>
                                <div>{{ order.paymentOrder.paymentOrderUnion.order_no }}</div>
                            </div>
                            <div class="item-box item-margin" flex="dir:left cross:center">
                                <span class="label">支付方式:</span>
                                <el-tag size="small" hit type="success" v-if="order.pay_type == 1">线上支付</el-tag>
                                <el-tag size="small" hit type="success" v-else-if="order.pay_type == 3">余额支付</el-tag>
                                <el-tag size="small" hit type="success" v-else-if="order.pay_type == 2">货到付款</el-tag>
                            </div>
                            <div class="item-box item-margin" flex="dir:left cross:center" v-if="order.goods_type == 'goods' || order.goods_type == 'exchange'">
                                <span class="label">配送方式:</span>
                                <el-tag size="small" hit type="primary" v-if="order.send_type == 0">快递发送</el-tag>
                                <el-tag size="small" hit type="primary" v-else-if="order.send_type == 1">到店自提</el-tag>
                                <el-tag size="small" hit type="primary" v-else-if="order.send_type == 2">同城配送</el-tag>
                                <el-tag size="small" hit type="primary" v-else-if="order.send_type == 3">自动发货</el-tag>
                            </div>
                            <div class="item-box item-margin" flex="dir:left cross:center">
                                <span class="label">用户:</span>
                                <div>{{ order.user.nickname }}</div>
                            </div>
                            <div class="item-box item-margin" v-if="order.name" flex="dir:left cross:center">
                                <span class="label">{{order.send_type == 0 ? '收货人' : '联系人'}}:</span>
                                <div>{{ order.name }}</div>
                            </div>
                            <div class="item-box item-margin" v-if="order.mobile" flex="dir:left cross:center">
                                <span class="label">电话:</span>
                                <div>{{ order.mobile }}</div>
                            </div>
                            <div class="item-box item-margin" flex="dir:left cross:center" v-if="order.goods_type == 'ecard' && order.paymentOrder">
                                <span class="label">卡密名称:</span>
                                <div>{{order.paymentOrder.title}}</div>
                            </div>
                            <div v-if="order.goods_type == 'ecard'">
                                <div class="item-box item-margin" flex="wrap:nowrap" v-for="(item, index) in ecard">
                                    <div>【{{index+1}}】</div>
                                   <div>
                                       <div v-for="(it, key) in item" style="float:left">
                                           <span style="max-height: " class="label">{{it.key}}:</span>
                                           <span>{{ it.value }}</span>
                                       </div>
                                   </div>
                                </div>
                            </div>
                            <div v-if="order.sign == 'exchange' && order.exchange_code">
                                <div class="item-box item-margin" flex="dir:left cross:center">
                                    <span class="label">兑换码库:</span>
                                    <div>{{ order.exchange_code.library_name }}</div>
                                </div>
                                <div class="item-box item-margin" flex="dir:left cross:center">
                                    <span class="label">兑换码:</span>
                                    <div>{{ order.exchange_code.code }}</div>
                                </div>
                            </div>
                            <div class="ecard-expand" @click="ecardExpand" v-if="order.type_data && order.type_data.ecard && order.type_data.ecard.length > 1">
                                {{ecard.length > 1 ? '收起全部' : '展开全部'}}
                                <image src="statics/img/mall/customize_jp.png" :class="ecard.length > 1 ? 'big' : 'small'"></image>
                            </div>
                            <div class="item-box item-margin" flex="dir:left cross:center">
                                <template v-if="order.send_type == 1">
                                    <span class="label">收货地址:</span>
                                    <el-tag size="small" hit type="warning">到店自提</el-tag>
                                </template>
                                <template v-else-if="order.address">
                                    <span class="label">收货地址:</span>
                                    <div class="express-address">
                                        {{ order.address }}
                                        <el-button
                                                v-if="isShowEditAddress && order.send_type != 2 && order.is_send==0 && order.cancel_status==0"
                                                type="text"
                                                icon="el-icon-edit"
                                                circle
                                                size="small"
                                                @click="openDialog(order, addressVisible = true)">
                                        </el-button>
                                    </div>
                                </template>
                            </div>
                            <!-- 物流信息 -->
                            <template>
                                <!-- TODO 兼容 -->
                                <div v-if="order.is_send == 1 && order.detailExpress.length == 0 && order.express && order.express_no"
                                     class="item-box item-margin"
                                     flex="dir:left cross:center">
                                    <span class="label">{{order.send_type == 2 ? '配送员' : '物流信息'}}:</span>
                                    <template v-if="order.send_type == 2">
                                           <span>{{order.city_name}}</span>
                                           <span style="margin: 0 10px;">{{order.city_mobile}}</span>
                                       </template>
                                    <template v-else>
                                        <el-tag style="margin-right: 5px;" type="info" hit size="small">{{ order.express}}
                                        </el-tag>
                                        <a :href="'https://www.baidu.com/s?wd='+ order.express + order.express_no"
                                           target="_blank" title='点击搜索运单号'>{{ order.express_no }}</a>
                                    </template>
                                    <el-button v-if="isShowSend && order.is_confirm == 0"
                                               type="text"
                                               icon="el-icon-edit"
                                               circle
                                               style="padding: 0 12px;"
                                               @click="openExpress(order,'send')">
                                    </el-button>
                                    <el-button v-if="order.expressSingle" size="mini" @click="printTeplate(order.expressSingle.print_teplate)">打印此面单</el-button>
                                </div>
                                <div v-else-if="order.is_send == 1 && order.detailExpress.length == 1" class="item-box item-margin"
                                     flex="dir:top">
                                    <div flex="dir:left cross:center">
                                        <span class="label">{{order.send_type == 2 ? '配送员' : '物流信息'}}:</span>
                                        <!-- 同城配送 -->
                                        <template v-if="order.send_type == 2">
                                            <span>{{order.detailExpress[0].city_name}}</span>
                                            <span style="margin: 0 10px;">{{order.detailExpress[0].city_mobile}}</span>
                                        </template>
                                        <!-- 快递配送 -->
                                        <template v-else>
                                            <!-- 快递配送发货 -->
                                            <template v-if="order.detailExpress[0].send_type == 1">
                                                <el-tag style="margin-right: 5px;" type="info" hit size="small">{{
                                                    order.detailExpress[0].express }}
                                                    </el-tag>
                                                    <a :href="'https://www.baidu.com/s?wd='+ order.detailExpress[0].express + order.detailExpress[0].express_no"
                                                   target="_blank" title='点击搜索运单号'>{{ order.detailExpress[0].express_no }}</a>
                                            </template>
                                            <!-- 无需物流发货 -->
                                            <template v-else>
                                                <span>{{order.detailExpress[0].express_content}}</span>
                                            </template>
                                        </template>
                                        <el-button v-if="isShowSend && order.is_confirm == 0"
                                                   type="text"
                                                   icon="el-icon-edit"
                                                   circle
                                                   style="padding: 0 12px;"
                                                   @click="openExpress(order,'change', order.detailExpress[0].id)">
                                        </el-button>
                                        <el-button v-if="order.detailExpress[0].expressSingle" size="mini" @click="printTeplate(order.detailExpress[0].expressSingle.print_teplate)">打印此面单</el-button>
                                    </div>
                                    <div flex="dir:left" v-if="order.detailExpress[0].send_type == 1 && order.detailExpress[0].merchant_remark">
                                        <span class="label">商家留言:</span>
                                        <span>{{order.detailExpress[0].merchant_remark}}</span>
                                    </div>
                                </div>
                                <div v-else-if="order.detailExpress.length >= 1"
                                     v-for="(expressItem, expressIndex) in order.detailExpress" :key="expressItem.id"
                                     class="item-box item-margin" flex="dir:left">
                                    <div>
                                        <div class="label" style="background: #fffaef;color: #e6a23c;padding: 3px 0;">
                                            {{order.send_type == 2 ? '配送员' : '收货信息'}}:{{expressIndex + 1}}
                                        </div>
                                    </div>
                                    <div flex="dir:top">
                                        <div flex="cross:center">
                                            <!-- 同城配送 -->
                                            <template v-if="order.send_type == 2">
                                                <span>{{expressItem.city_name}}</span>
                                                <span style="margin: 0 10px;">{{expressItem.city_mobile}}</span>
                                            </template>
                                            <!-- 快递配送 -->
                                            <template v-else>
                                                <!-- 快递配送发货 -->
                                                <template v-if="expressItem.send_type == 1">
                                                    <el-tag style="margin-right: 5px;" type="info" hit size="small">{{expressItem.express}}</el-tag>
                                                    <a :href="'https://www.baidu.com/s?wd='+ expressItem.express + expressItem.express_no"
                                                       target="_blank" title='点击搜索运单号'>{{ expressItem.express_no }}</a>
                                                </template>
                                                <!-- 无需物流发货 -->
                                                <template v-else>
                                                    <span>{{expressItem.express_content}}</span>
                                                </template>
                                            </template>
                                            <el-button v-if="isShowSend && order.is_confirm == 0"
                                                       type="text"
                                                       style="padding: 2px 12px"
                                                       icon="el-icon-edit"
                                                       circle
                                                       @click="openExpress(order,'change', expressItem.id)">
                                            </el-button>
                                            <el-button v-if="expressItem.expressSingle" size="mini" @click="printTeplate(expressItem.expressSingle.print_teplate)">打印此面单</el-button>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 10px;" v-if="expressItem.send_type == 1">
                                            <span class="label">商家留言:</span>
                                            <span>{{expressItem.merchant_remark}}</span>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 10px;">
                                            <span class="label">配送商品:</span>
                                            <img v-for="erItem in expressItem.expressRelation"
                                                 :key="erItem.id"
                                                 class="goods-pic"
                                                 :src="erItem.orderDetail.goods_info.goods_attr.pic_url ? erItem.orderDetail.goods_info.goods_attr.pic_url : erItem.orderDetail.goods_info.goods_attr.cover_pic">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-if="order.send_type == 1" class="item-box item-margin" flex="dir:top">
                                <div flex="dir:left cross:center">
                                    <span class="label">自提门店:</span>
                                    <el-tag type="info" hit size="small">{{ order.store.name }}</el-tag>
                                </div>
                                <div class="store-address">{{ order.store.address }}</div>
                            </div>
                            <div v-if="order.clerk != null" class="item-box item-margin" flex="dir:left cross:center">
                                <span class="label">核销人:</span>
                                <el-tag type="info" hit size="small">{{ order.clerk.nickname }}</el-tag>
                            </div>
                            <div v-if="order.orderClerk != null && order.orderClerk.clerk_remark" class="item-box item-margin"
                                 flex="dir:left cross:center">
                                <span class="label">核销备注:</span>
                                <div>{{order.orderClerk.clerk_remark}}</div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8" v-if="typeof order.goods_type === undefined || order.goods_type === 'goods'">
                        <div flex="dir:top" class="card-box">
                            <h3>表单信息</h3>
                            <!-- 排除身份证 -->
                            <div style="flex-shrink: 0" v-if="item.value && [`,`,``].indexOf(item.value.toString()) === -1" v-for="(item, index) in order.order_form" :key="index"
                                 class="item-box" flex="dir:left cross:center">
                                <span class="label">{{item.label}}:</span>
                                <div v-if="Array.isArray(item.value) && item.key == `img_upload`"
                                     flex="dir:left">
                                    <div v-for="(img,key) in item.value" :key="key">
                                        <el-image
                                                style="width: 80px; height: 80px"
                                                v-if="img"
                                                :src="img"
                                                :preview-src-list="[img]">
                                        </el-image>
                                    </div>
                                </div>
                                <div v-else-if="item.key==`img_upload`">
                                    <el-image
                                            style="width: 80px; height: 80px"
                                            v-if="item.value"
                                            :src="item.value"
                                            :preview-src-list="[item.value]">
                                    </el-image>
                                </div>
                                <span v-else>{{item.value}}</span>
                            </div>
                            <div style="flex-shrink: 0" v-if="order.remark" class="item-box" flex="dir:left cross:center">
                                <span class="label">买家留言:</span>
                                <div>{{order.remark}}</div>
                            </div>
                            <div style="flex-shrink: 0" v-if="order.words || order.seller_remark" class="item-box" flex="dir:left cross:center">
                                <span class="label">商家备注:</span>
                                <div>{{order.seller_remark ? order.seller_remark : order.words}}
                                    <i v-if="isShowRemark"
                                       class="el-icon-edit"
                                       style="color: #409EFF;cursor: pointer;margin-left: 10px;"
                                       @click="openDialog(order, sellerRemarkVisible = true)">
                                    </i>
                                </div>
                            </div>
                            <div class="order-detail-form-list">
                                <div class="order-detail-form-item"
                                     v-for="(orderDetail, orderDetailIndex) in order.detail"
                                     :key="orderDetailIndex"
                                     v-if="orderDetail.form_data && orderDetail.same_form !== true && orderDetail.goods_type === 'goods'">
                                    <h3>商品标题</h3>
                                    <div v-for="(subOrderDetail, subOrderDetailIndex) in order.detail"
                                         :key="orderDetail.form_id + '_' + subOrderDetailIndex"
                                         v-if="orderDetail.form_id == subOrderDetail.form_id"
                                         flex="cross:center box:first" style="margin-bottom: 10px">
                                        <el-image :src="
                                        subOrderDetail.goods_info && subOrderDetail.goods_info.goods_attr && subOrderDetail.goods_info.goods_attr.pic_url ?
                                        subOrderDetail.goods_info.goods_attr.pic_url : subOrderDetail.goods.cover_pic"
                                                  style="width: 50px;height: 50px; margin-right: 10px;"></el-image>
                                        <div style="color: #999999;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
                                            {{subOrderDetail.goods.goodsWarehouse.name}}
                                        </div>
                                    </div>
                                    <h3>表单信息</h3>
                                    <!-- 排除身份证 -->
                                    <div v-if="item.value && [`,`,``].indexOf(item.value.toString()) === -1"
                                         v-for="(item, index) in orderDetail.form_data" :key="index"
                                         class="item-box" flex="dir:left cross:center">
                                        <span class="label">{{item.label}}:</span>
                                        <div v-if="Array.isArray(item.value) && item.key == `img_upload`"
                                             flex="dir:left">
                                            <div v-for="(img,key) in item.value" :key="key">
                                                <el-image
                                                        style="width: 80px; height: 80px"
                                                        v-if="img"
                                                        :src="img"
                                                        :preview-src-list="[img]">
                                                </el-image>
                                            </div>
                                        </div>
                                        <div v-else-if="item.key==`img_upload`">
                                            <el-image
                                                    style="width: 80px; height: 80px"
                                                    v-if="item.value"
                                                    :src="item.value"
                                                    :preview-src-list="[item.value]">
                                            </el-image>
                                        </div>
                                        <span v-else>{{item.value}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div v-if="isShowShare" flex="dir:top" class="card-box">
                            <h3>分销信息</h3>
                            <div v-if="order.shareOrder" v-for="(shareItem, index) in order.shareOrder" :key="index">
                                <template v-if="shareItem.first_parent">
                                    <div class="share-title">
                                        {{shareItem.is_zigou ? '自购返利' : '一级分销商'}}
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">姓名:</span>
                                        <div>{{shareItem.first_parent.nickname}}</div>
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">手机号:</span>
                                        <div>{{shareItem.first_parent.mobile}}</div>
                                    </div>
                                    <div class="item-box share-price" flex="dir:left cross:center">
                                        <span class="label orange-label">佣金:</span>
                                        <div>￥{{shareItem.first_price}}</div>
                                    </div>
                                </template>
                                <template v-if="shareItem.second_parent">
                                    <div class="share-title">
                                        {{shareItem.is_zigou ? '一级分销商' : '二级分销商'}}
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">姓名:</span>
                                        <div>{{shareItem.second_parent.nickname}}</div>
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">手机号:</span>
                                        <div>{{shareItem.second_parent.mobile}}</div>
                                    </div>
                                    <div class="item-box share-price" flex="dir:left cross:center">
                                        <span class="label orange-label">佣金:</span>
                                        <div>￥{{shareItem.second_price}}</div>
                                    </div>
                                </template>
                                <template v-if="shareItem.third_parent">
                                    <div class="share-title">
                                        {{shareItem.is_zigou ? '二级分销商' : '三级分销商'}}
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">姓名:</span>
                                        <div>{{shareItem.third_parent.nickname}}</div>
                                    </div>
                                    <div class="item-box" flex="dir:left cross:center">
                                        <span class="label">手机号:</span>
                                        <div>{{shareItem.third_parent.mobile}}</div>
                                    </div>
                                    <div class="item-box share-price" flex="dir:left cross:center">
                                        <span class="label orange-label">佣金:</span>
                                        <div>￥{{shareItem.third_price}}</div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <slot name="shareInfo"></slot>
                    </el-col>
                </el-row>
                <slot :order="order"></slot>
                <el-card shadow="never" style="margin-top: 15px;">
                    <el-table stripe border :span-method="objectSpanMethod" :data="!isNewRequest ? goods_list : order.detail" style="width: 100%;margin-bottom: 15px;">
                        <el-table-column v-if="order.sign == 'composition'" align="center" prop="composition_type" label="套餐类型" width="200">
                            <template slot-scope="scope">
                                {{scope.row.composition_type == 1 ? '固定套餐' : '搭配套餐'}}
                            </template>
                        </el-table-column>
                        <el-table-column prop="goods" label="商品标题">
                            <template slot-scope="scope">
                                <div flex="dir:left cross:center">
                                    <img v-if="order.sign == 'composition'" :src="scope.row.pic_url" alt="" style="height: 60px;width: 60px;margin-right: 5px">
                                    <img v-else :src="scope.row.goods_info && scope.row.goods_info.goods_attr && scope.row.goods_info.goods_attr.pic_url ?
                                     scope.row.goods_info.goods_attr.pic_url : scope.row.goods.cover_pic" alt=""
                                         style="height: 60px;width: 60px;margin-right: 5px">
                                    <app-ellipsis v-if="order.sign == 'composition'" :line="1">{{scope.row.name}}</app-ellipsis>
                                    <app-ellipsis v-else :line="1">{{scope.row.goods_info && scope.row.goods_info.goods_attr &&
                                        scope.row.goods_info.goods_attr.name ?
                                        scope.row.goods_info.goods_attr.name : scope.row.goods.goodsWarehouse.name}}
                                    </app-ellipsis>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="规格" width="220">
                            <template slot-scope="scope">
                                <el-tag size="mini" style="margin-right: 5px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;max-width: 95%;" v-for="attr in scope.row.attr_list"
                                        :key="attr.id">{{attr.attr_group_name}}:{{attr.attr_name}}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column v-if="order.plugin_data && order.plugin_data.price_list && order.plugin_data.exchange_list" align="center" :label="order.plugin_data.exchange_list[0].label + '/' + order.plugin_data.price_list[0].label" width="120">
                            <template slot-scope="scope">
                                {{order.plugin_data.exchange_list[scope.$index].value}}/￥{{order.plugin_data.price_list[scope.$index].value}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="售价" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.unit_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="num" label="数量" width="80"></el-table-column>
                        <el-table-column align="center" label="商品小计" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.total_original_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="优惠后" width="120">
                            <template slot-scope="scope">
                             <span v-if="order.plugin_data && order.plugin_data.exchange_list && order.plugin_data.exchange_list.length">{{order.plugin_data.exchange_list[scope.$index].value}}/</span>￥{{scope.row.total_price}}
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-form label-width="200px" :model="order" class="app-order-count-price">
                        <el-form-item label="订单售价">
                            <span v-if="order.plugin_data && order.plugin_data && order.plugin_data.exchange_count">{{order.plugin_data.exchange_count}}{{order.plugin_data.price_name}}/</span>
                            <span>￥{{ order.total_goods_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="运费" v-if="order.is_show_express === 1">
                            <span>￥{{ order.express_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="会员折扣" v-if="order.member_discount_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.member_discount_price }}</span>
                        </el-form-item>
                        <el-form-item label="积分抵扣" v-if="order.integral_deduction_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.integral_deduction_price }}</span>
                        </el-form-item>
                        <el-form-item label="优惠券抵扣" v-if="order.coupon_discount_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.coupon_discount_price }}</span>
                        </el-form-item>
                        <!--插件特殊优惠-->
                        <template v-if="order && order.plugin_data && order.plugin_data.discount_list" v-for="pluginData in order.plugin_data.discount_list">
                            <el-form-item :label="pluginData.label">
                                <span style="color:#ff4544;">-￥{{pluginData.value}}</span>
                            </el-form-item>
                        </template>
                        <el-form-item label="满减优惠" v-if="order.full_reduce_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.full_reduce_price }}</span>
                        </el-form-item>
                        <el-form-item label="商品加价"
                                      v-if="(order.total_goods_original_price - order.total_goods_price) < 0">
                            <span style="color:#ff4544;">￥{{ (order.total_goods_price - order.total_goods_original_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="运费减免" v-if="(order.express_original_price - order.express_price) > 0">
                            <span style="color:#ff4544;">-￥{{ (order.express_original_price - order.express_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="运费增加" v-if="(order.express_original_price - order.express_price) < 0">
                            <span style="color:#ff4544;">￥{{ (order.express_price - order.express_original_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="订单改价" v-if="order.back_price != 0.00">
                            <span v-if="order.back_price > 0.00" style="color:#ff4544;">-￥{{ order.back_price }}</span>
                            <span v-if="order.back_price < 0.00" style="color:#ff4544;">￥{{ -order.back_price }}</span>
                        </el-form-item>
                        <el-form-item label="实付款">
                            <span style="color:#ff4544;" v-if="order.plugin_data && order.plugin_data && order.plugin_data.exchange_count">{{order.plugin_data.exchange_count}}{{order.plugin_data.price_name}}/</span>
                            <span style="color:#ff4544;">￥{{ order.total_pay_price }}</span>
                        </el-form-item>
                    </el-form>
                </el-card>
                <div class="action-box" flex="dir:right">
                    <div>
                        <!-- 结束 -->
                        <el-button
                                :loading="btnLoading"
                                v-if="order.is_recycle == 0 && order.is_confirm == 1 && order.is_sale == 0 && isShowFinish && order.status != 0"
                                size="small" type="primary" @click="saleOrder(order.id)">结束订单
                        </el-button>
                        <!-- 确认收货 -->
                        <el-button
                                :loading="btnLoading"
                                v-if="order.is_recycle == 0 && order.is_send == 1 && order.is_confirm == 0 && isShowConfirm && order.status != 0 && order.is_confirm_show == 1"
                                size="small" type="primary" @click="confirm(order.id)">确认收货
                        </el-button>
                        <el-button v-if="order.expressSingle" size="small" type="primary"
                                   @click="expressSingle(order.expressSingle.print_teplate)">电子面单
                        </el-button>
                        <!-- 核销 -->
                        <el-button
                                v-if="order.action_status.is_clerk_order && isShowClerk"
                                size="small" type="primary" @click="openDialog(order, clerkVisible = true)">核销订单
                        </el-button>
                        <!-- 发货 -->
                        <template>
                            <!-- 正常发货 -->
                            <el-button
                                    v-if="order.action_status.is_express_send  && isShowSend"
                                    size="small" type="primary" @click="openExpress(order,'send')">发货
                            </el-button>
                            <el-button
                                    v-if="order.action_status.is_city_send  && isShowSend"
                                    size="small" type="primary" @click="openExpress(order,'send')">发货
                            </el-button>
                            <!-- 到店自提发货 -->
                            <el-button
                                    v-if="order.action_status.is_store_send == 1 && isShowSend"
                                    size="small" @click="storeOrderSend(order)" type="primary">发货
                            </el-button>
                        </template>
                        <!-- 打印小票 -->
                        <el-button :loading="btnLoading"
                                   v-if="order.is_recycle == 0 && order.status != 0 && isShowPrint" size="small"
                                   type="primary" @click="hasPrintStatus = true">打印小票
                        </el-button>
                        <!-- 打印发货单 -->
                        <el-button :loading="btnLoading"
                                   v-if="order.action_status && order.action_status.is_print_send_template == 1" size="small"
                                   type="primary" @click="print_template(order)">打印发货单
                        </el-button>
                    </div>
                </div>
            </div>
        </el-card>
        <div id="print" v-show="false">
            <div v-for="(item) in printData" :style="{padding: `0 ${mmConversionPx(printPar.left_right_margins) + 'px'}`, width: `${mmConversionPx(Number(printPar.left_right_margins) + Number(printPar.left_right_margins) + Number(printPar.stencil_width) + Number(printPar.border_width)+ Number(printPar.border_width)) + 'px'}`, marginBottom: '20px'}">
                <div id="invoice" :style="{width: mmConversionPx(printPar.stencil_width) + 'px',minHeight:mmConversionPx(printPar.stencil_high) + 'px', marginLeft: printPar.offset.left + 'px', marginRight: printPar.offset.right + 'px', cursor: 'pointer', border: `${mmConversionPx(printPar.border_width)}px solid #000000`, boxSizing: 'content-box', margin: 0}">
                    <div style="display: inline-block;position: relative;white-space: nowrap;width: 100%;height: 50px">
                        <div :style="{
                                textAlign: printPar.headline.align === 0 ? 'center' : printPar.headline.align === 1 ? 'left' : 'right',
                                fontFamily: printPar.headline.fimaly,
                                 textDecoration: printPar.headline.underline ? 'underline' : 'none',
                                fontWeight: printPar.headline.bold ? 'bold' : 'normal',
                                fontStyle: printPar.headline.italic ? 'italic' : 'normal',
                                fontSize: printPar.headline.font / (4/3) + 'px',height: '50px',
                                width: '62%',
                                boxSizing: 'border-box',
                                display: 'inline-block',
                                position: 'absolute',
                                top: 0,
                                lineHeight: '50px', letterSpacing: printPar.headline.space / (4/3)+'px', borderBottom: `${!printPar.order.date && !printPar.order.time && !printPar.order.orderNumber ? '1px solid #000000' : 'none'}`}"
                             class="title"
                        >{{printPar.headline.name}}</div>
                        <div style="width: 38%;height: 50px;border-left: 1px solid #000000;padding: 8px 0;text-align: center; box-sizing: border-box;display: inline-block;position: absolute;right: 0;">
                            <img :id="'code_' + item.order_no" style="height: 34px;"/>
                        </div>
                    </div>
                    <div
                            v-if="printPar.order.date || printPar.order.time || printPar.order.orderNumber"
                            :style="{display: 'flex',flexWrap:'wrap',borderTop: '1px solid #000000',borderBottom: '1px solid #000000',padding:'10px 10px 10px 0.5%' , boxSizing: 'border-box'}"
                    >
                        <div style="width: 50%;font-size:10px;line-height:1;margin-bottom: 6px" v-if="printPar.order.date">打印日期：{{printTime}}</div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.time">订单时间：{{item.pay_time}}</div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.orderNumber">订单号：{{item.order_no}}</div>
                    </div>
                    <div :style="{display: 'flex', boxSizing: 'border-box'}"
                         v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address || printPar.personalInf.leaveComments || printPar.personalInf.payMethod || printPar.personalInf.shipMethod">
                        <div v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod"
                             :style="{boxSizing: 'border-box',width: `${printPar.personalInf.leaveComments ? '62%' : '100%'}`,borderBottom:'1px solid #000000',borderRight: `${ printPar.personalInf.leaveComments ? '1px solid #000000': 'none'}`, padding:'10px 10px 10px 0.5%'}"
                        >
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.name">收货人信息：{{item.name}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.nickname">收货人昵称：{{item.nickname}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.phone">联系方式：{{item.mobile}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.payMethod">支付方式：{{item.pay_type == 1 ? '在线支付' : item.pay_type == 2 ? '货到付款' : item.pay_type == 3 ? '余额支付' : ''}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.shipMethod && item.send_type != 1">发货方式：{{item.send_type == 0 ? '快递配送' : item.send_type == 1 ? '到店自提' :  item.send_type == 2 ? '同城配送' : ''}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.address && item.send_type != 1">收货地址：{{item.address}}</div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.mention_address && item.send_type == 1">自提门店地址：{{item.store_address}}</div>
                        </div>
                        <div :style="{boxSizing: 'border-box', width: `${printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod ? '38%' : '100%'}`,borderBottom:'1px solid #000000',padding: '10px 10px 10px 0.5%', fontSize:'10px', lineHeight:'1.2'}"
                             v-if="printPar.personalInf.leaveComments">
                            买家留言：{{item.remark}}
                        </div>
                    </div>
                    <div>
                        <div  style="box-sizing:border-box;width:100%;display: flex;border-bottom:1px solid #000000;position: relative;left: -0.5px;" v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">

                            <div style="box-sizing:border-box;width: 6%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;" v-if="printPar.goodsInf.serial">序号</div>
                           <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr">
                               <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;box-sizing:border-box;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                    v-if="printPar.goodsInf.name"
                               >商品名称</div>
                               <div :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                       style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;box-sizing:border-box;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;" v-if="printPar.goodsInf.attr">规格</div>
                           </div>
                            <div style="box-sizing:border-box;width: 8%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;" v-if="printPar.goodsInf.number">数量</div>
                            <div style="box-sizing:border-box;width: 12%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;" v-if="printPar.goodsInf.univalent">小计</div>
                            <div style="box-sizing:border-box;width: 18%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"  v-if="printPar.goodsInf.article_number">货号</div>

                            <div style="box-sizing:border-box;width: 8%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;" v-if="printPar.goodsInf.unit">单位</div>
                        </div>
                        <div v-for="good in item.detail" style="box-sizing:border-box;width: 100%;display: flex;border-bottom: 1px solid #000000;position: relative;left: -0.5px;" v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 6%;word-wrap: break-word;border-left: 1px solid #000000;padding:  10px 10px 10px 0.5%;font-size:10px;position: relative"  v-if="printPar.goodsInf.serial">
                                {{good.id}}
                            </div>
                           <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr">
                               <div
                                       :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                       style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap:break-word;box-sizing:border-box;word-wrap: break-word;border-left: 1px solid #000000;padding:  10px 10px 10px 0.5%;font-size:10px;position: relative"  v-if="printPar.goodsInf.name">
                                   {{good.name}}
                               </div>
                               <div
                                       :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                       style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap:break-word;box-sizing:border-box;word-wrap: break-word;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative" v-if="printPar.goodsInf.attr">
                                   {{good.attr}}
                               </div>
                           </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 8%;border-left: 1px solid #000000;;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative" v-if="printPar.goodsInf.number">
                                {{good.num}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 12%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative" v-if="printPar.goodsInf.univalent">
                                ￥{{good.price}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 18%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative" v-if="printPar.goodsInf.article_number">
                                {{good.goods_no}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 8%;border-left: 1px solid #000000;word-wrap: break-word;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative" v-if="printPar.goodsInf.unit">
                                {{good.unit}}
                            </div>
                        </div>

                        <div style="box-sizing:border-box;display: flex;height: 30px;padding-left: 0.5%;border-bottom:1px solid #000000;font-size: 10px" v-if="printPar.goodsInf.amount || printPar.goodsInf.totalAmount || printPar.goodsInf.fare || printPar.goodsInf.discount || printPar.goodsInf.actually_paid">
                            <div style="width: 24%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.amount">订单金额：￥{{item.total_goods_original_price}}</div>
                            <div style="width: 16%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.totalAmount">
                                总数量：{{item.goods_num}}
                            </div>
                            <div style="width: 21%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.fare">运费：￥{{item.express_price}}</div>
                            <div style="width: 20%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.discount">优惠：￥{{item.send_template_discount_price}}</div>
                            <div style="width: 19%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.actually_paid">实付：￥{{item.total_pay_price}}</div>
                        </div>
                    </div>
                    <div :style="{boxSizing: 'border-box', display:'flex',borderBottom:'1px solid #000000'}" v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address || printPar.sellerInf.remark">
                        <div :style="{boxSizing: 'border-box',width:`${!printPar.sellerInf.remark ? '100%': '62%'}`,padding: '10px 10px 10px 0.5%', fontSize: '10px',borderRight: `${!printPar.sellerInf.remark ? 'none' : '1px solid #000000'}`}" v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address">
                            <div v-if="printPar.sellerInf.branch">网点名称：{{address_list[0].name}}</div>
                            <div v-if="printPar.sellerInf.name">联系人：{{address_list[0].username}}</div>
                            <div v-if="printPar.sellerInf.phone">联系方式：{{address_list[0].mobile}}</div>
                            <div v-if="printPar.sellerInf.postcode">网点邮编：{{address_list[0].code}}</div>
                            <div v-if="printPar.sellerInf.address">网点地址：{{address_list[0].province}}{{address_list[0].city}}{{address_list[0].district}}{{address_list[0].address}}</div>
                        </div>
                        <div :style="{boxSizing: 'border-box',width: `${!printPar.sellerInf.branch && !printPar.sellerInf.name && !printPar.sellerInf.phone && !printPar.sellerInf.postcode && !printPar.sellerInf.address ? '100%' : '38%'}`,padding: '10px 10px 10px .5%', fontSize: '10px'}"  v-if="printPar.sellerInf.remark">
                            商家备注：{{item.seller_remark ? item.seller_remark : item.words}}
                        </div>
                    </div>
                    <div flex="" :style="{boxSizing: 'border-box',padding: '10px 10px 10px 0.5%', fontSize: '10px'}">
                        <div style="width: 100%;" flex="">
                            <div v-html="printPar.customize" style="width: 100%;word-wrap:break-word;">{{printPar.customize}}</div>
                        </div>
                        <div v-html="printPar.customize_image" style="width: 100%;margin-top: 10px;word-wrap:break-word;">{{printPar.customize_image}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="<?=Yii::$app->request->baseUrl?>/statics/js/JsBarcode.all.min.js"></script>
<script>
    Vue.component('app-order-detail', {
        template: '#app-order-detail',
        props: {
            getDetailUrl: {
                type: String,
                default: 'mall/order/detail'
            },
            getOrderListUrl: {
                type: String,
                default: 'mall/order/index'
            },
            // 控制按钮是否显示
            // 编辑收货地址
            isShowEditAddress: {
                type: Boolean,
                default: true
            },
            // 编辑订单备注
            isShowRemark: {
                type: Boolean,
                default: true
            },
            // 结束订单
            isShowFinish: {
                type: Boolean,
                default: true
            },
            // 确认收货
            isShowConfirm: {
                type: Boolean,
                default: true
            },
            // 小票打印
            isShowPrint: {
                type: Boolean,
                default: true
            },
            // 订单核销
            isShowClerk: {
                type: Boolean,
                default: true
            },
            // 订单发货
            isShowSend: {
                type: Boolean,
                default: true
            },
            // 订单状态进度
            isShowSteps: {
                type: Boolean,
                default: true
            },
            // 订单状态进度
            isShowShare: {
                type: Boolean,
                default: true
            },
            // 订单数据可从父组件传入 start
            // 组件内部不请求数据
            isNewRequest: {
                type: Boolean,
                default: false
            },
            // 父组件订单数据
            orderData: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            titleLabel: {
                type: String,
                default: '订单列表'
            },
            // 订单数据可从父组件传入 end
            // 订单状态
            newActive: {
                type: Number,
                default: 2,
            },
        },
        data() {
            return {
                loading: false,
                newOrder: {},// 传给各子组件的订单信息
                addressVisible: false,// 修改收货地址
                sellerRemarkVisible: false,// 添加商户备注
                clerkVisible: false,// 订单核销
                sendVisible: false,// 发货
                sendType: '',// 发货类型
                expressId: 0,// 编辑发货,物流ID
                cancelVisible: false,// 订单取消
                cancelType: -1,// 订单取消状态 同意|拒绝
                changePriceVisible: false,// 修改订单价格
                active: 1,
                order: {
                    user: {},
                    detailExpress: [],
                    action_status: {
                        is_print_send_template: 0
                    },
                    type_data: {
                        ecard: []
                    },
                    plugin_data: {},
                },
                btnLoading: false,
                citySendVisible: false,//选择配送员
                spanArr: [],
                goods_list: [],
                pos: 0,
                invoiceVisible: false,
                printTime: '',
                printData: [
                ],
                printPar: {
                    order: {
                        orderNumber: true,
                        time: true,
                        date: true,
                    },
                    personalInf: {
                        name: true,
                        shipMethod: true,
                        nickname: true,
                        payMethod: true,
                        mention_address: true,
                        phone: true,
                        address: true,
                        leaveComments: true,
                    },
                    goodsInf: {
                        serial: true,
                        name: true,
                        attr: true,
                        number: true,
                        unit: true,
                        univalent: true,
                        article_number: true,
                        amount: true,
                        totalAmount: true,
                        fare: true,
                        discount: true,
                        actually_paid: true,
                    },
                    sellerInf: {
                        branch: true,
                        name: true,
                        phone: true,
                        postcode: true,
                        address: true,
                        remark: true,
                    },
                    headline: {
                        name: '发货单',
                        fimaly: "微软雅黑",
                        font: 16,
                        align: 0,
                        line: 48,
                        space: -100,
                    },
                    offset: {
                        left: 0,
                        right: 0,
                    },
                    stencil_width: 204,
                    stencil_high: 142,
                    left_right_margins: 0,
                    border_width: 1,
                    customize_image: ''
                },
                address_list: [],

                hasPrintStatus: false,
                ecard: [],
                tableWidth: 'width:48%'
            };
        },
        watch: {
            orderData: function (newVal) {
                this.order = newVal;
            },
            newActive: function (newVal) {
                this.active = newVal;
            }
        },
        created() {
            // 数据从父组件传入
            if (!this.isNewRequest) {
                this.getDetail();
            }
        },
        methods: {
            //获取列表
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: this.getDetailUrl,
                        order_id: getQuery('order_id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.order = e.data.data.order;
                        //
                        // // 获取电子卡密数据
                        if (this.order.goods_type === 'ecard' && this.order.type_data && this.order.type_data.ecard.length > 0) {
                            this.ecard = [this.order.type_data.ecard[0]];
                        }

                        this.goods_list = [];
                        if(this.order.sign === 'composition') {
                            for(let i in this.order.composition_list) {
                                for(let j in this.order.composition_list[i].goods_list) {
                                    this.order.composition_list[i].goods_list[j].composition_id = this.order.composition_list[i].id;
                                    this.order.composition_list[i].goods_list[j].composition_type = this.order.composition_list[i].type;
                                    this.goods_list.push(this.order.composition_list[i].goods_list[j]);
                                }
                            }
                            this.getSpanArr(this.goods_list)
                        }else {
                            // this.goods_list.forEach(function(item, index) {
                            //     this.goods_list[index].plugin_data = this.order.plugin_data;
                            // })
                            this.goods_list = this.order.detail;
                        }
                        this.$emit('get-detail', this.order);
                        if (this.order.cancel_status == 1) {
                            this.active = 5;
                        }
                        if (this.order.is_pay == 1) {
                            this.active = 2;
                        }
                        if (this.order.is_send == 1) {
                            this.active = 3;
                        }
                        if (this.order.is_confirm == 1) {
                            this.active = 4;
                        }
                        if (this.order.is_sale == 1) {
                            this.active = 5;
                        }
                    }
                }).catch(e => {
                });
            },
            getSpanArr(data) {
                for (var i = 0; i < data.length; i++) {
                    if (i === 0) {
                        this.spanArr.push(1);
                        this.pos = 0
                    } else {
                      // 判断当前元素与上一个元素是否相同
                        if (data[i].composition_id === data[i - 1].composition_id) {
                            this.spanArr[this.pos] += 1;
                            this.spanArr.push(0);
                        } else {
                            this.spanArr.push(1);
                            this.pos = i;
                        }
                    }
                }
            },
            objectSpanMethod({ row, column, rowIndex, columnIndex }) {
                if(this.order.sign != 'composition') {
                    return false
                }
                if (columnIndex === 0) {
                    const _row = this.spanArr[rowIndex];
                    const _col = _row > 0 ? 1 : 0;
                    return {
                        rowspan: _row,
                        colspan: _col
                    }
                }
            },
            // 新的
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.addressVisible = false;
                this.sellerRemarkVisible = false;
                this.clerkVisible = false;
                this.sendVisible = false;
                this.changePriceVisible = false;
                this.citySendVisible = false;
            },
            dialogSubmit() {
                this.expressId = 0;
                this.getDetail()
            },
            // 发货
            openExpress(order, type, expressId = 0) {
                this.newOrder = order;
                this.sendType = type;
                this.sendVisible = true;
                this.expressId = parseInt(expressId);
            },
            // 确认收货
            confirm(id) {
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/confirm',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        message: e.data.msg,
                        type: 'error'
                    });
                });
            },
            // 结束订单
            saleOrder(id) {
                this.$confirm('是否结束该订单?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/order-sales',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        message: e.data.msg,
                        type: 'error'
                    });
                });
            },
            // 打印小票
            print(id) {
                this.$confirm('是否打印小票?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/order-print',
                            order_id: id
                        },
                        method: 'get',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        }
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            expressSingle(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
            openCity(order, sendType) {
                this.newOrder = order;
                this.sendType = sendType;
                this.citySendVisible = true;
            },
            toList() {
                this.$navigate({
                    r: this.getOrderListUrl,
                })
            },
            storeOrderSend(order) {
                this.$alert('是否将配送方式改为快递配送?', '提示', {
                    confirmButtonText: '确定',
                    showCancelButton: true,
                    type: 'warning',
                    callback: action => {
                        if (action == 'confirm') {
                            this.openDialog(order);
                            this.addressVisible = true;
                        }
                    }
                });
            },
            printTeplate(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
            print_template(order) {
                this.invoiceVisible = true;
                this.newOrder = order;
            },
            mmConversionPx(value) {
                let inch = value*2.834;
                return inch;
            },
            select_template(e, select_order, order) {
                request({
                    params: {
                        r: `/mall/order-send-template/address`
                    }
                }).then(res => {
                    if (!res.data.data.detail) {
                        this.$message({
                            message: '请先添加发货地址',
                            type: 'warning'
                        });
                        return;
                    }
                    this.address_list = [res.data.data.detail];
                    this.printData = [];
                    let { detail } = order;
                    let new_detailExpress = JSON.parse(JSON.stringify(order.detailExpress));
                    let new_select_order = [];
                    for (let i = 0; i < detail.length; i++) {
                        if (select_order.indexOf(detail[i]) === -1) {
                            new_select_order.push(detail[i]);
                        }
                    }
                    let order_list = JSON.parse(JSON.stringify(detail));
                    for (let i = 0; i < detail.length; i++) {
                        for (let j = 0; j < new_detailExpress.length; j++) {
                            for (let k = 0; k < new_detailExpress[j].expressRelation.length; k++) {
                                if (new_detailExpress[j].expressRelation[k].order_detail_id === detail[i].id) {
                                    for (let m = 0; m < order_list.length; m ++) {
                                        if (order_list[m].id === new_detailExpress[j].expressRelation[k].order_detail_id) {
                                            this.$set(new_detailExpress[j].expressRelation[k], 'num', order_list[m].num);
                                            this.$set(new_detailExpress[j].expressRelation[k], 'goods', order_list[m].goods);
                                            this.$set(new_detailExpress[j].expressRelation[k], 'total_price', order_list[m].total_price);
                                            order_list.splice(m, 1);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    for (let m = 0; m < new_select_order.length; m++) {
                        for (let i = 0; i < new_detailExpress.length; i++) {
                            for (let k = 0; k < new_detailExpress[i].expressRelation.length; k++) {
                                if (new_select_order[m].id === new_detailExpress[i].expressRelation[k].order_detail_id) {
                                    new_detailExpress[i].expressRelation.splice(k, 1);
                                }
                            }
                        }
                    }
                    for (let m = 0; m < new_select_order.length; m++) {
                        for (let i = 0; i < order_list.length; i++) {
                            if (new_select_order[m].id === order_list[i].id) {
                                order_list.splice(i, 1);
                            }
                        }
                    }
                    let date = new Date();
                    let Y = date.getFullYear() + '年';
                    let M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '月';
                    let D = date.getDate() + '日';
                    this.printPar = e.params;
                    this.printTime = Y+M+D;
                    let discount_price = Number(order.member_discount_price) + Number( order.coupon_discount_price);
                    for (let i = 0; i < new_detailExpress.length; i++) {
                        let detail = [];
                        if (new_detailExpress[i].expressRelation.length === 0) break;
                        for (let j = 0; j < new_detailExpress[i].expressRelation.length; j++) {
                            detail.push({
                                name: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.name,
                                num: new_detailExpress[i].expressRelation[j].num,
                                unit: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.unit,
                                price: new_detailExpress[i].expressRelation[j].total_price,
                                id: i+1,
                                goods_no: new_detailExpress[i].expressRelation[j].orderDetail.goods_no,
                                attr: this.getGoodsAttr(new_detailExpress[i].expressRelation[j].orderDetail.goods_info.attr_list),
                            });
                        }
                        let data = {
                            order_no: order.order_no,
                            pay_time: order.pay_time,
                            name: order.name,
                            nickname: order.nickname,
                            mobile: order.mobile,
                            address: order.address,
                            remark: order.remark,
                            seller_remark: order.seller_remark,
                            words: order.words,
                            total_goods_price: order.total_goods_price,
                            total_goods_original_price: order.total_goods_original_price,
                            total_pay_price: order.total_pay_price,
                            express_price: order.express_price,
                            send_type: order.send_type,
                            pay_type: order.pay_type,
                            discount_price: discount_price,
                            goods_num: order.goods_num,
                            detail: detail,
                            send_template_discount_price: order.send_template_discount_price
                        };
                        if (order.send_type == 1) {
                            data.store_address = order.store.address;
                        }
                        this.printData.push(data);
                    }
                    let order_detail = [];
                    for (let i = 0; i < order_list.length; i++) {
                        order_detail.push({
                            name: order_list[i].goods.goodsWarehouse.name,
                            num: order_list[i].num,
                            unit: order_list[i].goods.goodsWarehouse.unit,
                            price: order_list[i].total_price,
                            id: i+1,
                            goods_no: order_list[i].goods_no,
                            attr: this.getGoodsAttr(order_list[i].goods_info.attr_list),
                        });
                    }
                    if (order_detail.length > 0) {
                        let data = {
                            order_no: order.order_no,
                            pay_time: order.pay_time,
                            name: order.name,
                            nickname: order.nickname,
                            mobile: order.mobile,
                            address: order.address,
                            remark: order.remark,
                            seller_remark: order.seller_remark,
                            words: order.words,
                            total_goods_price: order.total_goods_price,
                            total_goods_original_price: order.total_goods_original_price,
                            total_pay_price: order.total_pay_price,
                            express_price: order.express_price,
                            send_type: order.send_type,
                            pay_type: order.pay_type,
                            discount_price: discount_price,
                            goods_num: order.goods_num,
                            detail:order_detail,
                            send_template_discount_price: order.send_template_discount_price
                        };
                        if (order.send_type == 1) {
                            data.store_address = order.store.address;
                        }
                        this.printData.push(data);
                    }
                    document.getElementById('print').style.display = 'block';
                    this.nameWidth(this.printPar);
                    setTimeout(() => {
                        for (let i = 0; i < this.printData.length; i++) {
                            JsBarcode('#code_' + this.printData[i].order_no, this.printData[i].order_no, {
                                format: "CODE39",//选择要使用的条形码类型
                                width:3.5,//设置条之间的宽度
                                height:200,//高度
                                displayValue:false,//是否在条形码下方显示文字
                                background:"#ffffff",//设置条形码的背景
                                lineColor:"#000000",//设置条和文本的颜色
                            });
                        }
                        setTimeout(() => {
                            let newWindow= window.open("打印窗口","_blank");//打印窗口要换成页面的url
                            let docStr = document.getElementById('print').outerHTML;
                            newWindow.document.write(docStr);
                            newWindow.document.close();
                            newWindow.print();
                            newWindow.close();
                            document.getElementById('print').style.display = 'none';
                        }, 1000);
                    });
                })
            },

            ecardExpand() {
                if (this.ecard.length < 2) {
                    this.ecard = this.order.type_data.ecard;
                } else {
                    this.ecard = [this.order.type_data.ecard[0]];
                }
            },
            getGoodsAttr(attrList) {
                let attr = '';
                attrList.forEach(item => {
                    attr += item.attr_group_name + ':' + item.attr_name + ';'
                })
                return attr;
            },
            nameWidth(data) {
                let per = 48;
                let { serial, number, univalent, article_number, unit } = data.goodsInf;
                if (!serial) {
                    per += 6;
                }
                if (!number) {
                    per += 8;
                }
                if (!univalent) {
                    per += 12;
                }
                if (!article_number) {
                    per += 18;
                }
                if (!unit) {
                    per += 13;
                }
                this.tableWidth = `width: ${per}%`;
            }
        }
    })
</script>
