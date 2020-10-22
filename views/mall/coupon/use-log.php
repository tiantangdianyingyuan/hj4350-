<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-new-export-dialog');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .award-name .label-name {
        flex-shrink: 0;
        margin-right: 5px
    }

    .award-name .value-name {
        margin-right: 5px;
        margin-bottom: 5px;
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div flex="dir:left cross:center">
                <span>使用记录</span>
                <div flex="dir:right" flex-box=1>
                    <app-new-export-dialog
                            :field_list='export_list'
                            action_url="mall/coupon/use-log"
                            :params="search">
                    </app-new-export-dialog>
                </div>
            </div>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar">
                <el-form size="small" :inline="true" :model="search">
                    <!-- 搜索框 -->
                    <el-form-item prop="time">
                        <span style="color:#606266">下单时间：</span>
                        <el-date-picker
                                v-model="search.time"
                                @change="searchList"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item>
                        <div class="input-item">
                            <el-input @keyup.enter.native="searchList" size="small" placeholder="请输入用户昵称\订单号"
                                      v-model="search.keyword" clearable @clear='searchList'>
                                <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
            </el-col>
            <!--列表-->
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="user_id" label="ID" width="100"></el-table-column>
                <el-table-column prop="user_name" label="用户" width="280">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar"></app-image>
                        <div flex="dir:left cross:center">
                            {{scope.row.nickname}}
                        </div>
                        <img v-if="scope.row.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                        <img v-if="scope.row.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                        <img v-if="scope.row.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                        <img v-if="scope.row.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                    </template>
                </el-table-column>
                <el-table-column prop="order_no" label="订单号" width="300"></el-table-column>
                <el-table-column prop="coupon_name" label="优惠券名称"></el-table-column>
                <el-table-column width='180' prop="coupon_min_price" label="最低消费金额（元）">
                </el-table-column>
                <el-table-column width='150' prop="coupon_type" label="优惠方式">
                    <template slot-scope="scope">
                        <div v-if="scope.row.coupon_type == 2">优惠:{{scope.row.coupon_sub_price}}元</div>
                        <div v-if="scope.row.coupon_type == 1">{{scope.row.coupon_discount}}折</div>
                        <div v-if="scope.row.coupon_discount_limit && scope.row.coupon_type == 1">
                            优惠上限:{{scope.row.coupon_discount_limit}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="下单时间" width="320"></el-table-column>
                <el-table-column prop="coupon_status" :formatter="couponStatusFormatter" label="状态"
                                 width="200"></el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next, jumper"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin-bottom:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                loading: false,
                pagination: null,
                page: 1,
                search: {
                    time: null,
                },

                export_list: [],//导出字段数据,
            };
        },
        methods: {
            couponStatusFormatter(row) {
                if (row.coupon_status === 'ok') {
                    return '已使用';
                }
                if (row.coupon_status === 'cancel') {
                    return '已退回';
                }
                return '';
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                this.loading = true;
                let param = Object.assign({r: 'mall/coupon/use-log'}, this.search, {page: this.page});
                request({
                    params: param,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>