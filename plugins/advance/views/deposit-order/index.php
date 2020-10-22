<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: fjt
 */
Yii::$app->loadViewComponent('app-order');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
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
    .app-order-del {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #7C868D;
        font-size: 18px;
        padding: 0;
        display: none;
    }
    .app-order-list .app-order-item:hover .app-order-del {
        display: block;
    }
    .app-order-info {
        width: 15px !important;
        margin-left: 30px;
    }
    .app-order-body {
        display: flex;
        flex-wrap: nowrap;
        height: 130px;
    }
    .app-order-list .goods-item {
        width: 40% !important;
        border-right: 1px solid #EBEEF5;
        display: flex;
        align-items: center;
    }
     .good-cover {
        width: 90px;
        height: 90px;
         margin-left: 20px;
         margin-right: 15px;
    }
    .goods-price {
        border-right: 1px solid #EBEEF5;
        width: 15% !important;
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
    }
    .goods-number {
        border-right: 1px solid #EBEEF5;
        width: 15% !important;
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
    }
    .goods-pay-price {
        border-right: 1px solid #EBEEF5;
        width: 15% !important;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
    }
    .goods-operating {
        width: 15%;
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
    }
    .good-re {
        width: 32px;
        height: 32px;
        background-color: #fde9e9;
        border-radius: 50%;
        line-height: 38px;
        text-align: center;
        margin-right: 10px;
        cursor: pointer;
    }
    .good-re > image {
        width: 16px;
        height: 15px;
    }
    .good-refund {
        width: 32px;
        height: 32px;
        background-color: #fff7f1;
        border-radius: 50%;
        line-height: 38px;
        text-align: center;
        margin-right: 10px;
        cursor: pointer;
    }
    .good-refund > image {
        width: 32px;
        height: 32px;
    }
    .name {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .goods-item .el-tag--small {
        height: 19px;
        line-height: 19px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>预售订单</span>
            <app-export-dialog action_url="index.php?r=plugin/advance/mall/deposit-order/index"
                               style="float: right;margin-top: -5px" :field_list='export_list'
                               :params="search">
            </app-export-dialog>
        </div>
        <div class="table-body">
            <div class="app-order-list" style="margin-bottom: 20px;">
                <el-form size="small" :inline="true" :model="search">
                    <el-form-item label="">
                        <!-- 时间选择框 -->
                        <el-date-picker
                                @change="changeTime"
                                v-model="search.time"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="所属平台">
                        <el-select style="width: 120px;" size="small" v-model="search.platform" @change='searchOrder'>
                            <el-option key="all" label="全部平台" value=""></el-option>
                            <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                            <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                            <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                            <el-option key="bdapp" label="百度" value="bdapp"></el-option>
                        </el-select>
                    </el-form-item>
                    <!-- 搜索框 -->
                    <el-form-item>
                        <div class="input-item">
                            <el-input v-model="search.keyword" placeholder="请输入搜索内容" clearable @clear="searchOrderEmp"
                                      @keyup.enter.native="searchOrderKey">
                                <el-select style="width: 120px" slot="prepend" v-model="search.keyword_1">
                                    <el-option v-for="item in select_list" :key="item.value" :label="item.name"
                                               :value="item.value"></el-option>
                                </el-select>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
                <!-- 订单状态选择 -->
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="全部" name="-1"></el-tab-pane>
                    <el-tab-pane label="未付款" name="0"></el-tab-pane>
                    <el-tab-pane label="已付款" name="1"></el-tab-pane>
                    <el-tab-pane label="已付尾款" name="3"></el-tab-pane>
                    <el-tab-pane label="已退款" name="2"></el-tab-pane>
                </el-tabs>
                <div class="app-order-title">
                    <div v-for="(item,index) in order_title" :key="index" :style="{width: item.width}">{{item.name}}</div>
                </div>
                <el-card v-loading="loading" v-for="(item, index) in list" class="app-order-item" :key="item.id"
                        shadow="never">
                    <div slot="header" class="app-order-head">
                        <div class="app-order-time">{{ item.created_at }}</div>
                        <div class="app-order-user"><span class="app-order-time">订单号：</span>{{ item.advance_no }}</div>
                        <div class="app-order-user">
                            <img src="statics/img/mall/ali.png" v-if="item.platform == 'aliapp'" alt="">
                            <img src="statics/img/mall/wx.png" v-else-if="item.platform == 'wxapp'" alt="">
                            <img src="statics/img/mall/toutiao.png" v-else-if="item.platform == 'ttapp'" alt="">
                            <img src="statics/img/mall/baidu.png" v-else-if="item.platform == 'bdapp'" alt="">
                            <span>{{ item.nickname }}({{ item.user_id }})</span>
                        </div>
                        <div class="app-order-info" v-if="item.remark" style="display: inline-block;">
                            <el-tooltip  effect="dark" placement="bottom" :content="`卖家备注：${item.remark}`">
                                <div v-if="item.remark != ''"><img
                                            src="statics/img/mall/order/remark.png" alt=""></div>
                            </el-tooltip>
                        </div>
                        <div class="app-order-offline">
                            <el-tag size="small" type="warning" v-if="item.is_pay == 0">未付款</el-tag>
                            <el-tag size="small" type="warning" v-if="item.is_pay == 1">已付款</el-tag>
                            <el-tag size="small" type="warning" v-if="item.is_pay == 1 && item.is_refund == 1 ">已退款</el-tag>
                        </div>
                    </div>
                    <div class="app-order-body">
                        <div class="goods-item">
                            <image class="good-cover" :src="item.goods.goodsWarehouse.cover_pic"></image>
                            <div>
                                <p>
                                    <span>
                                        <el-tag size="small" type="warning" >预售</el-tag>
                                        {{item.goods.goodsWarehouse.name}}
                                    </span>
                                    <span class="name" style="margin-left: 10px;"></span>
                                </p>
                                <div style="display: flex;">
                                    <div style="margin-right: 10px">规格:</div>
                                    <el-tag size="small" v-for="it in JSON.parse(item.goods_info).attr_list" style="margin-right: 10px;">
                                        <span>{{it.attr_group_name}}:</span>
                                        <span>{{it.attr_name}}</span>
                                    </el-tag>
                                </div>
                            </div>
                        </div>
                        <div class="goods-price">
                           ￥ {{item.goods.use_attr == 1 ? JSON.parse(item.goods_info).goods_attr.price : item.goods.price}}
                        </div>
                        <div class="goods-number">
                            ×{{item.goods_num}}
                        </div>
                        <div class="goods-pay-price">
                            <div style="margin-bottom: 10px;">
                                <span style="margin-right: 5px;">定金￥{{item.deposit}}</span>
                                <el-tag size="small" type="warning" effect="dark" v-if="item.pay_type == 1">在线支付</el-tag>
                                <el-tag size="small" type="warning" effect="dark" v-if="item.pay_type == 2">货到付款</el-tag>
                                <el-tag size="small" type="warning" effect="dark" v-if="item.pay_type == 3">余额支付</el-tag>
                            </div>
                            <template v-if="item.refund > 0">
                                <div>已退￥{{item.refund}}</div>
                            </template>
                        </div>
                        <div class="goods-operating">
                            <template v-if="item.order_id == 0">
                                <el-tooltip class="item" effect="dark" content="订单退款" placement="top" v-if="item.is_pay  == 1 && item.is_refund == 0">
                                    <div class="good-refund" @click="refund(item.id)"><img
                                                src="statics/img/plugins/refund.png" alt=""></div>
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="订单删除" placement="top" v-if="item.is_pay  == 0 && item.is_refund == 0">
                                    <div class="good-refund" @click="del(item.id, index)"><img
                                                src="statics/img/mall/order/del.png" alt=""></div>
                                </el-tooltip>
                            </template>
                            <el-tooltip class="item" effect="dark" content="商家备注" placement="top">
                                <div class="good-re" @click="open(item, index)"><img
                                            src="statics/img/mall/order/remark.png" alt=""></div>
                            </el-tooltip>
                            <!-- 订单详情 -->
                            <el-tooltip class="item" effect="dark" content="查看订单详情" placement="top">
                                <img class="app-order-icon" @click="toDetail(item.id)" src="statics/img/mall/order/detail.png"
                                     alt="">
                            </el-tooltip>
                        </div>
                    </div>
                </el-card>
                <el-card v-loading="loading" shadow="never" class="app-order-item"
                         style="height: 100px;line-height: 100px;text-align: center;"
                         v-if="list.length == 0">
                    暂无订单信息
                </el-card>
            </div>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    background
                    :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
                    :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                submit_load: false,
                export_list: [],
                list: [],
                express_list: [],
                loading: false,
                activeName: '-1',
                pagination: null,
                obj: {},
                search: {
                    time: null,
                    keyword: '',
                    keyword_1: '1',
                    date_start: '',
                    date_end: '',
                    platform: '',
                    status: '-1',
                    page: 1,
                },
                select_list: [
                    {value: '1', name: '订单号'},
                    {value: '2', name: '昵称'},
                    {value: '4', name: '用户ID'},
                    {value: '5', name: '手机号'}
                ],
                order_title:  [
                    {width: '40%', name: '商品'},
                    {width: '15%', name: '单价'},
                    {
                    width: '15%',
                    name: '数量'
                    },
                    {width: '15%', name: '实付金额'},
                    {width: '15%', name: '操作'}
                ]
            };
        },
        created() {
        },
        methods: {
            open(item, index) {
                this.$prompt('请输入商家备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputType: 'textarea',
                }).then(({value}) => {
                    this.list[index].remark = value;
                    let para = {
                        r: 'plugin/advance/mall/deposit-order/remark',
                    };
                    request({
                        params: para,
                        method: 'post',
                        data: {
                            remark: value,
                            id: item.id,
                        }
                    }).then(res => {
                        this.$message({
                            type: 'success',
                            message: '保存成功'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            },
            handleClick() {
            },
            pageChange(data) {
                this.search.page = data;
                let para = {
                    page: this.search.page,
                    time: this.search.time,
                    keyword: this.search.keyword,
                    keyword_1: this.search.keyword_1,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform,
                    status: this.search.status,
                    r: `plugin/advance/mall/deposit-order/index`
                };
                delete para.time;
                this.loading = true;
                request({
                    params: para,
                    method: 'get'
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.list = response.data.data.list;
                    }
                })
            },
            async request(data) {
                let para = {
                    r: `plugin/advance/mall/deposit-order/index`,
                    status: data.status,
                    date_start: data.date_start,
                    date_end: data.date_end,
                    keyword: data.keyword,
                    keyword_1: data.keyword_1,
                    platform: data.platform
                };
                const response = await request({
                    params: para,
                    method: 'get',
                });
                this.loading = false;
                let { list, pagination, export_list }  = response.data.data;
                if (response.data.code === 0) {
                    this.list = list;
                    this.pagination = pagination;
                    this.export_list = export_list;
                }
            },
            searchOrderKey(data) {
                // this.search.keyword = data;
                let para = {
                    page: this.search.page,
                    time: this.search.time,
                    keyword: this.search.keyword,
                    keyword_1: this.search.keyword_1,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform,
                    status: this.search.status,
                    r: `plugin/advance/mall/deposit-order/index`
                };
                delete para.time;
                this.loading = true;
                request({
                    params: para,
                    method: 'get'
                }).then(response => {
                    this.loading = false;
                    let { list, pagination, export_list }  = response.data.data;
                    if (response.data.code === 0) {
                        this.list = list;
                        this.pagination = pagination;
                        this.export_list = export_list;
                    }
                })
            },
            searchOrderEmp() {
                this.search.keyword = '';
                let para = {
                    page: this.search.page,
                    time: this.search.time,
                    keyword: this.search.keyword,
                    keyword_1: this.search.keyword_1,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform,
                    status: this.search.status,
                    r: `plugin/advance/mall/deposit-order/index`
                };
                delete para.time;
                this.loading = true;
                request({
                    params: para,
                    method: 'get'
                }).then(response => {
                    this.loading = false;
                    let { list, pagination, export_list }  = response.data.data;
                    if (response.data.code === 0) {
                        this.list = list;
                        this.pagination = pagination;
                        this.export_list = export_list;
                    }
                })
            },
            searchOrder(data) {
               this.search.platform = data;
                let para = {
                    page: this.search.page,
                    time: this.search.time,
                    keyword: this.search.keyword,
                    keyword_1: this.search.keyword_1,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform,
                    status: this.search.status,
                    r: `plugin/advance/mall/deposit-order/index`
                };
                delete para.time;
                this.loading = true;
                request({
                    params: para,
                    method: 'get'
                }).then(response => {
                    this.loading = false;
                    let { list, pagination, export_list }  = response.data.data;
                    if (response.data.code === 0) {
                        this.list = list;
                        this.pagination = pagination;
                        this.export_list = export_list;
                    }
                })
            },
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                let para = {
                    page: this.search.page,
                    time: this.search.time,
                    keyword: this.search.keyword,
                    keyword_1: this.search.keyword_1,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    platform: this.search.platform,
                    status: this.search.status,
                    r: `plugin/advance/mall/deposit-order/index`
                };
                delete para.time;
                this.loading = true;
                request({
                    params: para,
                    method: 'get'
                }).then(response => {
                    this.loading = false;

                    let { list, pagination, export_list }  = response.data.data;
                    if (response.data.code === 0) {
                        this.list = list;
                        this.pagination = pagination;
                        this.export_list = export_list;
                    }
                })
            },
            toDetail(id) {
                this.$navigate({
                    r: 'plugin/advance/mall/deposit-order/detail',
                    order_id: id
                })
            },
            refund(id) {
                this.$confirm('此操作将退款该商品, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'plugin/advance/mall/deposit-order/cancel'
                        },
                        data: {
                            id: id
                        },
                        method: 'post',
                    }).then(response => {
                        this.loading = false;
                        if (response.data.code === 0) {
                            this.$message({
                                type: 'success',
                                message: '退款成功!'
                            });
                            for (let i = 0; i < this.list.length; i++) {
                                if (this.list[i].id === id) {
                                    this.list[i].is_refund = '1';
                                }
                            }
                        } else {
                            this.$message({
                                type: 'warning',
                                message: response.data.msg
                            });
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消退款'
                    });
                });

            },
            del(id,  index) {
                this.$confirm('此操作将删除该商品, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'plugin/advance/mall/deposit-order/del'
                        },
                        data: {
                            id: id
                        },
                        method: 'post',
                    }).then(response => {
                        this.loading = false;
                        if (response.data.code === 0) {
                            this.$message({
                                type: 'success',
                                message: '删除成功!'
                            });
                            this.$delete(this.list, index);
                        }
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消退款'
                    });
                });

            }
        },
        watch: {
            activeName: {
                handler: function(n, o) {
                    if (n !== o) {
                        this.loading = true;
                        this.search.status = n;
                        this.request({
                            page: this.search.page,
                            time: this.search.time,
                            keyword: this.search.keyword,
                            keyword_1: this.search.keyword_1,
                            date_start: this.search.date_start,
                            date_end: this.search.date_end,
                            platform: this.search.platform,
                            status: n,
                        });
                    }
                },
                immediate: true,
            }
        }
    });
</script>
