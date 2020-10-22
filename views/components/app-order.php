<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/14 13:49
 */
Yii::$app->loadViewComponent('order/app-search');
Yii::$app->loadViewComponent('order/app-edit-address');
Yii::$app->loadViewComponent('order/app-edit-seller-remark');
Yii::$app->loadViewComponent('order/app-clerk');
Yii::$app->loadViewComponent('order/app-send');
Yii::$app->loadViewComponent('order/app-invoice');
Yii::$app->loadViewComponent('order/app-cancel');
Yii::$app->loadViewComponent('order/app-edit-price');
Yii::$app->loadViewComponent('order/app-city');
Yii::$app->loadViewComponent('order/app-select-print');
Yii::$app->loadViewComponent('app-new-export-dialog');

?>
<style>
    .app-order-list .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .app-order-list .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .app-order-list .header-box .title {
        display: inline-block;
    }

    .app-order-list .addPrice {
        color: #5CB85C;
    }

    .app-order-list .app-order-item .el-button {
        padding: 0;
    }

    .app-order-list .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
    }

    .app-order-price-item .el-form-item:last-of-type {
        margin-bottom: 0 !important;
    }

    .app-order-list .important {
        color: #ff4544;
    }

    .app-order-list .app-order-list .goods-info {
        padding: 5px;
        font-size: 12px;
        color: #353535;
        text-align: left;
    }

    .app-order-list .app-order-list .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .app-order-list .el-date-editor .el-range-separator {
        width: auto;
    }

    .app-order-list .goods-image {
        height: 90px;
        width: 90px;
        margin-right: 15px;
        float: left;
    }

    .app-order-list .app-order-item {
        margin-top: 20px;
        min-width: 900px;
    }

    .app-order-list .app-order-item:hover {
        border: 1px solid #3399FF;
    }

    .app-order-list .app-order-item:hover .app-order-del {
        display: block;
    }

    .app-order-del {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #7C868D;
        font-size: 18px;
        padding: 0;
        display: none;
    }

    .app-order-list .app-order-item .el-card__header {
        padding: 0;
    }

    .app-order-list .app-order-head {
        padding: 20px;
        background-color: #F3F5F6;
        color: #303133;
        min-width: 750px;
        display: flex;
        position: relative;
    }

    .app-order-list .app-order-time {
        color: #909399;
    }

    .app-order-user {
        margin-left: 30px;
    }

    .app-order-user img {
        height: 20px;
        width: 20px;
        display: block;
        float: left;
        border-radius: 50%;
        margin-right: 10px;
    }

    .app-order-offline {
        margin-left: 30px;
        margin-top: -2px;
    }

    .app-order-offline .el-tag {
        margin-right: 5px;
    }

    .app-order-refund-status {
        position: absolute;
        bottom: 19px;
        left: 20px;
        height: 20px;
        width: 90px;
        z-index: 5;
        background-color: #FF7171;
        color: #fff;
        text-align: center;
    }

    .cancel {
        margin-left: 10px;
    }

    .app-order-list .app-order-item .cancel .el-button {
        padding: 5px;
    }

    .app-order-list .el-card__body {
        padding: 0;
    }

    .app-order-body {
        display: flex;
        flex-wrap: nowrap;
    }

    .app-order-list .goods-item {
        border-right: 1px solid #EBEEF5;
    }

    .app-order-list .goods-item .goods {
        position: relative;
        padding: 20px;
        min-height: 130px;
        border-top: 1px solid #EBEEF5;
    }

    .app-order-list .goods-item .goods:first-of-type {
        border-top: 0;
    }

    .app-order-list .goods-item .goods-info {
        width: 50%;
        margin-top: 5px;
    }

    .app-order-list .goods-item .goods-info .goods-name {
        margin-bottom: 5px;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .app-order-list .goods-item .goods .app-order-goods-price {
        height: 24px;
        margin-top: 10px;
        position: absolute;
        bottom: 20px;
        left: 125px;
    }

    .app-order-list .app-order-info {
        display: flex;
        align-items: center;
        width: 15%;
        text-align: center;
        border-right: 1px solid #EBEEF5;
    }

    .app-order-list .app-order-info > div {
        width: 100%;
    }

    .app-order-list .express-price {
        height: 30px;
        line-height: 30px;
    }

    .app-order-title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
        min-width: 900px;
    }

    .input-1 .el-input-group__append, .el-input-group__prepend {
        padding: 0 20px;
    }

    .app-order-title div {
        text-align: center;
    }

    .app-order-icon {
        margin-right: 5%;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .app-order-icon:last-of-type {
        margin-right: 0;
    }

    .app-order-list .remark-box {
        padding-top: 3px;
        margin-left: 7px;
    }

    /*表格底部样式 start*/
    .app-order-list .card-footer {
        background: #F3F5F6;
        padding: 10px 20px;
    }

    .app-order-list .card-footer .address-box {
        margin-right: 10px;
    }

    .app-order-list .card-footer .seller-remark {
        margin-top: 10px;
        color: #E6A23C;
    }

    /*表格底部样式 end*/

    .app-order-list .express-send-box {
        position: relative;
        overflow: hidden;
        border-radius: 4px;
        height: 24px;
    }

    .app-order-list .express-send-box .triangle {
        width: 0;
        height: 0;
        border-right: 23px solid rgba(0, 0, 0, 0);
        border-top: 23px solid red;
        position: relative;
        top: -24px;
    }

    .app-order-list .express-send-box .triangle .text {
        font-size: 10px;
        color: #ffffff;
        position: absolute;
        top: -25px;
    }

    .express-single-box {
        margin-bottom: 10px;
    }

    .express-single-box .goods-pic {
        width: 35px;
        height: 35px;
        margin: 0 4px;
    }

    .express-single-box .label {
        margin-right: 10px;
    }
</style>
<template id="app-order-list" ref="appOrder">
    <div class="app-order-list" style="margin-bottom: 20px;">
        <div class="header-box" v-if="isShowHeader">
            <div class="title">
                <slot name="orderTitle">{{titleLabel}}</slot>
            </div>
            <app-new-export-dialog
                    v-if="isShowExport"
                    style="float: right;margin-top: -5px"
                    :field_list='export_list'
                    :action_url="orderUrl"
                    :params="search">
            </app-new-export-dialog>
            <el-button
                    v-if="isShowRecycle"
                    style="float: right; margin: -5px 20px"
                    :loading="submitLoading"
                    type="primary"
                    size="small"
                    @click="toRecycleAll">清空回收站
            </el-button>
        </div>
        <div class="table-body">
            <app-search
                    @search="toSearch"
                    @print="setPrintInvoice"
                    :plugins="plugins"
                    :tabs="tabs"
                    :is-show-platform="isShowPlatform"
                    :active-name="activeName"
                    :is-show-order-type="isShowOrderType"
                    :is-show-order-plugin="isShowOrderPlugin"
                    :new-search="newSearch"
                    :is-send-template="isSendTemplate"
                    :is-show-print-invoice="isShowPrintInvoice"
                    :is-goods-type="isGoodsType"
                    :is_show_ecard="hide_function.is_show_ecard"
                    :date-type-list="dateTypeList"
                    :select-list="selectList">
            </app-search>
            <app-edit-address
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="addressVisible"
                    :order="newOrder">
            </app-edit-address>
            <app-edit-seller-remark
                    :url="editRemarkUrl"
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
                    :express-id="expressId"
                    :send-type="sendType"
                    :order="newOrder">
            </app-send>
            <app-invoice
                    @close="dialogClose"
                    :is-show="printInvoice"
                    :order="newOrder"
                    :print-status="printStatus"
                    @select_template="select_template"
                    @select_template_all="select_template_all"
            ></app-invoice>
            <app-cancel
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="cancelVisible"
                    :cancel-type="cancelType"
                    :order="newOrder">
            </app-cancel>
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
            <app-select-print v-model="hasPrintStatus" :order-id="print_order_id"></app-select-print>
            <div class="app-order-title">
                <div v-if="isShowCheckBox" style="width: 5%;">
                    <el-checkbox :indeterminate="checkBoxSelect.isIndeterminate" v-model="checkBoxSelect.checkAll"
                                 @change="handleCheckAllChange" style="margin-left: 10%;position: relative;">
                        <span style="position: absolute;top: 11px;">全选</span>
                    </el-checkbox>
                </div>
                <div v-for="(item,index) in orderTitle" :key="index" :style="{width: item.width}">{{item.name}}</div>
            </div>

            <div v-loading="loading" v-if="list && list.length > 0">
                <el-card
                        v-for="item in list"
                        class="app-order-item"
                        :key="item.id"
                        shadow="never">
                    <div slot="header" class="app-order-head" flex="cross:center">
                        <div class="app-order-time">{{ item.created_at }}</div>
                        <div class="app-order-user">
                            <span class="app-order-time">订单号：</span>{{ item.order_no }}
                        </div>
                        <div class="app-order-user" flex="cross:center">
                            <img src="statics/img/mall/ali.png" v-if="item.platform == 'aliapp'" alt="">
                            <img src="statics/img/mall/wx.png" v-else-if="item.platform == 'wxapp'" alt="">
                            <img src="statics/img/mall/toutiao.png" v-else-if="item.platform == 'ttapp'" alt="">
                            <img src="statics/img/mall/baidu.png" v-else-if="item.platform == 'bdapp'" alt="">
                            <img src="statics/img/mall/platform/backstage.png" v-else-if="item.platform == ''" alt="">
                            <span>{{ item.nickname }}({{ item.user_id }})</span>
                        </div>
                        <div flex="cross:center" class="remark-box" v-if="item.remark || (item.merchant_remark_list && item.merchant_remark_list.length > 0)">
                            <el-tooltip effect="dark" placement="bottom">
                                <div slot="content">
                                    <span v-if="item.remark">买家留言:{{item.remark}}</span>
                                    <br v-if="item.remark"/>
                                    <span v-for="(merchantRemark, deIndex) in item.merchant_remark_list" :key="deIndex">
                                            商家留言{{item.merchant_remark_list.length > 1 ? deIndex + 1 : ''}}: {{merchantRemark}}
                                        <br v-if="merchantRemark"/>
                                    </span>
                                </div>
                                <div>
                                    <img src="statics/img/mall/order/remark.png" alt="">
                                </div>
                            </el-tooltip>
                        </div>
                        <div class="app-order-offline" flex="dir:left wrap:wrap">
                            <template v-if="item.cancel_status == 2 && item.status == 1 && isShowCancel">
                                <!-- 用户申请取消 -->
                                <div class="cancel" flex="wrap:wrap">
                                    <span style="margin-right: 5px;">用户正在申请取消该订单</span>
                                    <el-button type="success" size="mini" @click="agreeCancel(item,1)">同意</el-button>
                                    <el-button type="danger" size="mini" @click="agreeCancel(item,0)">拒绝</el-button>
                                </div>
                            </template>
                            <template v-else-if="isShowOrderStatus">
                                <div v-if="item.send_type == 0" class="express-send-box">
                                    <el-tag size="small">快递发送</el-tag>
                                    <div v-if="item.store_id > 0" class="triangle">
                                        <el-tooltip class="item" effect="dark" content="该订单由门店自提改成快递发送" placement="top">
                                            <span class="text">改</span>
                                        </el-tooltip>
                                    </div>
                                </div>
                                <el-tag size="small" v-if="item.send_type == 1">到店自提</el-tag>
                                <el-tag size="small" v-if="item.send_type == 2">同城配送</el-tag>
                                <el-tag size="small" v-if="item.send_type == 3">自动发货</el-tag>
                                <el-tag size="small" type="warning" v-if="item.is_pay == 0">未付款</el-tag>
                                <el-tag size="small" type="warning" v-if="item.is_pay == 1 && item.is_send == 0">已付款
                                </el-tag>
                                <el-tag size="small" type="success"
                                        v-if="item.is_send == 0 && item.is_pay == 1 && item.detailExpress && item.detailExpress.length > 0">
                                    部分发货
                                </el-tag>
                                <el-tag size="small" type="success"
                                        v-if="item.is_send == 0 && item.is_pay == 1 && item.detailExpress && item.detailExpress.length == 0">
                                    未发货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_send == 1 && item.is_confirm == 0">
                                    已发货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_confirm == 0 && item.is_send == 1">
                                    未收货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_sale == 1">已完成</el-tag>
                                <el-tag size="small" type="danger" v-if="item.cancel_status == 1">已取消</el-tag>
                                <el-tag size="small" type="danger" v-else-if="item.cancel_status == 2">申请取消</el-tag>
                            </template>
                            <slot name="orderTag" :order="item"></slot>
                        </div>

                        <el-button
                                v-if="isShowCancel && item.is_send == 0 && item.cancel_status == 0 && item.is_cancel_show == 1 && item.status == 1"
                                style="right: 60px"
                                class="app-order-del"
                                @click="agreeCancel(item,2)"
                                type="text">
                            <el-tooltip class="item" effect="dark" content="强制取消" placement="top">
                                <img src="statics/img/mall/order/force.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="isShowRecycle && item.is_recycle == 0"
                                   class="app-order-del"
                                   @click="toRecycle(item)"
                                   type="text">
                            <el-tooltip class="item" effect="dark" content="放入回收站" placement="top">
                                <img src="statics/img/mall/order/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </div>
                    <div class="app-order-body">
                        <!-- 全选 -->
                        <div v-if="isShowCheckBox" flex="cross:center main:center"
                             :style="{width: '5%',borderRight: '1px solid #e2e2e2'}">
                            <el-checkbox v-model="item.isChecked" @change="handleCheckedCitiesChange(item)">
                            </el-checkbox>
                        </div>
                        <!-- 订单信息 -->
                        <div class="goods-item" :style="{width: orderTitle[0].width}">
                            <div class="goods" v-for="(goods, iIndex) in item.detail">
                                <img :src="goods.goods_info && goods.goods_info.goods_attr && goods.goods_info.goods_attr.pic_url ? goods.goods_info.goods_attr.pic_url : goods.goods_info.goods_attr.cover_pic"
                                     class="goods-image">
                                <image style="width: 24px;height: 24px;position: absolute;left: 20px;"
                                       v-if="goods.goods.goodsWarehouse.type === 'goods' && isGoodsType && hide_function.is_show_ecard"
                                       src="statics/img/mall/goods.png"></image>
                                <image style="width: 24px;height: 24px;position: absolute;left: 20px;"
                                       v-if="['ecard', 'exchange', 'vip_card'].indexOf(goods.goods.goodsWarehouse.type) !== -1 && isGoodsType && hide_function.is_show_ecard"
                                       src="statics/img/mall/ecard-goods.png"></image>
                                <span v-if="goods.refund_status_text"
                                      class="app-order-refund-status">{{goods.refund_status_text}}</span>
                                <div flex="dir:left">
                                    <div class="goods-info">
                                        <div class="goods-name">
                                            <app-ellipsis :line="2">
                                                <el-tag style="margin-right: 5px"
                                                        v-if="goods.plugin_name != null"
                                                        size="mini"
                                                        type="warning" hit>
                                                    {{goods.mch && goods.mch.id > 0 ?
                                                    goods.mch.store.name+'('+goods.mch.id+')'
                                                    :goods.plugin_name}}
                                                </el-tag>
                                                {{goods.goods_info && goods.goods_info.goods_attr &&
                                                goods.goods_info.goods_attr.name ?
                                                goods.goods_info.goods_attr.name : goods.goods.goodsWarehouse.name}}
                                            </app-ellipsis>
                                        </div>
                                        <div style="margin-bottom: 24px;">
                                            <span style="margin-right: 10px;">
                                                <slot name="attr" :item="item">
                                                    规格：
                                                    <el-tag size="mini"
                                                            style="margin-right: 5px;max-width: 300px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis"
                                                            v-for="attr in goods.attr_list"
                                                            :key="attr.id">
                                                        {{attr.attr_group_name}}:{{attr.attr_name}}
                                                    </el-tag>
                                                </slot>
                                            </span>
                                        </div>
                                        <div class="app-order-goods-price">
                                            <span v-if="goods.goods_info && goods.goods_info.goods_attr &&
                                             goods.goods_info.goods_attr.no">
                                                货号：{{goods.goods_info.goods_attr.no}}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="width: 250px" flex="dir:left box:mean">
                                        <div flex="dir:top main:center">
                                            <div v-if="item.plugin_data && item.plugin_data.exchange_list &&  item.plugin_data.exchange_list.length">
                                                <span>{{item.plugin_data.exchange_list[iIndex].label}}：{{item.plugin_data.exchange_list[iIndex].value}}</span>
                                            </div>
                                            <div v-if="item.plugin_data && item.plugin_data.price_list &&  item.plugin_data.price_list.length">
                                                <span>{{item.plugin_data.price_list[iIndex].label}}：￥{{item.plugin_data.price_list[iIndex].value}}</span>
                                                <el-button type="text"
                                                           style="margin-left: 3px;"
                                                           v-if="isShowEditSinglePrice && item.is_pay == 0 && item.is_send == 0 && search.status != 5"
                                                           circle
                                                           @click="changeGoods(goods)">
                                                    <img src="statics/img/mall/order/edit.png" alt="">
                                                </el-button>
                                            </div>
                                        </div>
                                        <div flex="cross:center main:center">数量：x {{goods.num}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div flex="cross:center" class="app-order-info" :style="{width:orderTitle[1].width}">
                            <div flex="dir:top">
                                <div>
                                    <span style="font-size: 16px">￥{{item.total_pay_price}}</span>
                                    <el-popover
                                            placement="bottom"
                                            width="250"
                                            trigger="hover">
                                        <el-form class="app-order-price-item" label-width="100px" :model="item">
                                            <el-form-item label="商品小计">
                                                <span>{{ item.total_goods_original_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="会员优惠" v-if="item.member_discount_price != 0.00">
                                                <span class="important" v-if="item.member_discount_price > 0">
                                                    -{{ item.member_discount_price }}元
                                                </span>
                                                <span class="addPrice" v-if="item.member_discount_price < 0">
                                                    +{{ -item.member_discount_price }}元
                                                </span>
                                            </el-form-item>
                                            <el-form-item label="积分抵扣" v-if="item.integral_deduction_price != 0.00">
                                                <span class="important">-{{ item.integral_deduction_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="优惠券抵扣" v-if="item.coupon_discount_price != 0.00">
                                                <span class="important">-{{ item.coupon_discount_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="后台改价" v-if="item.back_price != 0.00">
                                                <span class="important" v-if="item.back_price > 0">-{{ item.back_price }}元</span>
                                                <span class="addPrice" v-else>+{{ -item.back_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="运费改价" v-if="item.express_price != 0">
                                                <span class="important"
                                                      v-if="+item.express_price > +item.express_original_price">
                                                    +{{(item.express_price - item.express_original_price).toFixed(2)}}元
                                                </span>
                                                <span class="addPrice" v-else>
                                                    {{(item.express_price - item.express_original_price).toFixed(2) }}元
                                                </span>
                                            </el-form-item>
                                            <template v-if="item.plugin_data && item.plugin_data.discount_list">
                                                <el-form-item v-for="pluginData in item.plugin_data.discount_list"
                                                              :label="pluginData.label" :key="pluginData.label">
                                                    <span class="important">{{pluginData.value}}元</span>
                                                </el-form-item>
                                            </template>
                                        </el-form>
                                        <img src="statics/img/mall/order/price.png" slot="reference" alt="">
                                    </el-popover>
                                    <slot name="other" :item="item"></slot>
                                </div>
                                <div>
                                    <span>
                                        <span style="color: #909399" v-if="item.is_show_express === 1">(含运费￥{{item.express_price}})</span>
                                        <el-button type="text"
                                                   v-if="isShowEditExpressPrice && item.is_pay == 0 && item.is_send == 0 && search.status != 5"
                                                   circle
                                                   @click="openDialog(item, changePriceVisible = true)">
                                            <img src="statics/img/mall/order/edit.png" alt="">
                                        </el-button>
                                    </span>
                                </div>
                                <div>
                                    <el-tag
                                            v-if="item.pay_type == 1"
                                            size="mini" color="#E6A23C"
                                            style="color:#fff;border:0"
                                            >在线支付
                                    </el-tag>
                                    <el-tag
                                            v-else-if="item.pay_type == 3"
                                            size="mini" color="#E6A23C"
                                            style="color:#fff;border:0"
                                            >余额支付
                                    </el-tag>
                                    <el-tag
                                            v-else-if="item.pay_type == 2"
                                            size="mini" color="#E6A23C"
                                            style="color:#fff;border:0"
                                            >货到付款
                                    </el-tag>
                                </div>
                                <span v-if="item.sign == 'community' && isShowProfit" style="color: #ff4544;margin-top: 5px;">(团长利润￥{{item.plugin_data.extra &&item.plugin_data.extra.profit_price ? item.plugin_data.extra.profit_price : '0.00'}})</span>
                            </div>
                        </div>
                        <div flex="cross:center" class="app-order-info" v-if="showMoreInfo" :style="{width:orderTitle[2].width}">
                            <slot name="orderInfo" :order="item"></slot>
                        </div>
                        <div v-if="isShowAction" class="app-order-info" :style="{width: showMoreInfo ? orderTitle[3].width : orderTitle[2].width}"
                             style="padding: 10px;border-right: 0;">
                            <div flex="wrap:wrap cross:center">
                                <!-- 结束 -->
                                <el-tooltip class="item" effect="dark" content="结束订单" placement="top">
                                    <img class="app-order-icon" @click="saleOrder(item.id)"
                                         v-if="item.is_recycle == 0 && item.is_confirm == 1 && item.is_sale == 0 && isShowFinish && item.status != 0"
                                         src="statics/img/mall/order/sale.png" alt="">
                                </el-tooltip>
                                <!-- 确认收货 -->
                                <el-tooltip class="item" effect="dark" content="确认收货" placement="top">
                                    <img class="app-order-icon" src="statics/img/mall/order/confirm.png" alt=""
                                         v-if="item.is_recycle == 0 && item.is_send == 1 && item.is_confirm == 0 && isShowConfirm && item.status != 0 && item.is_confirm_show"
                                         @click="confirm(item.id)">
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="电子面单" placement="top">
                                    <img class="app-order-icon" src="statics/img/mall/order/express_single.png" alt=""
                                         v-if="item.new_express_single && item.new_express_single.length > 0"
                                         @click="expressSingle(item.new_express_single)">
                                </el-tooltip>
                                <!-- 核销 -->
                                <el-tooltip class="item" effect="dark" :content="item.is_pay == 1 ? '核销订单' : '确认收款'"
                                            placement="top">
                                    <img class="app-order-icon" @click="openDialog(item, clerkVisible = true)"
                                         v-if="item.send_type == 1 && (item.is_pay == 1 || item.pay_type == 2) && item.clerk == null && item.is_send == 0 && item.is_clerk_show && item.is_recycle == 0 && isShowClerk && item.is_recycle == 0 && item.status != 0 && item.cancel_status != 1"
                                         src="statics/img/mall/order/clerk.png" alt="">
                                </el-tooltip>
                                <template>
                                    <slot name="orderSend" :order="item"></slot>
                                    <!-- 发货 -->
                                    <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                        <img class="app-order-icon" @click="openExpress(item,'send')"
                                             v-if="item.action_status.is_express_send && isShowSend"
                                             src="statics/img/mall/order/send.png" alt="">
                                    </el-tooltip>
                                    <!-- 同城配送发货 选择配送员 -->
                                    <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                        <img class="app-order-icon" @click="openExpress(item,'send')"
                                             v-if="item.action_status.is_city_send && isShowSend"
                                             src="statics/img/mall/order/send.png" alt="">
                                    </el-tooltip>
                                    <!-- 到店自提也可发货 -->
                                    <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                        <img class="app-order-icon" @click="storeOrderSend(item)"
                                             v-if="item.send_type == 1 && (item.is_pay == 1 || item.pay_type == 2) && item.is_send == 0 && item.cancel_status != 1 && item.is_send_show == 1 && isShowSend && item.is_recycle == 0 && item.status != 0"
                                             src="statics/img/mall/order/send.png" alt="">
                                    </el-tooltip>
                                </template>
                                <!-- 打印小票 -->
                                <el-tooltip class="item" effect="dark" content="打印小票" placement="top">
                                    <img class="app-order-icon"
                                         v-if="item.is_recycle == 0 && isShowPrint"
                                         @click="showPrintDialog(item.id)"
                                         src="statics/img/mall/order/print.png" alt="">
                                </el-tooltip>
                                <!-- 打印发货单 -->
                                <el-tooltip class="item"
                                            v-if="item.action_status && item.action_status.is_print_send_template == 1"
                                            effect="dark" content="打印发货单" placement="top">
                                    <img class="app-order-icon"
                                         src="statics/img/mall/order/invoice.png"
                                         @click="openInvoice(item)"
                                         alt="">
                                </el-tooltip>
                                <!-- 恢复订单 -->
                                <el-tooltip class="item" effect="dark" content="恢复订单" placement="top">
                                    <img class="app-order-icon" v-if="item.is_recycle == 1"
                                         @click="toRecycle(item)"
                                         src="statics/img/mall/order/renew.png" alt="">
                                </el-tooltip>
                                <!-- 删除订单 -->
                                <el-tooltip class="item" effect="dark" content="删除订单" placement="top">
                                    <img class="app-order-icon" v-if="item.is_recycle == 1"
                                         @click="toDelete(item)"
                                         src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                                <!-- 备注 -->
                                <el-tooltip class="item" effect="dark"
                                            :content="item.seller_remark != '' || item.bonus_remark ? '修改备注' : '添加备注'"
                                            placement="top">
                                    <img class="app-order-icon" @click="openDialog(item, sellerRemarkVisible = true)"
                                         v-if="item.is_recycle == 0 && isShowRemark"
                                         src="statics/img/mall/order/add_remark.png">
                                </el-tooltip>
                                <!-- 修改快递单号 -->
                                <template
                                        v-if="item.detailExpress && item.detailExpress.length == 1 && item.is_send == 1 && item.cancel_status != 1 && item.is_confirm == 0 && item.is_recycle == 0 && isShowSend && item.status != 0">
                                    <el-tooltip class="item" effect="dark" :content="item.send_type == 2 ? '修改配送员' : '修改快递单号'" placement="top">
                                        <img class="app-order-icon"
                                             @click="openExpress(item,'change', item.detailExpress[0].id)"
                                             src="statics/img/mall/order/change.png" alt="">
                                    </el-tooltip>
                                </template>
                                <!--多个物流信息订单 需到订单详情修改 -->
                                <template
                                        v-else-if="item.detailExpress && item.detailExpress.length >= 1 && item.cancel_status != 1 && item.is_confirm == 0 && item.is_recycle == 0 && isShowSend && item.status != 0">
                                    <el-tooltip class="item" effect="dark" :content="item.send_type == 2 ? '修改配送员' : '修改快递单号'" placement="top">
                                        <img class="app-order-icon"
                                             @click="openExpressHint" src="statics/img/mall/order/change.png"
                                             alt="">
                                    </el-tooltip>
                                </template>
                                <!-- 订单详情 -->
                                <el-tooltip class="item" effect="dark" content="查看订单详情" placement="top">
                                    <img v-if="isShowDetail" class="app-order-icon" @click="toDetail(item.id)"
                                         src="statics/img/mall/order/detail.png"
                                         alt="">
                                </el-tooltip>
                            </div>
                        </div>
                        <div v-if="!isShowAction && isShowPrintAction" class="app-order-info"
                             :style="{width:orderTitle[2].width}" style="padding: 10px;border-right: 0;">
                            <!-- 打印发货单 -->
                            <el-tooltip class="item" v-if="item.action_status.is_print_send_template == 1"
                                        effect="dark" content="打印发货单" placement="top">
                                <img class="app-order-icon"
                                     src="statics/img/mall/order/invoice.png"
                                     @click="openInvoice(item)"
                                     alt="">
                            </el-tooltip>
                        </div>
                        <!--目前用于分销-->
                        <slot name="orderAction" :order="item"></slot>
                    </div>
                    <div class="card-footer">
                        <template v-if="item.goods_type == 'ecard'">
                            <div style="margin: 10px 0;">收货人: {{item.name}} 电话：{{item.mobile}}</div>
                        </template>
                        <template v-if="item.send_type == 1">
                            <div flex="cross:center">
                                <el-tag style="margin-right: 10px;" size="small" hit type="warning">到店自提</el-tag>
                                <span class="address-box" v-if="item.store">门店名称：{{item.store.name}} 电话：{{item.store.mobile}} 地址：{{item.store.address}}</span>
                            </div>
                            <div style="margin: 10px 0;">收货人: {{item.name}} 电话：{{item.mobile}}</div>
                        </template>
                        <div v-else-if="(item.send_type == 0 || item.send_type == 2) && item.address">
                            <div flex="dir:left" style="word-break: break-word;">
                                <div class="address-box">收货人: {{item.name}} 电话：{{item.mobile}} 地址：{{item.address}}</div>
                                <el-button
                                        v-if="isShowEditAddress == 1 && item.send_type != 2 && item.cancel_status == 0 && item.is_send==0"
                                        type="text"
                                        icon="el-icon-edit"
                                        circle
                                        @click="openDialog(item, addressVisible = true)">
                                </el-button>
                            </div>
                        </div>
                        <slot name="footerFirst" :item="item"></slot>
                        <div class="seller-remark" v-if="item.seller_remark">商家备注：{{item.seller_remark ? item.seller_remark : item.words}}</div>
                        <slot name="footer" :item="item"></slot>
                    </div>
                </el-card>
            </div>
            <el-card v-loading="loading" shadow="never" class="app-order-item"
                     style="height: 100px;line-height: 100px;text-align: center;"
                     v-if="list && list.length == 0">
                暂无订单信息
            </el-card>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    background
                    :page-count="pagination.page_count"
                    :current-page="pagination.current_page"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>

        <el-dialog
                title="电子面单"
                :visible.sync="singleDialogVisible"
                width="30%">
            <div v-for="(expressSingle, index) in newExpressSingle"
                 :key="index"
                 class="express-single-box" flex="dir:left">
                <div>
                    <div class="label" style="background: #fffaef;color: #e6a23c;padding: 3px 0;">
                        收货信息:{{index + 1}}
                    </div>
                </div>
                <div flex="dir:top">
                    <div flex="cross:center">
                        <template v-if="expressSingle.send_type == 1">
                            <el-tag style="margin-right: 5px;" type="info" hit size="small">{{
                                expressSingle.express }}
                            </el-tag>
                            <a :href="'https://www.baidu.com/s?wd='+ expressSingle.express + expressSingle.express_no"
                               target="_blank" title='点击搜索运单号'>{{ expressSingle.express_no }}</a>
                        </template>
                        <template v-else>
                            <span>{{expressSingle.express_content}}</span>
                        </template>
                        <el-button @click="printTeplate(expressSingle.print_teplate)" v-if="expressSingle.print_teplate"
                                   style="margin-left: 10px;" size="mini" type="default">打印此面单
                        </el-button>
                    </div>
                    <div flex="dir:left" style="margin-top: 10px;">
                        <span class="label">配送商品:</span>
                        <img v-for="(goods, index) in expressSingle.goods_list"
                             :key="index"
                             class="goods-pic"
                             :src="goods.cover_pic">
                    </div>
                </div>
            </div>
        </el-dialog>

        <el-dialog width="25%" title="修改商品价格" :visible.sync="editGoodsPriceVisible">
            <el-form :model="editGoodsForm" ref="goodsValidateForm" label-width="80px" size="small">
                <el-form-item
                        label="商品价格"
                        prop="total_price"
                        :rules="[{ required: true, message: '价格不能为空'}]">
                    <el-input type="number" v-model="editGoodsForm.total_price" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="editGoodsPriceVisible = false">取 消</el-button>
                <el-button size="small" :loading="submitLoading" type="primary"
                           @click="changePrice('goodsValidateForm')">确 定
                </el-button>
            </div>
        </el-dialog>
        <div id="orderprint" v-show="false">
            <div v-for="(item) in printData"
                 :style="{padding: `0 ${mmConversionPx(printPar.left_right_margins) + 'px'}`,
                 marginLeft: printPar.offset.left + 'px',
                 marginRight: printPar.offset.right + 'px',
                 pageBreakBefore: `${printPar.printSetting.page_type === 2 ? 'always' : 'auto'}`,
                 width: `${mmConversionPx(Number(printPar.left_right_margins) + Number(printPar.left_right_margins) + Number(printPar.stencil_width) + Number(printPar.border_width)+ Number(printPar.border_width)) + 'px'}`,
                 marginBottom: `${printPar.printSetting.page_type === 1 ? printPar.printSetting.space + 'px' : '0'}`}">
                <div :style="{width: mmConversionPx(printPar.stencil_width) + 'px',minHeight:mmConversionPx(printPar.stencil_high) + 'px', cursor: 'pointer', border: `${mmConversionPx(printPar.border_width)}px solid #000000`, boxSizing: 'content-box', margin: 0}">
                    <div  style="display: inline-block;position: relative;white-space: nowrap;width: 100%;height: 50px">
                        <div :style="{
                                textAlign: printPar.headline.align === 0 ? 'center' : printPar.headline.align === 1 ? 'left' : 'right',
                                fontFamily: printPar.headline.fimaly,
                                 textDecoration: printPar.headline.underline ? 'underline' : 'none',
                                fontWeight: printPar.headline.bold ? 'bold' : 'normal',
                                fontStyle: printPar.headline.italic ? 'italic' : 'normal',
                                fontSize: printPar.headline.font / (4/3) + 'px',
                                height: '50px',
                                width: '62%',
                                boxSizing: 'border-box',
                                display: 'inline-block',
                                position: 'absolute',
                                top: 0,
                                lineHeight: '50px',
                                letterSpacing: printPar.headline.space / (4/3)+'px', borderBottom: `${!printPar.order.date && !printPar.order.time && !printPar.order.orderNumber ? '1px solid #000000' : 'none'}`}"
                             class="title"
                        >{{printPar.headline.name}}
                        </div>
                        <div style="width: 38%;height: 50px;border-left: 1px solid #000000;padding: 8px 0;text-align: center; box-sizing: border-box;display: inline-block;position: absolute;right: 0;">
                            <img :id="'code_' + item.order_no" style="height: 34px;"/>
                        </div>
                    </div>
                    <div
                            v-if="printPar.order.date || printPar.order.time || printPar.order.orderNumber"
                            :style="{display: 'flex',flexWrap:'wrap',borderTop: '1px solid #000000',borderBottom: '1px solid #000000',padding:'10px 10px 10px 0.5%', boxSizing: 'border-box'}"
                    >
                        <div style="width: 50%;font-size:10px;line-height:1;margin-bottom: 6px"
                             v-if="printPar.order.date">打印日期：{{printTime}}
                        </div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.time">
                            订单时间：{{item.pay_time}}
                        </div>
                        <div style="width: 50%;font-size:10px;line-height:1;" v-if="printPar.order.orderNumber">
                            订单号：{{item.order_no}}
                        </div>
                    </div>
                    <div :style="{display: 'flex', boxSizing: 'border-box'}"
                         v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address || printPar.personalInf.leaveComments  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod">
                        <div v-if="printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod"

                             :style="{boxSizing: 'border-box',width: `${printPar.personalInf.leaveComments ? '62%' : '100%'}`,borderBottom:'1px solid #000000',borderRight: `${ printPar.personalInf.leaveComments ? '1px solid #000000': 'none'}`, padding: ' 10px 10px 10px .5%'}"
                        >
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.name">
                                收货人信息：{{item.name}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.nickname">
                                收货人昵称：{{item.nickname}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.phone">
                                联系方式：{{item.mobile}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;" v-if="printPar.personalInf.payMethod">
                                支付方式：{{item.pay_type == 1 ? '在线支付' : item.pay_type == 2 ? '货到付款' : item.pay_type == 3 ?
                                '余额支付' : ''}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.shipMethod && item.send_type != 1">发货方式：{{item.send_type ==
                                0 ? '快递配送' : item.send_type == 1 ? '到店自提' : item.send_type == 2 ? '同城配送' : ''}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.address && item.send_type != 1">收货地址：{{item.address}}
                            </div>
                            <div style="font-size:10px;line-height:1.5;"
                                 v-if="printPar.personalInf.address && item.send_type == 1">
                                自提门店地址：{{item.store_address}}
                            </div>
                        </div>
                        <div :style="{boxSizing: 'border-box',width: `${printPar.personalInf.name || printPar.personalInf.nickname || printPar.personalInf.phone || printPar.personalInf.address  || printPar.personalInf.payMethod || printPar.personalInf.shipMethod? '38%' : '100%'}`,borderBottom:'1px solid #000000',padding: ' 10px 10px 10px .5%', fontSize:'10px', lineHeight:'1.2'}"
                             v-if="printPar.personalInf.leaveComments">
                            买家留言：{{item.remark}}
                        </div>
                    </div>
                    <div>
                        <div style="box-sizing:border-box;width: 100%;display: flex;border-bottom:1px solid #000000;position: relative;left: -0.5px;"
                             v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">
                            <div style="box-sizing:border-box;width: 6%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                 v-if="printPar.goodsInf.serial">序号
                            </div>
                           <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr">
                               <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;box-sizing:border-box;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                    v-if="printPar.goodsInf.name"
                               >商品名称
                               </div>
                               <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;box-sizing:border-box;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                    v-if="printPar.goodsInf.attr">规格
                               </div>
                           </div>
                            <div style="box-sizing:border-box;width: 8%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                 v-if="printPar.goodsInf.number">数量
                            </div>
                            <div style="box-sizing:border-box;width: 12%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                 v-if="printPar.goodsInf.univalent">小计
                            </div>
                            <div style="box-sizing:border-box;width: 18%;border-left: 1px solid #000000;height: 30px;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                 v-if="printPar.goodsInf.article_number">货号
                            </div>

                            <div style="box-sizing:border-box;width: 8%;height: 30px;border-left: 1px solid #000000;line-height: 30px;padding-left: 0.5%;font-size:10px;"
                                 v-if="printPar.goodsInf.unit">单位
                            </div>
                        </div>
                        <div v-for="good in item.detail"
                             style="box-sizing:border-box;width: 100%;display: flex;border-bottom: 1px solid #000000;position: relative;left: -0.5px;"
                             v-if="printPar.goodsInf.serial || printPar.goodsInf.name || printPar.goodsInf.attr || printPar.goodsInf.number || printPar.goodsInf.univalent || printPar.goodsInf.article_number || printPar.goodsInf.unit">
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 6%;border-left: 1px solid #000000;padding: 10px 10px 10px 0.5%;font-size:10px;position: relative"
                                 v-if="printPar.goodsInf.serial">
                                {{good.id}}
                            </div>
                           <div :style="tableWidth" style="display: flex;" v-if="printPar.goodsInf.name || printPar.goodsInf.attr" >
                               <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap:break-word;box-sizing:border-box;border-left: 1px solid #000000;padding: 10px 10px 10px 0.5%;font-size:10px;position: relative"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '60%' : '100%'}"
                                    v-if="printPar.goodsInf.name">
                                   {{good.name}}
                               </div>

                               <div style="min-width: 0;flex-grow: 1;flex-shrink: 1;-webkit-box-flex: 1;word-wrap:break-word;box-sizing:border-box;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                    :style="{width: printPar.goodsInf.name && printPar.goodsInf.attr ? '40%' : '100%'}"
                                    v-if="printPar.goodsInf.attr">
                                   {{good.attr}}
                               </div>
                           </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 8%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px 0.5%;position: relative"
                                 v-if="printPar.goodsInf.number">
                                {{good.num}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 12%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.univalent">
                                ￥{{good.price}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 18%;border-left: 1px solid #000000;font-size:10px;padding: 10px 10px 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.article_number">
                                {{good.goods_no}}
                            </div>
                            <div style="word-wrap:break-word;box-sizing:border-box;width: 8%;border-left: 1px solid #000000;word-wrap: break-word;font-size:10px;padding: 10px 10px 10px .5%;position: relative"
                                 v-if="printPar.goodsInf.unit">
                                {{good.unit}}
                            </div>
                        </div>

                        <div style="box-sizing:border-box;display: flex;height: 30px;padding-left: .5%;border-bottom:1px solid #000000;font-size: 10px;"
                             v-if="printPar.goodsInf.amount || printPar.goodsInf.totalAmount || printPar.goodsInf.fare || printPar.goodsInf.discount || printPar.goodsInf.actually_paid">
                            <div style="width: 24%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.amount">
                                订单金额：￥{{item.total_goods_original_price}}
                            </div>
                            <div style="width: 16%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.totalAmount">
                                总数量：{{item.goods_num}}
                            </div>
                            <div style="width: 21%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.fare">
                                运费：￥{{item.express_price}}
                            </div>
                            <div style="width: 20%;height: 30px;line-height:30px;" v-if="printPar.goodsInf.discount">
                                优惠：￥{{item.send_template_discount_price}}
                            </div>
                            <div style="width: 19%;height: 30px;line-height:30px;"
                                 v-if="printPar.goodsInf.actually_paid">实付：￥{{item.total_pay_price}}
                            </div>
                        </div>
                    </div>
                    <div :style="{boxSizing: 'border-box',display:'flex',borderBottom:'1px solid #000000'}"
                         v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address || printPar.sellerInf.remark">
                        <div :style="{width:`${!printPar.sellerInf.remark ? '100%': '62%'}`,padding: ' 10px 10px 10px .5%', fontSize: '10px',borderRight: `${!printPar.sellerInf.remark ? 'none' : '1px solid #000000'}`}"
                             v-if="printPar.sellerInf.branch || printPar.sellerInf.name || printPar.sellerInf.phone || printPar.sellerInf.postcode || printPar.sellerInf.address">
                            <div v-if="printPar.sellerInf.branch">网点名称：{{address_list[0].name}}</div>
                            <div v-if="printPar.sellerInf.name">联系人：{{address_list[0].username}}</div>
                            <div v-if="printPar.sellerInf.phone">联系方式：{{address_list[0].mobile}}</div>
                            <div v-if="printPar.sellerInf.postcode">网点邮编：{{address_list[0].code}}</div>
                            <div v-if="printPar.sellerInf.address">
                                网点地址：{{address_list[0].province}}{{address_list[0].city}}{{address_list[0].district}}{{address_list[0].address}}
                            </div>
                        </div>
                        <div :style="{boxSizing: 'border-box',width: `${!printPar.sellerInf.branch && !printPar.sellerInf.name && !printPar.sellerInf.phone && !printPar.sellerInf.postcode && !printPar.sellerInf.address ? '100%' : '38%'}`,padding: ' 10px 10px 10px .5%', fontSize: '10px'}"
                             v-if="printPar.sellerInf.remark">
                            商家备注：{{item.seller_remark ? item.seller_remark : item.words}}
                        </div>
                    </div>
                    <div flex="" :style="{boxSizing: 'border-box',padding: '10px 10px 10px 0.5%', fontSize: '10px'}">
                        <div style="width: 100%;" flex="">
                            <div v-html="printPar.customize" style="width: 100%;word-wrap:break-word;">
                                {{printPar.customize}}
                            </div>
                        </div>
                        <div v-html="printPar.customize_image"
                             style="width: 100%;margin-top: 10px;word-wrap:break-word;">{{printPar.customize_image}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="<?=Yii::$app->request->baseUrl?>/statics/js/JsBarcode.all.min.js"></script>
<script>

    Vue.component('app-order', {
        template: '#app-order-list',
        props: {
            orderTitle: {
                type: Array,
                default: function () {
                    return [
                        {width: '55%', name: '订单信息'},
                        {width: '20%', name: '实付金额'},
                        {width: '20%', name: '操作'}
                    ]
                }
            },
            selectList: {
                type: Array,
                default: function () {
                    return [
                        {value: '1', name: '订单号'},
                        {value: '9', name: '商户单号'},
                        {value: '2', name: '用户名'},
                        {value: '4', name: '用户ID'},
                        {value: '5', name: '商品名称'},
                        {value: '3', name: '收货人'},
                        {value: '6', name: '收货人电话'},
                        {value: '7', name: '门店名称'},
                        {value: 'goods_no', name: '商品货号'},
                        {value: 'address', name: '收货地址'},
                    ]
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {value: '-1', name: '全部'},
                        {value: '0', name: '未付款'},
                        {value: '1', name: '待发货'},
                        {value: '2', name: '待收货'},
                        {value: '9', name: '已收货'},
                        {value: '3', name: '已完成'},
                        {value: '4', name: '待处理'},
                        {value: '5', name: '已取消'},
                        {value: '7', name: '回收站'},
                    ]
                }
            },
            titleLabel: {
                type: String,
                default: '订单列表'
            },
            activeName: {
                type: String,
                default: '-1',
            },
            // 订单列表请求URL
            orderUrl: {
                type: String,
                default: 'mall/order/index',
            },
            // 删除回收站请求URl
            recycleUrl: {
                type: String,
                default: 'mall/order/destroy-all',
            },
            // 订单详情URL
            orderDetailUrl: {
                type: String,
                default: 'mall/order/detail'
            },
            // 修改备注请求URL
            editRemarkUrl: {
                type: String,
                default: 'mall/order/seller-remark'
            },
            // 是否显示更多订单信息
            showMoreInfo: {
                type: Boolean,
                default: false
            },
            // 控制按钮是否显示
            // 编辑收货地址
            isShowEditAddress: {
                type: Boolean,
                default: true
            },
            // 订单取消操作
            isShowCancel: {
                type: Boolean,
                default: true
            },
            // 编辑订单备注
            isShowRemark: {
                type: Boolean,
                default: true
            },
            // 所属平台
            isShowPlatform: {
                type: Boolean,
                default: true
            },
            // 社区团购展示团长利润
            isShowProfit: {
                type: Boolean,
                default: false
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
            isShowPrintAction: {
                type: Boolean,
                default: false
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
            isSendTemplate: {
                type: Boolean,
                default: false
            },
            // 订单发货
            isShowSend: {
                type: Boolean,
                default: true
            },
            // 清空回收站
            isShowRecycle: {
                type: Boolean,
                default: true
            },
            isShowHeader: {
                type: Boolean,
                default: true
            },
            // 批量导出
            isShowExport: {
                type: Boolean,
                default: true
            },
            // 订单配送方式
            isShowOrderType: {
                type: Boolean,
                default: true
            },
            isShowPrintInvoice: {
                type: Boolean,
                default: true
            },
            // 订单配送方式
            isShowOrderStatus: {
                type: Boolean,
                default: true
            },
            // 订单详情
            isShowDetail: {
                type: Boolean,
                default: true
            },
            // 操作按钮
            isShowAction: {
                type: Boolean,
                default: true
            },
            // 修改运费
            isShowEditExpressPrice: {
                type: Boolean,
                default: true
            },
            // 修改商品小计
            isShowEditSinglePrice: {
                type: Boolean,
                default: true
            },
            // 插件筛选
            isShowOrderPlugin: {
                type: Boolean,
                default: false
            },
            // 全选功能
            isShowCheckBox: {
                type: Boolean,
                default: false
            },
            isGoodsType: {
                type: Boolean,
                default: false
            },
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        time: null,
                        keyword: '',
                        keyword_1: '1',
                        date_start: '',
                        date_end: '',
                        platform: '',
                        status: '',
                        plugin: 'all',
                        send_type: -1,
                        type: '',
                        date_type: 'created_time',
                    }
                }
            },
            dateTypeList: {
                type: Array,
                default: function() {
                    return [
                        {
                            label: '付款时间',
                            value: 'pay_time'
                        },
                        {
                            label: '发货时间',
                            value: 'send_time'
                        },
                        {
                            label: '收货时间',
                            value: 'confirm_time'
                        },
                        {
                            label: '结束时间',
                            value: 'finish_time'
                        },
                    ]
                }
            }
        },
        data() {
            return {
                search: {},
                submitLoading: false,
                // 新的
                loading: false,
                list: [],
                pagination: {},
                newOrder: {},// 传给各子组件的订单信息
                addressVisible: false,// 修改收货地址
                sellerRemarkVisible: false,// 添加商户备注
                clerkVisible: false,// 订单核销
                sendVisible: false,// 发货
                sendType: '',// 发货类型
                cancelVisible: false,// 订单取消
                cancelType: -1,// 订单取消状态 同意|拒绝
                changePriceVisible: false,// 修改订单价格
                plugins: [
                    {
                        name: '全部订单',
                        sign: 'all',
                    }
                ],// 插件筛选
                export_list: [],//导出字段数据,
                // 修改商品单价 start
                editGoodsPriceVisible: false,//修改商品单价
                editGoodsForm: {
                    total_price: '',
                    id: 0,
                },//价格
                // 修改商品单价 end
                expressId: 0,// 修改物流
                citySendVisible: false, //同城配送发货
                singleDialogVisible: false,// 电子面单弹框
                newExpressSingle: [],
                printInvoice: false,
                printStatus: true,

                // 全选属性
                checkBoxSelect: {
                    isIndeterminate: false,
                    checkAll: false,
                    chooseList: [],
                    isShow: true,
                },

                printPar: {
                    printSetting: {
                        page_type: 1,
                        space: 10,
                    },
                    order: {
                        orderNumber: true,
                        time: true,
                        date: true,
                    },
                    personalInf: {
                        name: true,
                        nickname: true,
                        payMethod: true,
                        shipMethod: true,
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
                printTime: '2020年2月10日',
                printData: [],
                address_list: [],

                print_order_id: 0,
                hasPrintStatus: false,
                hide_function: {
                    is_show_ecard: false
                },
                tableWidth: 'width:48%',
            };
        },
        mounted() {
            this.search = this.newSearch;
            // 用户列表 用户订单数
            if (getQuery('user_id') > 0) {
                this.search.keyword_1 = '4';
                this.search.keyword = getQuery('user_id')
            }
            if (getQuery('order_no')) {
                this.search.keyword = getQuery('order_no')
            }
            if (getQuery('clerk_id') > 0) {
                this.search.clerk_id = getQuery('clerk_id');
            }
            if (getQuery('nickname')) {
                this.search.keyword = getQuery('nickname');
                this.search.captain_id = getQuery('captain_id');
                this.search.captain_name = getQuery('name');
                this.search.keyword_1 = '2';
            } else if (getQuery('name')) {
                this.search.keyword = getQuery('name');
                this.search.keyword_1 = '7';
            }
            this.getList();
        },
        methods: {
            showPrintDialog(order_id) {
                this.print_order_id = parseInt(order_id);
                this.hasPrintStatus = true;
            },
            // 关闭弹出框
            closeDialog() {
                this.submitLoading = false;
                this.id = null;
            },
            // 进入商品详情
            toDetail(id) {
                this.$navigate({
                    r: this.orderDetailUrl,
                    order_id: id
                })
            },
            // 确认收货
            confirm(id) {
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/confirm',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
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
                }).catch(e => {
                });
            },
            // 结束订单
            saleOrder(id) {
                this.$confirm('是否结束该订单?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/order-sales',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            // 打印小票
            print(id) {
                this.$confirm('是否打印小票?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'mall/order/order-print',
                            order_id: id
                        },
                        method: 'get',
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
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
            changeGoods(e) {
                this.editGoodsPriceVisible = true;
                this.editGoodsForm.total_price = e.total_price;
                this.editGoodsForm.id = e.id;
            },
            // 修改商品金额
            changePrice(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/order/update-price',
                            },
                            data: {
                                order_detail_id: this.editGoodsForm.id,
                                total_price: this.editGoodsForm.total_price
                            },
                            method: 'post',
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.editGoodsPriceVisible = false
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                                this.getList();
                            } else {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'warning'
                                });
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 回收站
            toRecycle(e) {
                let that = this;
                let text = "是否放入回收站(可在回收站中恢复)?";
                let para = {
                    order_id: e.id,
                    is_recycle: 1
                };
                if (e.is_recycle == 1) {
                    para.is_recycle = 0;
                    text = "是否移出回收站?"
                }
                this.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/recycle',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        e.visible = false;
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            expressSingle(newExpressSingle) {
                this.singleDialogVisible = true
                this.newExpressSingle = newExpressSingle;
            },
            printTeplate(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
            // 删除订单
            toDelete(e) {
                this.$confirm('是否删除订单？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/destroy',
                        },
                        data: {
                            order_id: e.id,
                        },
                        method: 'post'
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        }

                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            // 获取订单列表
            getList() {
                this.loading = true;
                let params = {
                    r: this.orderUrl
                };
                Object.keys(this.search).map((key) => {
                    params[key] = this.search[key]
                });
                params['flag'] = '';

                request({
                    params: params,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        let newList = e.data.data.list;
                        newList.forEach(function (item) {
                            item.isChecked = false;
                        });
                        this.list = newList;
                        this.export_list = e.data.data.export_list;
                        this.pagination = e.data.data.pagination;
                        this.plugins = e.data.data.plugins;
                        if(e.data.data.hide_function) {
                            this.hide_function = e.data.data.hide_function;
                        }
                    }
                }).catch(e => {
                });
            },
            // app-search组件 搜索事件
            toSearch(searchParams) {
                this.search = searchParams;
                this.search.page = 1;
                this.checkBoxSelect.chooseList = [];
                this.checkBoxSelect.checkAll = false;
                this.checkBoxSelect.isIndeterminate = false;
                this.getList();
            },
            // 分页
            pageChange(page) {
                this.search.page = page;
                this.getList();
            },
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.addressVisible = false;
                this.sellerRemarkVisible = false;
                this.clerkVisible = false;
                this.sendVisible = false;
                this.changePriceVisible = false;
                this.cancelVisible = false;
                this.citySendVisible = false;
                this.printInvoice = false;
            },
            dialogSubmit() {
                this.expressId = 0;
                this.getList();
            },
            // 发货
            openExpress(order, type, expressId) {
                this.newOrder = order;
                this.sendType = type;
                this.sendVisible = true;
                this.expressId = parseInt(expressId);
            },
            // 申请取消订单
            agreeCancel(row, status) {
                this.newOrder = row;
                this.cancelType = status;
                this.cancelVisible = true;
            },
            // 清空回收站
            toRecycleAll(e) {
                this.$confirm('此操作将清空回收站, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    this.submitLoading = true;
                    request({
                        params: {
                            r: this.recycleUrl,
                        },
                        data: {},
                        method: 'post',
                    }).then(e => {
                        e.visible = false;
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                        this.submitLoading = false;
                    });
                }).catch(() => {
                });
            },
            openExpressHint() {
                this.$alert('该订单有多个物流,请到订单详情修改物流信息', '提示', {
                    confirmButtonText: '确定',
                    callback: action => {
                    }
                });
            },
            openCity(order, sendType) {
                this.newOrder = order;
                this.sendType = sendType
                this.citySendVisible = true;
            },
            storeOrderSend(order) {
                this.$alert('是否将配送方式改为快递配送？', '提示', {
                    confirmButtonText: '确定',
                    showCancelButton: true,
                    type: 'warning',
                    callback: action => {
                        if (action == 'confirm') {
                            this.openDialog(order)
                            this.addressVisible = true;
                        }
                    }
                });
            },

            // 打印发货单
            openInvoice(item) {
                if (item.is_send == 0) {
                    this.printStatus = true;
                }
                // if (item.is_send == 1 && item.is_confirm == 0) {
                //     this.printStatus = false;
                // }
                this.newOrder = item;
                this.printInvoice = true;
                console.log(1)
            },
            setPrintInvoice() {
                if (this.checkBoxSelect.chooseList.length === 0) {
                    this.$message({
                        message: '请先勾选订单',
                        type: 'warning'
                    });
                    return true;
                }
                this.printInvoice = true;
                this.printStatus = false;
            },
            select_template(e, select_order, order) {
                if (this.isSendTemplate) {
                    this.$emit('select_template', e, select_order, order);
                } else {
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
                        let {detail} = order;
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
                                        for (let m = 0; m < order_list.length; m++) {
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
                        let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '月';
                        let D = date.getDate() + '日';
                        this.printPar = e.params;
                        this.printTime = Y + M + D;
                        let discount_price = Number(order.member_discount_price) + Number(order.coupon_discount_price);
                        for (let i = 0; i < new_detailExpress.length; i++) {
                            let detail = [];
                            if (new_detailExpress[i].expressRelation.length === 0) break;
                            for (let j = 0; j < new_detailExpress[i].expressRelation.length; j++) {
                                detail.push({
                                    name: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.name,
                                    num: new_detailExpress[i].expressRelation[j].num,
                                    unit: new_detailExpress[i].expressRelation[j].goods.goodsWarehouse.unit,
                                    price: new_detailExpress[i].expressRelation[j].total_price,
                                    id: i + 1,
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
                                pay_type: order.pay_type,
                                total_goods_price: order.total_goods_price,
                                total_goods_original_price: order.total_goods_original_price,
                                total_pay_price: order.total_pay_price,
                                express_price: order.express_price,
                                send_type: order.send_type,
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
                                id: i + 1,
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
                                pay_type: order.pay_type,
                                total_goods_price: order.total_goods_price,
                                total_goods_original_price: order.total_goods_original_price,
                                total_pay_price: order.total_pay_price,
                                express_price: order.express_price,
                                send_type: order.send_type,
                                discount_price: discount_price,
                                goods_num: order.goods_num,
                                detail: order_detail,
                                send_template_discount_price: order.send_template_discount_price
                            };
                            if (order.send_type == 1) {
                                data.store_address = order.store.address;
                            }
                            this.printData.push(data);
                        }
                        this.nameWidth(this.printPar);
                        document.getElementById('orderprint').style.display = 'block';
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
                               let newWindow = window.open("打印窗口", "_blank");//打印窗口要换成页面的url
                               let docStr = document.getElementById('orderprint').outerHTML;
                               newWindow.document.write(docStr);
                               newWindow.document.close();
                               newWindow.print();
                               newWindow.close();
                               document.getElementById('orderprint').style.display = 'none';
                           }, 1000);
                        })
                    });
                }
            },
            select_template_all(e) {
                this.$emit('print_invoice', e, this.checkBoxSelect.chooseList);
            },
            mmConversionPx(value) {
                let inch = value * 2.834;
                return inch;
            },
            // 全选操作
            handleCheckAllChange(val) {
                let self = this;
                self.checkBoxSelect.chooseList = [];
                self.list.forEach(function (item) {
                    item.isChecked = val;
                    if (item.isChecked) {
                        self.checkBoxSelect.chooseList.push(item);
                    }
                });
                this.checkBoxSelect.isIndeterminate = false;
            },
            handleCheckedCitiesChange(order) {
                let self = this;
                if (order.isChecked) {
                    self.checkBoxSelect.chooseList.push(order);
                } else {
                    self.checkBoxSelect.chooseList.forEach((cItem, index) => {
                        if (cItem.id === order.id) {
                            self.checkBoxSelect.chooseList.splice(index, 1);
                        }
                    })
                }
                let checkedCount = self.checkBoxSelect.chooseList.length;
                this.checkBoxSelect.checkAll = checkedCount === this.list.length;
                this.checkBoxSelect.isIndeterminate = checkedCount > 0 && checkedCount < this.list.length;
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
        },
    });
</script>
