<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        position: relative;
        z-index: 1;
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .table-body .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>提现管理</span>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input  @keyup.enter.native="searchList" size="small" placeholder="请输入订单号搜索" v-model="keyword" clearable @clear='searchList'>
                    <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="全部" name="first"></el-tab-pane>
                <el-tab-pane label="待审核" name="second"></el-tab-pane>
                <el-tab-pane label="已通过" name="third"></el-tab-pane>
                <el-tab-pane label="未通过" name="fourth"></el-tab-pane>
            </el-tabs>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="60">
                </el-table-column>
                <el-table-column
                        label="订单号"
                        prop="order_no"
                        width="180">
                </el-table-column>
                <el-table-column
                        width="150"
                        label="店铺信息">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <app-image width="25" height="25" :src="scope.row.mch.store.cover_url"></app-image>
                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.mch.store.name}}
                            </app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        width="180"
                        label="用户">
                    <template slot-scope="scope">
                        <div>
                            <app-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.mch.user ? scope.row.mch.user.nickname : ''}}
                            </app-ellipsis>
                            <app-ellipsis style="margin-left: 10px;" :line="1">电话:{{scope.row.mch.mobile}}
                            </app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="转账方式" width="220">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type == 'wx'" flex="dir:top">
                            <span>微信线下转账</span>
                            <span style="color: #999999;">姓名: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                        <div v-if="scope.row.type == 'alipay'" flex="dir:top">
                            <span>支付宝线下转账</span>
                            <span style="color: #999999;">姓名: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                        <div v-if="scope.row.type == 'auto'" flex="dir:top">
                            <span>自动转账</span>
                        </div>
                        <div v-if="scope.row.type == 'balance'" flex="dir:top">
                            <span>余额</span>
                        </div>
                        <div v-if="scope.row.type == 'bank'" flex="dir:top">
                            <span>银行卡线下转账</span>
                            <span style="color: #999999;">开户行: {{scope.row.type_data.bank_name}}</span>
                            <span style="color: #999999;">开户人: {{scope.row.type_data.nickname}}</span>
                            <span style="color: #999999;">账号: {{scope.row.type_data.account}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="提现金额"
                        prop="money">
                </el-table-column>
                <el-table-column
                        label="申请时间"
                        prop="created_at"
                        width="180">
                </el-table-column>
                <el-table-column
                        label="操作"
                        width="180">
                    <template slot-scope="scope">
                        <template v-if="scope.row.status == 0">
                            <el-button @click="edit(scope.row, 1)" type="text" circle
                                       size="mini">
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img class="app-order-icon" src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="edit(scope.row, 0)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img class="app-order-icon" src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                        <template v-else-if="scope.row.status == 2">
                            <span style="color: #ff4544;">审核未通过</span>
                        </template>
                        <template v-else>
                            <span v-if="scope.row.transfer_status == 1">已打款</span>
                            <span style="color: #ff4544;" v-else-if="scope.row.transfer_status == 2">拒绝打款</span>
                            <template v-else>
                                <el-button @click="transfer(scope.row, 1)"
                                           type="text"
                                           circle
                                           size="mini">
                                    <el-tooltip class="item" effect="dark" content="确认打款" placement="top">
                                        <img class="app-order-icon" src="statics/img/mall/transfer.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="transfer(scope.row, 2)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="取消打款" placement="top">
                                        <img class="app-order-icon" src="statics/img/mall/nopass.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </template>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
                        :page-count="pageCount">
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
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                activeName: 'first',
                status: -1,
                keyword: '',
            };
        },
        methods: {
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/cash/index',
                        page: self.page,
                        status: self.status,
                        keyword: self.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(row, type) {
                let self = this;
                let text = '';
                if (type == 1) {
                    text = '同意申请'
                } else {
                    text = '拒绝申请';
                }
                self.$confirm(text + ', 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/cash/edit',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                            status: type,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            handleClick(tab, event) {
                this.page = 1;
                this.status = tab.index ? tab.index - 1 : '';
                this.getList();
            },
            transfer(row, type) {
                let self = this;
                let text = type === 1 ? '确认打款' : '拒绝打款';
                self.$confirm(text + ', 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/cash/transfer',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                            transfer_type: type
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.getList();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
