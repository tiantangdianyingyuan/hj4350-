<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('order/app-search');
Yii::$app->loadViewComponent('refund/app-remark');
Yii::$app->loadViewComponent('refund/app-cancel');
Yii::$app->loadViewComponent('refund/app-agree-refund');
Yii::$app->loadViewComponent('order/app-edit-address');
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    /*表格头部样式 start*/
    .app-order-title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
        min-width: 750px;
    }

    .app-order-title div {
        text-align: center;
    }

    /*表格头部样式 end*/
    /*表格底部样式 start*/
    .card-footer {
        background: #F3F5F6;
        padding: 20px;
    }

    .card-footer .address-box {
        margin-right: 10px;
    }

    /*表格底部样式 end*/

    /*表格中部内容样式 start*/
    .el-card__body {
        padding: 0;
    }

    .app-order-item .el-button {
        padding: 0;
    }

    .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
    }

    .goods-info {
        padding: 5px;
        width: 60%;
        font-size: 12px;
        color: #353535;
        text-align: left;
    }

    .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .goods-image {
        height: 90px;
        width: 90px;
        margin-right: 15px;
        float: left;
    }

    .app-order-item {
        margin-top: 20px;
        min-width: 750px;
    }

    .app-order-item:hover {
        border: 1px solid #3399FF;
    }

    .app-order-item .el-card__header {
        padding: 0;
    }

    .app-order-head {
        padding: 20px;
        background-color: #F3F5F6;
        color: #303133;
        min-width: 750px;
        display: flex;
        position: relative;
    }

    .app-order-time {
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

    .app-order-body {
        display: flex;
        flex-wrap: nowrap;
    }

    .goods-item {
        width: 50%;
        border-right: 1px solid #EBEEF5;
    }

    .goods-item .goods {
        position: relative;
        padding: 20px;
        min-height: 130px;
        border-top: 1px solid #EBEEF5;
    }

    .goods-item .goods:first-of-type {
        border-top: 0;
    }

    .goods-item .goods-info .goods-name {
        margin-bottom: 5px;
    }

    .goods-item .goods .app-order-goods-price {
        height: 24px;
        margin-top: 10px;
        position: absolute;
        bottom: 20px;
        left: 125px;
    }

    .app-order-info {
        display: flex;
        align-items: center;
        width: 15%;
        text-align: center;
        border-right: 1px solid #EBEEF5;
    }

    .app-order-info > div {
        width: 100%;
    }

    .express-price {
        height: 30px;
        line-height: 30px;
    }

    .app-order-icon {
        margin-right: 5%;
        cursor: pointer;
    }

    .app-order-icon:last-of-type {
        margin-right: 0;
    }

    .goods-num {
        margin-left: 65px;
    }

    .remark-box {
        padding-top: 3px;
        margin-left: 7px;
    }

    .remark-img {
        margin-top: 10px;
        width: 33.33%
    }

    .click-img {
        width: 100%;
    }

    .small-img {
        width: 20px;
        /*padding: 5px;*/
        cursor: pointer;
    }

    /*表格中部内容样式 end*/

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <!-- 标题栏 -->
        <div slot="header">
            <span>售后订单</span>
            <div style="float: right;margin: -5px 0">
                <app-export-dialog :field_list='export_list' :params="search"></app-export-dialog>
            </div>
        </div>
        <div class="form-body">
            <app-search
                    @search="toSearch"
                    :select-list="selectList"
                    :is-show-order-type="false"
                    date-label="添加时间"
                    :tabs="tabs">
            </app-search>
            <app-remark
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="remarkVisible"
                    :refund-order="newOrder">
            </app-remark>
            <app-cancel
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="cancelVisible"
                    :refund-order="newOrder">
            </app-cancel>
            <app-agree-refund
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="agreeRefundVisible"
                    :is-show-confirm="refundConfirmVisible"
                    :address="address"
                    :refund-order="newOrder">
            </app-agree-refund>
            <app-edit-address
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="addressVisible"
                    :order="newOrder">
            </app-edit-address>

            <div class="app-order-title">
                <div v-for="(item,index) in orderTitle" :key="index" :style="{width: item.width}">{{item.name}}</div>
            </div>

            <el-card v-loading="loading"
                     v-if="list.length > 0"
                     v-for="item in list"
                     class="app-order-item"
                     :key="item.id"
                     shadow="never">
                <div slot="header" class="app-order-head">
                    <div class="app-order-time">{{ item.created_at }}</div>
                    <div class="app-order-user">
                        <span class="app-order-time">订单号：</span>{{ item.order_no }}
                    </div>
                    <div class="app-order-user">
                        <img src="statics/img/mall/ali.png" v-if="item.platform == 'aliapp'" alt="">
                        <img src="statics/img/mall/wx.png" v-else-if="item.platform == 'wxapp'" alt="">
                        <img src="statics/img/mall/toutiao.png" v-else-if="item.platform == 'ttapp'" alt="">
                        <img src="statics/img/mall/baidu.png" v-else-if="item.platform == 'bdapp'" alt="">
                        <span>{{ item.nickname }}({{ item.user_id }})</span>
                    </div>
                    <div flex="cross:center" class="remark-box" v-if="item.remark">
                        <el-tooltip effect="dark" placement="bottom">
                            <div slot="content">
                                <span v-if="item.remark">买家留言:{{item.remark}}</span>
                                <br v-if="item.remark"/>
                            </div>
                        </el-tooltip>
                    </div>
                </div>
                <div class="app-order-body">
                    <!-- 订单信息 -->
                    <div class="goods-item" :style="{width: orderTitle[0].width}">
                        <div class="goods" v-for="goods in item.detail">
                            <img :src="goods.goods_info && goods.goods_info.goods_attr && goods.goods_info.goods_attr.pic_url ? goods.goods_info.goods_attr.pic_url : goods.goods_info.goods_attr.cover_pic"
                                 class="goods-image">
                            <div flex="dir:left">
                                <div class="goods-info">
                                    <div class="goods-name">
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
                                    </div>
                                    <div style="margin-bottom: 24px;">
                                            <span style="margin-right: 10px;">规格：
                                                <el-tag size="mini"
                                                        style="margin-right: 5px;"
                                                        v-for="attr in goods.attr_list"
                                                        :key="attr.id">
                                                    {{attr.attr_group_name}}:{{attr.attr_name}}
                                                </el-tag>
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
                                    <div flex="cross:center main:center">
                                        <span>小计：￥{{goods.total_price}}</span>
                                    </div>
                                    <div flex="cross:center main:center">数量：x {{goods.num}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 售后类型 -->
                    <div class="app-order-info" style="width: 8%">
                        <div>
                            <div v-if="item.type == 1" type="danger">退货退款</div>
                            <div v-if="item.type == 2" type="warning">换货</div>
                            <div v-if="item.type == 3" type="danger">仅退款</div>
                        </div>
                    </div>
                    <!-- 订单状态 -->
                    <div class="app-order-info" style="width: 8%">
                        <div>
                            <div size="medium" hit v-if="item.status == 1" type="info">
                                <span>待卖家受理</span>
                                <el-tooltip v-if="item.order.pay_type == 2" effect="dark"
                                            content="货到付款方式的退款需要线下与客户自行协商"
                                            placement="top">
                                    <i class="header-icon el-icon-info"></i>
                                </el-tooltip>
                            </div>
                            <div v-else>{{item.status_text}}</div>
                        </div>
                    </div>
                    <!-- 金额 -->
                    <div class="app-order-info" style="width:11%">
                        <div flex="dir:top">
                            <div>实付金额:￥{{item.order.total_pay_price}}</div>
                            <template v-if="item.type == 1 || item.type == 3">
                                <div>申请退款:￥{{item.refund_price}}</div>
                                <div v-if="item.is_refund == 1">实际退款:￥{{item.reality_refund_price}}</div>
                            </template>
                        </div>
                    </div>
                    <!-- 申请理由 -->
                    <div class="app-order-info" style="width: 8%;text-align: center;padding: 0 5px;">
                        <div>
                            <el-tooltip effect="dark" placement="top">
                                <template slot="content">
                                    <div style="width: 320px;">{{item.remark == '' ? '无' : item.remark}}</div>
                                </template>
                                <app-ellipsis :line="3"><div style="text-align: left;">{{item.remark == '' ? '无' : item.remark}}</div></app-ellipsis>
                            </el-tooltip>
                            <el-row type="flex" flex="wrap:wrap" justify="left">
                                <el-col class="remark-img" v-for="item in item.pic_list" :key="item.index">
                                    <el-popover
                                            placement="left"
                                            width="200"
                                            trigger="hover">
                                        <img style="width: 100%" :src="item" alt="">
                                        <img class="small-img" :src="item" alt="" @click="openBig(item)"
                                             slot="reference">
                                    </el-popover>
                                </el-col>
                            </el-row>
                        </div>
                    </div>


                    <div flex="wrap:wrap main:center cross:center" class="app-order-info" style="width:15%;">
                        <el-tooltip class="item" effect="dark" content="取消售后" placement="top">
                            <img class="app-order-icon" @click="openDialog(item, cancelVisible = true)"
                                 v-if="item.action_status.is_show_cancel_refund"
                                 src="statics/img/mall/order/cancel.png"
                                 alt="">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="同意" placement="top">
                            <img class="app-order-icon" src="statics/img/mall/pass.png" alt="" v-if="item.action_status.is_show_apply == 1"
                                 @click="openDialog(item, agreeRefundVisible = true)">
                        </el-tooltip>
                        <!-- 拒绝 -->
                        <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                            <img class="app-order-icon" src="statics/img/mall/nopass.png" alt="" v-if="item.action_status.is_show_apply == 1"
                                 @click="openDialog(item, remarkVisible = true)">
                        </el-tooltip>
                        <!-- 确认收货 -->
                        <el-tooltip class="item" effect="dark" content="确认收货" placement="top">
                            <img class="app-order-icon" src="statics/img/mall/order/confirm.png" alt=""
                                 v-if="item.action_status.is_show_confirm == 1"
                                 @click="shouHuo(item)">
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="打款" placement="top">
                            <img class="app-order-icon" src="statics/img/mall/pay.png" alt=""
                                 v-if="item.action_status.is_show_refund == 1"
                                 @click="openDialog(item, refundConfirmVisible = true)">
                        </el-tooltip>
                        <!-- 售后详情 -->
                        <el-tooltip class="item" effect="dark" content="售后详情" placement="top">
                            <img class="app-order-icon" @click="toRefundDetail(item.id)"
                                 src="statics/img/mall/order/refund-detail.png"
                                 alt="">
                        </el-tooltip>
                        <!-- 订单详情 -->
                        <el-tooltip class="item" effect="dark" content="订单详情" placement="top">
                            <img class="app-order-icon" @click="toDetail(item.order_id)"
                                 src="statics/img/mall/order/detail.png"
                                 alt="">
                        </el-tooltip>
                    </div>
                </div>
                <div class="card-footer">
                    <div v-if="item.order.send_type == 1" flex="cross:center">
                        <el-tag style="margin-right: 10px;" size="small" hit type="warning">到店自提</el-tag>
                        <span class="address-box" v-if="item.order.store">门店名称：{{item.order.store.name}} 电话：{{item.order.store.mobile}} 地址：{{item.order.store.address}}</span>
                    </div>
                    <div v-else-if="(item.order.send_type == 0 || item.order.send_type == 2) && item.order.address">
                        <div flex="dir:left">
                            <div class="address-box">收件人: {{item.order.name}} 电话：{{item.order.mobile}}
                                地址：{{item.order.address}}
                            </div>
                            <el-button v-if="item.action_status.is_show_edit_address == 1"
                                       type="text"
                                       icon="el-icon-edit"
                                       circle
                                       @click="openDialog(item.order, addressVisible = true)">
                            </el-button>
                        </div>
                    </div>
                </div>
            </el-card>
            <el-card v-loading="loading" shadow="never" class="app-order-item"
                     style="height: 100px;line-height: 100px;text-align: center;"
                     v-if="list.length == 0">
                暂无订单信息
            </el-card>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    background
                    hide-on-single-page
                    :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
                    :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
        <!-- 查看大图 -->
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    time: null,
                    r: 'mall/order/refund',
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                    platform: '',
                },
                loading: false,
                pagination: {},
                list: [],
                address: [],
                selectList: [
                    {value: '1', name: '订单号'},
                    {value: '8', name: '原订单号'},
                    {value: '2', name: '用户名'},
                    {value: '4', name: '用户ID'},
                    {value: '5', name: '商品名称'},
                    {value: '3', name: '收件人'},
                    {value: '6', name: '收件人电话'}
                ],
                orderTitle: [
                    {width: '50%', name: '商品信息'},
                    {width: '8%', name: '售后类型'},
                    {width: '8%', name: '订单状态'},
                    {width: '8%', name: '金额'},
                    {width: '8%', name: '申请理由'},
                    {width: '18%', name: '操作'},
                ],
                export_list: [],
                tabs: [
                    {value: '-1', name: '全部'},
                    {value: '0', name: '待审核'},
                    {value: '1', name: '待买家处理'},
                    {value: '2', name: '待卖家处理'},
                    {value: '3', name: '已完成'},
                ],
                // 查看大图
                dialogImg: false,
                click_img: false,
                newOrder: {},
                remarkVisible: false,
                cancelVisible: false,
                agreeRefundVisible: false,
                refundConfirmVisible: false,
                addressVisible: false,// 修改收货地址
            };
        },
        created() {
            this.getList();
        },
        methods: {
            // 订单状态分类
            handleClick(e) {
                this.searchOrder();
            },

            // 搜索
            searchOrder() {
                this.search.page = 1;
                this.getList();
            },
            // 获取列表
            getList() {
                this.loading = true;
                this.list = [];

                let params = {
                    r: 'mall/order/refund'
                };
                Object.keys(this.search).map((key) => {
                    params[key] = this.search[key]
                });

                request({
                    params: params,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        let detail = [];
                        // 将detail转成数组 发货展示用
                        for (let i = 0; i < this.list.length; i++) {
                            this.list[i].detail = [this.list[i].detail]
                        }
                        this.pagination = e.data.data.pagination;
                        this.address = e.data.data.address;
                        this.export_list = e.data.data.export_list;
                    }

                }).catch(e => {
                });
            },
            // 分页
            pageChange(page) {
                this.search.page = page;
                this.getList();
            },
            // 新的
            // app-search组件 搜索事件
            toSearch(searchParams) {
                this.search = searchParams;
                this.getList();
            },
            // 进入商品详情
            toDetail(id) {
                this.$navigate({
                    r: 'mall/order/detail',
                    order_id: id
                })
            },
            toRefundDetail(id) {
                this.$navigate({
                    r: 'mall/order/refund-detail',
                    refund_order_id: id
                })
            },
            // 显示大图
            openBig(e) {
                this.click_img = e;
                this.dialogImg = true;
            },
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.remarkVisible = false;
                this.cancelVisible = false;
                this.agreeRefundVisible = false;
                this.refundConfirmVisible = false;
                this.addressVisible = false;
            },
            dialogSubmit() {
                this.getList();
            },
            // 商家确认收货
            shouHuo(refundOrder) {
                // 换货的确认收货
                if (refundOrder.status == 2 && refundOrder.type == 2 && refundOrder.order.send_type != 1  && refundOrder.order.send_type != 2) {
                    this.newOrder = refundOrder;
                    this.refundConfirmVisible = true;
                    return;
                }
                // 退款的确认收货
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/shou-huo',
                        },
                        data: {
                            refund_order_id: refundOrder.id
                        },
                        method: 'post'
                    }).then(e => {
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.getList();
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
        }
    });
</script>
